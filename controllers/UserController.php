<?php

namespace app\controllers;

use app\models\Master;
use app\models\User;
use app\models\UserDetail;
use Yii;
use app\components\ccf\CommonClass;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;
use yii\web\UnprocessableEntityHttpException;

class UserController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\User';
    //Include pagination information directly in the response body
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //Remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // restrict access to
                'Origin' => Yii::$app->params['GLOBAL']['ALLOWED_DOMAINS'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Headers' => Yii::$app->params['GLOBAL']['ALLOWED_REQUEST_HEADERS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                //'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        //Re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options', 'login']
        ];

        $behaviors['verb'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET','HEAD'],
                'view'   => ['GET','HEAD'],
                'create' => ['POST'],
                'update' => ['PUT','PATCH'],
                'delete' => ['DELETE'],
                'login' => ['POST']
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index','view','create','update','delete'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],									
                    'roles' => [
                        'VIEW_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],																	
                    'roles' => [
                        'VIEW_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [
                        'CREATE_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],					
                    'roles' => [
                        'UPDATE_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [
                        'DELETE_USER'
                    ]
                ]
            ],
        ];

        return $behaviors;
    }

    //Override the checkAccess in ActiveController for special checking
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'view' or $action === 'update' or $action === 'delete')
        {
            if (Yii::$app->user->identity->userType != Yii::$app->params['USER']['TYPE']['ADMIN']) {
                //Make sure the tenant can perform actions stated above to the objects under the same tenant.
                if (Yii::$app->user->identity->masterId != $model->masterId) {
                    throw new ForbiddenHttpException('You do not have the permission to ' . $action . ' the object.');
                }
            }
        }
    }

    public function actions()
    {
        /*
         * Default routing set by yii\rest\UrlRule
         * 'PUT,PATCH users/<id>' => 'user/update',
         * 'DELETE users/<id>' => 'user/delete',
         * 'GET,HEAD users/<id>' => 'user/view',
         * 'POST users' => 'user/create',
         * 'GET,HEAD users' => 'user/index',
         * 'users/<id>' => 'user/options',
         * 'users' => 'user/options',
        */
        $actions = parent::actions();

        // disable the default "create", "update" and "delete" action
        unset($actions['create'],$actions['update'],$actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider()
    {
        $request = Yii::$app->request;
        $params = $request->get();

        $user = User::find();
        $where = null;

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            $userTypes = [Yii::$app->params['USER']['TYPE']['AGENT']];
            $where = [['masterId'=>Yii::$app->user->identity->masterId,'userType'=>$userTypes]];
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $userTypes = [Yii::$app->params['USER']['TYPE']['PLAYER']];
            $where = [['masterId'=>Yii::$app->user->identity->masterId,'userType'=>$userTypes,'agentId'=>Yii::$app->user->identity->getId()]];
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $where = [['id'=>Yii::$app->user->identity->id]];
        }

        return CommonClass::prepareActiveQueryDataProvider($params,$user,$where);
    }

    public function actionCreate()
    { //Override the default create action
        $request = Yii::$app->request;
        $params = $request->post();

        $user = new User();
        $user->load($params,''); //Massive Assignment
        $user->setPasswordHash($params['password']);
        $masterId = $params['masterId'];

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['ADMIN']) {
            //Admin user will always create master user only
            $roleName = Yii::$app->params['AUTH_ITEM']['ROLE']['MASTER'];
            $user->userType = Yii::$app->params['USER']['TYPE']['MASTER'];
            $user->masterId = $masterId;
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Master user will always create agent user only
            $roleName = Yii::$app->params['AUTH_ITEM']['ROLE']['AGENT'];
            $user->userType = Yii::$app->params['USER']['TYPE']['AGENT'];
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            //Agent user will always create player user only
            $roleName = Yii::$app->params['AUTH_ITEM']['ROLE']['PLAYER'];
            $user->userType = Yii::$app->params['USER']['TYPE']['PLAYER'];
            $user->agentId = Yii::$app->user->identity->id;
        }

        if (Yii::$app->user->identity->userType != Yii::$app->params['USER']['TYPE']['ADMIN']) {
            $user->masterId = Yii::$app->user->identity->masterId;
        }
        $master = Master::findOne(['id'=>$user->masterId]);

        $user->username = $master->prefix.$user->username;
        $user->passwordNeverExpires = true;

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!$user->save()) {
                Yii::error($user->errors);
                return $user;
            }

            $auth = Yii::$app->authManager;
            $role = $auth->getRole($roleName);
            //It will throw exception if the role has already been assigned to the user
            if (!$auth->assign($role, $user->getId())) {
                throw new ServerErrorHttpException(("Failed to assign role to the user."));
            }

            if ($user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                $userDetail = new UserDetail();
                $userDetail->packageId = $params['packageId'];
                $userDetail->creditLimit = $params['creditLimit'];
                $userDetail->betMethod = $params['betMethod'];
                $userDetail->betGdLotto = $params['betGdLotto'];
                $userDetail->bet6d = $params['bet6d'];
                $userDetail->userId = $user->id;

                if (!$userDetail->save()) {
                    Yii::error($userDetail->errors);
                    return $userDetail;
                }
            } else if ($user->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
                $uplineAgent = $user->agent;
                //Check if the agent has sufficient credit limit to grant
                $creditAvailable = $uplineAgent->userDetail->creditLimit - $uplineAgent->userDetail->creditGranted;
                $grantedCredit = $params['creditLimit'];
                if ($grantedCredit > $creditAvailable) {
                    Throw new UnprocessableEntityHttpException("You do not have sufficient credit to grant to the player.");
                }

                $userDetail = new UserDetail();
                $userDetail->packageId = Yii::$app->user->identity->userDetail->packageId; //Follow upline agent settings
                $userDetail->creditLimit = $params['creditLimit'];
                $userDetail->betMethod = $params['betMethod'];
                $userDetail->betGdLotto = Yii::$app->user->identity->userDetail->betGdLotto; //Follow upline agent settings
                $userDetail->bet6d = Yii::$app->user->identity->userDetail->bet6d; //Follow upline agent settings
                $userDetail->extra4dCommRate = $params['extra4dCommRate'] ?? null;
                $userDetail->extra6dCommRate = $params['extra6dCommRate'] ?? null;
                $userDetail->extraGdCommRate = $params['extraGdCommRate'] ?? null;
                $userDetail->userId = $user->id;

                if (!$userDetail->save()) {
                    Yii::error($userDetail->errors);
                    return $userDetail;
                }

                //Proceed to update upline creditGranted
                $uplineAgentUd = $uplineAgent->userDetail;
                $uplineAgentUd->creditGranted += $grantedCredit;
                if (!$uplineAgentUd->save()) {
                    Yii::error($uplineAgentUd->errors);
                    return $uplineAgentUd;
                }
            }

            $dbTrans->commit();

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($user->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $user;
    }

    public function actionUpdate($id) {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $user = User::findOne($id);
        $user->load($params,''); //Massive Assignment

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!$user->save()) {
                Yii::error($user->errors);
                return $user;
            }

            if ($user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                $userDetail = $user->userDetail;
                $userDetail->packageId = $params['packageId'];
                $userDetail->creditLimit = $params['creditLimit'];
                $userDetail->betMethod = $params['betMethod'];
                $userDetail->betGdLotto = $params['betGdLotto'];
                $userDetail->bet6d = $params['bet6d'];
                $userDetail->userId = $user->id;

                if (!$userDetail->save()) {
                    Yii::error($userDetail->errors);
                    return $userDetail;
                }

                //Proceed to update all the downline players with the same package
                $players = $user->players;
                if (is_array($players)) {
                    foreach ($players as $player) {
                        $playerUd = $player->userDetail;
                        $playerUd->packageId = $params['packageId'];
                        $playerUd->betGdLotto = $params['betGdLotto'];
                        $playerUd->bet6d = $params['bet6d'];
                        if (!$playerUd->save()) {
                            Yii::error($playerUd->errors);
                            return $playerUd;
                        }
                    }
                }
            } else if ($user->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
                $uplineAgent = $user->agent;
                //Check if the agent has sufficient credit limit to grant
                $creditAvailable = $uplineAgent->userDetail->creditLimit - $uplineAgent->userDetail->creditGranted;
                $userDetail = $user->userDetail;
                $userDetail->load($params,''); //Massive Assignment
                $grantedCredit = $params['creditLimit']-$userDetail->creditLimit; //This might be negative, if the agent decreases the credit limit
                if ($grantedCredit > $creditAvailable) {
                    Throw new UnprocessableEntityHttpException("You do not have sufficient credit to grant to the player.");
                }

                $userDetail->packageId = $uplineAgent->userDetail->packageId; //Follow upline agent settings
                $userDetail->creditLimit = $params['creditLimit'];
                $userDetail->betMethod = $params['betMethod'];
                $userDetail->betGdLotto = $uplineAgent->userDetail->betGdLotto; //Follow upline agent settings
                $userDetail->bet6d = $uplineAgent->userDetail->bet6d; //Follow upline agent settings
                $userDetail->extra4dCommRate = $params['extra4dCommRate'] ?? null;
                $userDetail->extra6dCommRate = $params['extra6dCommRate'] ?? null;
                $userDetail->extraGdCommRate = $params['extraGdCommRate'] ?? null;
                $userDetail->userId = $user->id;

                if (!$userDetail->save()) {
                    Yii::error($userDetail->errors);
                    return $userDetail;
                }

                //Proceed to update upline creditGranted
                $uplineAgentUd = $uplineAgent->userDetail;
                $uplineAgentUd->creditGranted += $grantedCredit;
                if (!$uplineAgentUd->save()) {
                    Yii::error($uplineAgentUd->errors);
                    return $uplineAgentUd;
                }
            }

            $dbTrans->commit();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $user;
    }

    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $params = $request->get();

        $user = User::findOne($id);

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!$user->delete()) {
                Yii::error($user->errors);
                return $user;
            }

            //Revoke all the existing roles and permissions for the user
            $auth = Yii::$app->authManager;
            if (!$auth->revokeAll($id)) {
                throw new ServerErrorHttpException('Failed to revoke the role and permissions for unknown reason.');
            }

            $dbTrans->commit();
            $response = Yii::$app->getResponse();
            $response->setStatusCode(204);
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        $params = $request->post();

        $prefix = strtolower(substr($params['username'],0,3));
        if ($prefix == 'sys') { //System reserved prefix
            $user = User::findByUsername($params['username']);
        } else {
            $master = Master::findOne(['prefix'=>$prefix]);
            if (!$master) {
                throw new UnauthorizedHttpException();
            }
            $user = User::findByUsernameAndMasterId($params['username'], $master->id);
        }

        if (!$user || !$user->validatePassword($params['password'])) {
            throw new UnauthorizedHttpException();
        } else {			
            //Password validated, proceed to generate JWT
            $user->touch('lastLoginAt');
            $user->generateJWT();
        }

        return $user;
    }
	
    public function actionChangePassword() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $user = User::findOne($params['id']);
        if (!$user) {
            throw new NotFoundHttpException();
        }

        if (!empty($params["currentPassword"])) {
            if (!$user->validatePassword($params['currentPassword'])) {
                throw new UnprocessableEntityHttpException('Invalid current password entered.');
            }
        }

        $user -> scenario = User::SCENARIO_CHANGEPASSWORD;        
        $user -> setPasswordHash($params['password']);
        $user -> passwordExpiryDate = new \yii\db\Expression('NOW() + INTERVAL 10 YEAR');        
        $user -> save();

        return $user;
    }
}
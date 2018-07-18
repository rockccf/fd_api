<?php

namespace app\controllers;

use app\models\Master;
use app\models\Package;
use app\models\User;
use Yii;
use app\components\dbix\CommonClass;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;

class MasterController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\Master';
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
            'except' => ['options']
        ];

        $behaviors['verb'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET','HEAD'],
                'view'   => ['GET','HEAD'],
                'create' => ['POST'],
                'update' => ['PUT','PATCH'],
                'delete' => ['DELETE']
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
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [
                        'CREATE_MASTER'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [
                        'UPDATE_MASTER'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [
                        'DELETE_MASTER'
                    ],
                ]
            ],
        ];

        return $behaviors;
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
        unset($actions['create']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider() {
        $request = Yii::$app->request;
        $params = $request->get();

        $master = Master::find();
        $where = null;

        return CommonClass::prepareActiveQueryDataProvider($params,$master,$where);
    }

    public function actionCreate()
    { //Override the default create action
        $request = Yii::$app->request;
        $params = $request->post();

        $master = new Master();
        $master->load($params,''); //Massive Assignment
        if (strtolower($master->prefix) == 'sys') {
            throw new UnprocessableEntityHttpException("The specified prefix has already been taken.");
        }
        $master->prefix = strtolower($master->prefix);

        $dbTrans = Master::getDb()->beginTransaction();
        try {
            if (!$master->save()) {
                Yii::error($master->errors);
                return $master;
            }

            //Proceed to create a default master user for the master
            $user = new User();
            $user->username = $master->prefix.'master';
            $user->name = strtoupper($master->prefix).' Master User';
            $user->setPasswordHash($master->prefix.'123');
            $user->passwordNeverExpires = true;
            $user->active = 1;
            $user->userType = Yii::$app->params['USER']['TYPE']['MASTER'];
            $user->masterId = $master->id;
            if (!$user->save()) {
                Yii::error($user->errors);
                return $user;
            }

            $roleName = Yii::$app->params['AUTH_ITEM']['ROLE']['MASTER'];
            $auth = Yii::$app->authManager;
            $role = $auth->getRole($roleName);
            //It will throw exception if the role has already been assigned to the user
            if (!$auth->assign($role, $user->getId())) {
                throw new ServerErrorHttpException(("Failed to assign role to the user."));
            }

            $dbTrans->commit();

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($master->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $master;
    }
}
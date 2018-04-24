<?php

namespace app\controllers;

use app\models\AuthItem;
use Yii;
use app\components\dbix\CommonClass;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\ForbiddenHttpException;
use yii\web\ServerErrorHttpException;

class AuthItemController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\AuthItem';
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
                    'roles' => ['@']
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_ROLE',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_ROLE',
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_ROLE',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_ROLE'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_ROLE',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_ROLE'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_ROLE',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_ROLE'
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

        // disable the default "create", "update", "delete" and "view" action
        unset($actions['create'],$actions['update'],$actions['delete'],$actions['view']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider() {
        $request = Yii::$app->request;
        $params = $request->get();

        $authItem = AuthItem::find();
        $where = null;
        //If it's tenant, only allow to retrieve tenant related roles and permissions
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
            $tenantRolePrefix = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['TENANT'].Yii::$app->user->identity->tenantId.'_';
            $where = [
                ['or like','name',[$tenantRolePrefix,Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT']]],
            ];
        }

        return CommonClass::prepareActiveQueryDataProvider($params,$authItem,$where);
    }

    public function actionCreate() { //Override the default create action
        $request = Yii::$app->request;
        $params = $request->post();

        //For now, we only accept requests to create role with permissions assignment
        if (!empty($params['type']) && $params['type'] == Yii::$app->params['AUTH_ITEM']['TYPE']['ROLE']) {
            $roleName = null;
            $name = trim($params['name']);
            $description = ucwords($name); // Capitalize each word (Hello World)
            $name = str_replace(' ', '_', $name); //Replace spaces with underscores
            $name = strtoupper($name); //Make it uppercase
            if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
                $tenantRolePrefix = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['TENANT'] . Yii::$app->user->identity->tenantId . '_';
                $roleName = $tenantRolePrefix . $name;
            } else {
                $roleName = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['ADMIN'] . '_' . $name;
            }

            $authItem = new AuthItem;
            $dbTrans = AuthItem::getDb()->beginTransaction();
            try {
                $authItem->name = $roleName;
                $authItem->description = $description;
                $authItem->remarks = $params['remarks'] ?? null;
                $authItem->type = AuthItem::TYPE_ROLE;

                if (!$authItem->save()) {
                    Yii::error($authItem->errors);
                    return $authItem;
                }

                $auth = Yii::$app->authManager;

                $role = $auth->getRole($roleName);
                $permissions = $params['permissions'];
                foreach ($permissions as $permission) {
                    $permissionObj = $auth->getPermission($permission);
                    if ($auth->canAddChild($role, $permissionObj)) {
                        if (!$auth->addChild($role, $permissionObj)) {
                            throw new ServerErrorHttpException('Failed to add the child for unknown reason.');
                        }
                    } else {
                        throw new ServerErrorHttpException('Cannot add the child for unknown reason.');
                    }
                }

                $dbTrans->commit();

                $response = Yii::$app->getResponse();
                $response->setStatusCode(201);
                $id = implode(',', array_values($authItem->getPrimaryKey(true)));
                $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
            } catch (\Throwable $e) {
                $dbTrans->rollBack();
                throw $e;
            }
        } else {
            throw new BadRequestHttpException('Invalid parameters.');
        }

        return $authItem;
    }

    public function actionUpdate() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $auth = Yii::$app->authManager;

        //For now, we only accept requests to update role with permissions assignment
        if (!empty($params['type']) && $params['type'] == Yii::$app->params['AUTH_ITEM']['TYPE']['ROLE']) {
            $oldRoleName = null;
            $roleName = null;
            $oldName = trim($params['oldName']);
            $name = trim($params['name']);
            $description = ucwords($name); // Capitalize each word (Hello World)
            $name = str_replace(' ','_',$name); //Replace spaces with underscores
            $name = strtoupper($name); //Make it uppercase
            $oldName = str_replace(' ','_',$oldName); //Replace spaces with underscores
            $oldName = strtoupper($oldName); //Make it uppercase
            if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
                $tenantRolePrefix = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['TENANT'].Yii::$app->user->identity->tenantId.'_';
                $oldRoleName = $tenantRolePrefix.$oldName;
                $roleName = $tenantRolePrefix.$name;
            } else {
                $oldRoleName = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['ADMIN'].$oldName;
                $roleName = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['ADMIN'].$name;
            }

            $currentRole = $auth->getRole($oldRoleName);
            $authItem = AuthItem::findOne(['name' => $oldRoleName]);
            $dbTrans = AuthItem::getDb()->beginTransaction();
            try {
                $authItem->name = $roleName;
                $authItem->description = $description;
                $authItem->remarks = $params['remarks'] ?? null;

                if (!$authItem->save()) {
                    Yii::error($authItem);
                    return $authItem;
                }

                //Remove every permission under the current role regardless whether the name has been changed or not
                $auth->removeChildren($currentRole);

                //Reassign the permissions
                $permissions = $params['permissions'];
                if ($oldRoleName != $roleName) { //Name changed
                    $currentRole = $auth->getRole($roleName);
                }
                foreach ($permissions as $permission) {
                    $permissionObj = $auth->getPermission($permission);
                    if ($auth->canAddChild($currentRole, $permissionObj)) {
                        if (!$auth->addChild($currentRole, $permissionObj)) {
                            throw new ServerErrorHttpException('Failed to add the child for unknown reason.');
                        }
                    } else {
                        throw new ServerErrorHttpException('Cannot add the child for unknown reason.');
                    }
                }

                $dbTrans->commit();
            } catch (\Throwable $e) {
                $dbTrans->rollBack();
                throw $e;
            }
        } else {
            throw new BadRequestHttpException('Invalid parameters.');
        }

        return $authItem;
    }

    public function actionDelete($id) {
        $request = Yii::$app->request;
        $params = $request->get();

        $auth = Yii::$app->authManager;

        //For now, we only accept requests to delete role
        if ($params['type'] == Yii::$app->params['AUTH_ITEM']['TYPE']['ROLE']) {
            $authItem = AuthItem::findOne($id);
            //Check if there's any existing user assigned with this role
            $users = $auth->getUserIdsByRole($authItem->name);
            if (!empty($users) && count($users) > 0) {
                throw new ConflictHttpException('Failed to delete the role because there are users currently assigned with the role.');
            } else {
                $role = $auth->getRole($authItem->name);
                if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
                    $tenantRolePrefix = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['TENANT'].Yii::$app->user->identity->tenantId.'_';
                    //To make sure that the tenant is deleting own objects
                    if (strpos($role->name,$tenantRolePrefix) === false) { //Not same tenant detected
                        throw new ForbiddenHttpException('You do not have the permission to delete the object.');
                    }
                }
                if (!$auth->remove($role)) {
                    throw new ServerErrorHttpException('Failed to delete the object for unknown reason.');
                }
            }
        } else {
            throw new BadRequestHttpException('Invalid parameters.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);
    }

    public function actionView($id) {
        $request = Yii::$app->request;
        $params = $request->get();

        $authItem = null;

        //For now, we only accept requests to view role with permissions assignment
        if ($params['type'] == Yii::$app->params['AUTH_ITEM']['TYPE']['ROLE']) {
            //$name = trim($params['name']);
            $authItem = AuthItem::findOne($id);
            if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
                $tenantRolePrefix = Yii::$app->params['AUTH_ITEM']['ROLE_PREFIX']['TENANT'].Yii::$app->user->identity->tenantId.'_';
                //To make sure that the tenant is deleting own objects
                if (strpos($authItem->name,$tenantRolePrefix) === false) { //Not same tenant detected
                    throw new ForbiddenHttpException('You do not have the permission to view the object.');
                }
            }
        } else {
            throw new BadRequestHttpException('Invalid parameters.');
        }

        return $authItem;
    }
}
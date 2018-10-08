<?php

namespace app\controllers;

use app\models\Category;
use app\models\Package;
use Yii;
use app\components\ccf\CommonClass;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\rest\ActiveController;

class PackageController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\Package';
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
                    'roles' => [
                        '@'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],
                    'roles' => [
                        '@'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [
                        'CREATE_PACKAGE'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [
                        'UPDATE_PACKAGE'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [
                        'DELETE_PACKAGE'
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

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider() {
        $request = Yii::$app->request;
        $params = $request->get();

        $package = Package::find();
        $where = null;

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            $where = [['masterId' => Yii::$app->user->identity->masterId]];
        }

        return CommonClass::prepareActiveQueryDataProvider($params,$package,$where);
    }
}
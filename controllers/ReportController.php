<?php

namespace app\controllers;

use app\components\dbix\ReportClass;

use Yii;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Json;
use yii\rest\ActiveController;

class ReportController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\pdf';
    //Include pagination information directly in the response body
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    //Default custom layout file
    public $layout = "report_general";

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
                // Allow the x-filename header to be exposed to the browser.
                'Access-Control-Expose-Headers' => ['x-filename'],
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
                'delete' => ['DELETE'],
                'get-tenant-report' => ['GET','HEAD'],
                'get-tenant-report-excel' => ['GET','HEAD'],
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
            'only' => ['get-report'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['get-report'],
                    'roles' => ['@'],
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

        // disable all the default actions
        unset($actions['index'],$actions['view'],$actions['create'],$actions['update'],$actions['delete']);

        return $actions;
    }

    public function actionGetAdminReport() {

    }

    public function actionGetMasterReport() {

    }

    public function actionGetReport() {
        $request = Yii::$app->request;
        $params = $request->get();

        $fileTemplateId = $params["fileTemplateId"];
        $extraParams = $params["extraParams"] ?? null;
        $extraParams = Json::decode($extraParams);

        $content = null;

        $resultArray = null;
        switch ($fileTemplateId) {
            case Yii::$app->params['FILE_TEMPLATE']['REPORT']['WIN_LOSS_DETAILS']:
                $resultArray = ReportClass::getWinLossDetails($extraParams);
                break;
        }

        return $resultArray;
    }
}

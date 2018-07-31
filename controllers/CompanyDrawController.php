<?php

namespace app\controllers;

use app\models\Company;
use app\models\CompanyDraw;
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

class CompanyDrawController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\CompanyDraw';
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
                'bulk-delete' => ['DELETE']
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
            'only' => ['index','view','create','bulk-delete'],
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
                        '@'
                    ],
                ],
                [
                    'allow' => true,
                    'actions' => ['bulk-delete'],
                    'roles' => [
                        '@'
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
        unset($actions['create'],$actions['update'],$actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider() {
        $request = Yii::$app->request;
        $params = $request->get();

        $cd = CompanyDraw::find();
        $where = null;

        return CommonClass::prepareActiveQueryDataProvider($params,$cd,$where);
    }

    public function actionCreate()
    { //Override the default create action
        $request = Yii::$app->request;
        $params = $request->post();

        $companyArray = $params["companyArray"];

        $dbTrans = CompanyDraw::getDb()->beginTransaction();
        try {
            foreach ($companyArray as $companyObject) {
                $companyId = $companyObject["id"];
                $drawDateArray = $companyObject["days"];

                $company = Company::findOne(['id'=>$companyId]);
                foreach ($drawDateArray as $drawDate) {
                    $cd = new CompanyDraw();
                    $drawDate = new \DateTime($drawDate);
                    $cd->drawDate = $drawDate->format('Y-m-d');
                    $cd->status = Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW'];
                    $cd->drawTime = $company->drawTime;
                    $cd->stopBetTime = $company->stopBetTime;
                    $cd->companyId = $company->id;

                    if (!$cd->save()) {
                        Yii::error($cd);
                        return $cd;
                    }
                }
            }

            $dbTrans->commit();

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($cd->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $cd;
    }

    public function actionBulkDelete() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $drawIdArray = $params["drawIdArray"];

        $deletedRows = CompanyDraw::deleteAll(['id'=>$drawIdArray]);
        if ($deletedRows == 0) {
            throw new ServerErrorHttpException('Failed to delete company draws.');
        }

        $response = Yii::$app->getResponse();
        $response->setStatusCode(204);
    }
}
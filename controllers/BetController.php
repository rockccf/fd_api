<?php

namespace app\controllers;

use app\models\Bet;
use app\models\BetDetail;
use app\models\BetDetailReject;
use app\models\Company;
use app\models\Master;
use app\models\UserDetail;
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

class BetController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\Bet';
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
            'only' => ['index','view','create','update'],
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
                        'BET'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],
                    'roles' => [
                        'UPDATE_USER'
                    ]
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

        $bet = Bet::find();
        $where = null;

        return CommonClass::prepareActiveQueryDataProvider($params,$bet,$where);
    }

    //Override the default create action
    public function actionCreate()
    {
        $request = Yii::$app->request;
        $params = $request->post();

        $betArray = $params['betArray'];
        Yii::info($betArray);
        $masterId = Yii::$app->user->identity->masterId;

        $validBets = false;
        $today = new \DateTime();
        $today->setTime(0, 0);
        foreach ($betArray as $bet) {
            //Rule 1 : Check stop bet time for each company
            $companyCode = $bet["companyCode"];
            $company = Company::findOne(['code' => $companyCode]);
            $drawDate = $bet["drawDate"];
            $drawDate = new \DateTime($drawDate);
            $drawDate->setTime(0,0);
            if ($drawDate > $today) {
                $validBets = true;
                break;
            } else if ($drawDate == $today) { //Same day detected
                $stopBetTime = $company->stopBetTime;
                if (time() >= strtotime($stopBetTime)) {
                    continue;
                } else {
                    $validBets = true;
                }
            }
        }

        if (!$validBets) {
            throw new ServerErrorHttpException('No valid bets.');
        }

        $master = Master::findOne($masterId);

        $betModel = new Bet();
        $betModel->betMaxLimitBig = $master->betMaxLimitBig;
        $betModel->betMaxLimitSmall = $master->betMaxLimitSmall;
        $betModel->betMaxLimit4a = $master->betMaxLimit4a;
        $betModel->betMaxLimit4b = $master->betMaxLimit4b;
        $betModel->betMaxLimit4c = $master->betMaxLimit4c;
        $betModel->betMaxLimit4d = $master->betMaxLimit4d;
        $betModel->betMaxLimit4e = $master->betMaxLimit4e;
        $betModel->betMaxLimit4f = $master->betMaxLimit4f;
        $betModel->betMaxLimit3abc = $master->betMaxLimit3abc;
        $betModel->betMaxLimit3a = $master->betMaxLimit3a;
        $betModel->betMaxLimit3b = $master->betMaxLimit3b;
        $betModel->betMaxLimit3c = $master->betMaxLimit3c;
        $betModel->betMaxLimit3d = $master->betMaxLimit3d;
        $betModel->betMaxLimit3e = $master->betMaxLimit3e;
        $betModel->betMaxLimit5d = $master->betMaxLimit5d;
        $betModel->betMaxLimit6d = $master->betMaxLimit6d;
        $userDetail = UserDetail::findOne(['userId'=>Yii::$app->user->identity->getId()]);
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $betModel->{'4dCommRate'} = $userDetail->package->{'4dAgentCommRate'};
            $betModel->{'6dCommRate'} = $userDetail->package->{'6dAgentCommRate'};
            $betModel->{'gdCommRate'} = $userDetail->package->{'gdAgentCommRate'};
        } else {
            $betModel->{'4dCommRate'} = $userDetail->package->{'4dPlayerCommRate'};
            $betModel->{'6dCommRate'} = $userDetail->package->{'6dPlayerCommRate'};
            $betModel->{'gdCommRate'} = $userDetail->package->{'gdPlayerCommRate'};
        }
        $betModel->extra4dCommRate = $userDetail->extra4dCommRate;
        $betModel->extra6dCommRate = $userDetail->extra6dCommRate;
        $betModel->extraGdCommRate = $userDetail->extraGdCommRate;
        $betModel->masterId = $masterId;

        $total4dCommRate = $betModel->{'4dCommRate'}+$betModel->extra4dCommRate;
        $total6dCommRate = $betModel->{'6dCommRate'}+$betModel->extra6dCommRate;
        $totalGdCommRate = $betModel->{'gdCommRate'}+$betModel->extraGdCommRate;

        $dbTrans = Bet::getDb()->beginTransaction();
        try {
            if (!$betModel->save()) {
                Yii::error($betModel->errors);
                return $betModel;
            }

            $grandTotalSales = 0;
            $grandTotalCommission = 0;
            $betNumberArray = [];

            foreach ($betArray as $bet) {
                //Rule 1 : Check stop bet time for each company
                $betAllowed = false;
                $companyCode = $bet["companyCode"];
                $company = Company::findOne(['code' => $companyCode]);
                $drawDate = $bet["drawDate"];
                $drawDate = new \DateTime($drawDate);
                if ($drawDate == $today) { //Same day detected
                    $stopBetTime = $company->stopBetTime;
                    if (time() < strtotime($stopBetTime)) {
                        $betAllowed = true;
                    }
                }

                if (!$betAllowed) {
                    continue;
                }

                //Rule 2 : Check bet max limit and available balance to bet
                $balanceArray = CommonClass::getAvailableBalance($bet['number'],$drawDate,$company->id,$masterId);

                $betOption = $bet["betOption"];
                $big = $bet["big"] ?? null;
                $small = $bet["small"] ?? null;
                $amount4a = $bet["4a"] ?? null;
                $amount4b = $bet["4b"] ?? null;
                $amount4c = $bet["4c"] ?? null;
                $amount4d = $bet["4d"] ?? null;
                $amount4e = $bet["4e"] ?? null;
                $amount4f = $bet["4f"] ?? null;
                $amount3abc = $bet["3abc"] ?? null;
                $amount3a = $bet["3a"] ?? null;
                $amount3b = $bet["3b"] ?? null;
                $amount3c = $bet["3c"] ?? null;
                $amount3d = $bet["3d"] ?? null;
                $amount3e = $bet["3e"] ?? null;
                $amount5d = $bet["5d"] ?? null;
                $amount6d = $bet["6d"] ?? null;

                if (!array_key_exists($bet["number"],$betNumberArray)) {
                    $betNumberArray[$bet["number"]] = [
                        "betOption" => $betOption,
                        "big" => $big,
                        "small" => $small,
                        "4a" => $amount4a,
                        "4b" => $amount4b,
                        "4c" => $amount4c,
                        "4d" => $amount4d,
                        "4e" => $amount4e,
                        "4f" => $amount4f,
                        "3abc" => $amount3abc,
                        "3a" => $amount3a,
                        "3b" => $amount3b,
                        "3c" => $amount3c,
                        "3d" => $amount3d,
                        "3e" => $amount3e,
                        "5d" => $amount5d,
                        "6d" => $amount6d
                    ];
                }

                $rejectBig = null;
                $rejectSmall = null;
                $reject4a = null;
                $reject4b = null;
                $reject4c = null;
                $reject4d = null;
                $reject4e = null;
                $reject4f = null;
                $reject3abc = null;
                $reject3a = null;
                $reject3b = null;
                $reject3c = null;
                $reject3d = null;
                $reject3e = null;
                $reject5d = null;
                $reject6d = null;

                if ($big > $balanceArray['balanceBig']) { //Insufficient balance
                    $rejectBig = $big - $balanceArray['balanceBig'];
                    $big -= $rejectBig;
                }

                if ($small > $balanceArray['balanceSmall']) { //Insufficient balance
                    $rejectSmall = $small - $balanceArray['balanceSmall'];
                    $small -= $rejectSmall;
                }

                if ($amount4a > $balanceArray['balance4a']) { //Insufficient balance
                    $reject4a = $amount4a - $balanceArray['balance4a'];
                    $amount4a -= $reject4a;
                }

                if ($amount4b > $balanceArray['balance4b']) { //Insufficient balance
                    $reject4b = $amount4b - $balanceArray['balance4b'];
                    $amount4b -= $reject4b;
                }

                if ($amount4c > $balanceArray['balance4c']) { //Insufficient balance
                    $reject4c = $amount4c - $balanceArray['balance4c'];
                    $amount4c -= $reject4c;
                }

                if ($amount4d > $balanceArray['balance4d']) { //Insufficient balance
                    $reject4d = $amount4d - $balanceArray['balance4d'];
                    $amount4d -= $reject4d;
                }

                if ($amount4e > $balanceArray['balance4e']) { //Insufficient balance
                    $reject4e = $amount4e - $balanceArray['balance4e'];
                    $amount4e -= $reject4e;
                }

                if ($amount4f > $balanceArray['balance4f']) { //Insufficient balance
                    $reject4f = $amount4f - $balanceArray['balance4f'];
                    $amount4f -= $reject4f;
                }

                if ($amount3abc > $balanceArray['balance3abc']) { //Insufficient balance
                    $reject3abc = $amount3abc - $balanceArray['balance3abc'];
                    $amount3abc -= $reject3abc;
                }

                if ($amount3a > $balanceArray['balance3a']) { //Insufficient balance
                    $reject3a = $amount3a - $balanceArray['balance3a'];
                    $amount3a -= $reject3a;
                }

                if ($amount3b > $balanceArray['balance3b']) { //Insufficient balance
                    $reject3b = $amount3b - $balanceArray['balance3b'];
                    $amount3b -= $reject3b;
                }

                if ($amount3c > $balanceArray['balance3c']) { //Insufficient balance
                    $reject3c = $amount3c - $balanceArray['balance3c'];
                    $amount3c -= $reject3c;
                }

                if ($amount3d > $balanceArray['balance3d']) { //Insufficient balance
                    $reject3d = $amount3d - $balanceArray['balance3d'];
                    $amount3d -= $reject3d;
                }

                if ($amount3e > $balanceArray['balance3e']) { //Insufficient balance
                    $reject3e = $amount3e - $balanceArray['balance3e'];
                    $amount3e -= $reject3e;
                }

                if ($amount5d > $balanceArray['balance5d']) { //Insufficient balance
                    $reject5d = $amount5d - $balanceArray['balance5d'];
                    $amount5d -= $reject5d;
                }

                if ($amount6d > $balanceArray['balance6d']) { //Insufficient balance
                    $reject6d = $amount6d - $balanceArray['balance6d'];
                    $amount6d -= $reject6d;
                }

                if (!empty($rejectBig) || !empty($rejectSmall)
                    || !empty($reject4a) || !empty($reject4b) || !empty($reject4c) || !empty($reject4d) || !empty($reject4e) || !empty($reject4f)
                    || !empty($reject3abc) || !empty($reject3a) || !empty($reject3b) || !empty($reject3c) || !empty($reject3d) || !empty($reject3e)
                    || !empty($reject5d) || !empty($reject6d)) {
                    if (empty($big) && empty($small)
                        && empty($amount4a) && empty($amount4b) && empty($amount4c) && empty($amount4d) && empty($amount4e) && empty($amount4f)
                        && empty($amount3abc) && empty($amount3a) && empty($amount3b) && empty($amount3c) && empty($amount3d) && empty($amount3e)
                        && empty($amount5d) && empty($amount6d)) {
                        $status = Yii::$app->params['BET']['DETAIL']['STATUS']['REJECTED']; //Every bet is rejected
                    } else {
                        $status = Yii::$app->params['BET']['DETAIL']['STATUS']['LIMITED']; //Some bets are rejected
                    }
                } else {
                    $status = Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']; //All bets accepted
                }

                $bd = new BetDetail();
                $bd->number = $bet["number"];
                $bd->status = $status;
                $bd->big = $big;
                $bd->small = $small;
                $bd->{'4a'} = $amount4a;
                $bd->{'4b'} = $amount4b;
                $bd->{'4c'} = $amount4c;
                $bd->{'4d'} = $amount4d;
                $bd->{'4e'} = $amount4e;
                $bd->{'4f'} = $amount4f;
                $bd->{'3abc'} = $amount3abc;
                $bd->{'3a'} = $amount3a;
                $bd->{'3b'} = $amount3b;
                $bd->{'3c'} = $amount3c;
                $bd->{'3d'} = $amount3d;
                $bd->{'3e'} = $amount3e;
                $bd->{'5d'} = $amount5d;
                $bd->{'6d'} = $amount6d;

                $total4dSales = $big+$small+$amount4a+$amount4b+$amount4c+$amount4d+$amount4e+$amount4f;
                $total4dSales += $amount3abc+$amount3a+$amount3b+$amount3c+$amount3d+$amount3e;
                $total6dSales = $amount5d+$amount6d;
                $totalSales = $total4dSales+$total6dSales;

                $total4dCommission = 0;
                if ($total4dSales > 0) {
                    if ($companyCode == Yii::$app->params['COMPANY']['CODE']['GD']) {
                        $total4dCommission = round($total4dSales*$totalGdCommRate/100,2);
                    } else {
                        $total4dCommission = round($total4dSales*$total4dCommRate/100,2);
                    }
                }

                $total6dCommission = 0;
                if ($total6dSales > 0) {
                    $total6dCommission = round($total6dSales*$total6dCommRate/100,2);
                }
                $totalCommission = $total4dCommission+$total6dCommission;

                $bd->totalSales = $totalSales;
                $bd->totalCommission = $totalCommission;
                $bd->drawDate = $drawDate->format('Y-m-d');
                $bd->companyDrawId = $balanceArray['companyDrawId'];
                $bd->betId = $betModel->id;
                if (!$bd->save()) {
                    Yii::error($bd->errors);
                    return $bd;
                }

                $grandTotalSales += $totalSales;
                $grandTotalCommission += $totalCommission;

                if ($status != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                    //There's rejected bet, proceed to insert into bet_detail_reject
                    $bdr = new BetDetailReject();
                    $bdr->big = $rejectBig;
                    $bdr->small = $rejectSmall;
                    $bdr->{'4a'} = $reject4a;
                    $bdr->{'4b'} = $reject4b;
                    $bdr->{'4c'} = $reject4c;
                    $bdr->{'4d'} = $reject4d;
                    $bdr->{'4e'} = $reject4e;
                    $bdr->{'4f'} = $reject4f;
                    $bdr->{'3abc'} = $reject3abc;
                    $bdr->{'3a'} = $reject3a;
                    $bdr->{'3b'} = $reject3b;
                    $bdr->{'3c'} = $reject3c;
                    $bdr->{'3d'} = $reject3d;
                    $bdr->{'3e'} = $reject3e;
                    $bdr->{'5d'} = $reject5d;
                    $bdr->{'6d'} = $reject6d;

                    $totalReject = $rejectBig+$rejectSmall+$reject4a+$reject4b+$reject4c+$reject4e+$reject4e+$reject4f;
                    $totalReject += $reject3abc+$reject3a+$reject3b+$reject3c+$reject3d+$reject3e+$reject5d+$reject6d;

                    $bdr->totalReject = $totalReject;
                    $bdr->betDetailId = $bd->id;
                    if (!$bdr->save()) {
                        Yii::error($bdr->errors);
                        return $bdr;
                    }
                }
            } //End foreach ($betArray as $bet)
            //Update the totalSales and totalCommission columns
            $betModel->totalSales = $grandTotalSales;
            $betModel->totalCommission = $grandTotalCommission;
            if (!$betModel->save()) {
                Yii::error($betModel->errors);
                return $betModel;
            }

            $dbTrans->commit();

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($betModel->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $betModel;
    }
}
<?php

namespace app\controllers;

use app\models\Bet;
use app\models\BetDetail;
use app\models\BetDetailReject;
use app\models\BetNumber;
use app\models\Company;
use app\models\Master;
use app\models\User;
use app\models\UserDetail;
use Yii;
use app\components\ccf\CommonClass;
use yii\db\Expression;
use yii\db\Query;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\rest\ActiveController;
use yii\web\ServerErrorHttpException;
use yii\web\UnprocessableEntityHttpException;

class BetController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\Bet';
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
                'get-bet-slip-history' => ['GET','HEAD']
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
            'only' => ['index','view','create','update','get-bet-slip-history','get-bet-number-history','get-void-bet-history','get-voidable-bets','get-voided-bets'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],
                    'roles' => ['@'],
                ],
                [
                    'allow' => true,
                    'actions' => ['view','get-bet-slip-history','get-bet-number-history','get-void-bet-history','get-voidable-bets','get-voided-bets'],
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
                        'BET'
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
        $betFormRowsArray = $params['betFormRowsArray'];
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
        $betModel->status = Yii::$app->params['BET']['STATUS']['NEW'];
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
        $betModel->betMethod = $userDetail->betMethod;
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
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $betModel->superior4dCommRate = $userDetail->package->{'4dAgentCommRate'};
            $betModel->superior6dCommRate = $userDetail->package->{'4dAgentCommRate'};
            $betModel->superiorGdCommRate = $userDetail->package->{'4dAgentCommRate'};
        }
        $betModel->masterCommRate = $master->commissionRate;
        $betModel->packageId = $userDetail->packageId;
        $betModel->masterId = $masterId;

        $own4dCommRate = $betModel->{'4dCommRate'};
        $extra4dCommRate = $betModel->extra4dCommRate;
        $total4dCommRate = $betModel->{'4dCommRate'}+$betModel->extra4dCommRate;

        $own6dCommRate = $betModel->{'6dCommRate'};
        $extra6dCommRate = $betModel->extra6dCommRate;
        $total6dCommRate = $betModel->{'6dCommRate'}+$betModel->extra6dCommRate;

        $ownGdCommRate = $betModel->{'gdCommRate'};
        $extraGdCommRate = $betModel->extraGdCommRate;
        $totalGdCommRate = $betModel->{'gdCommRate'}+$betModel->extraGdCommRate;

        $superior4dCommRate = $betModel->superior4dCommRate-$total4dCommRate;
        $superior6dCommRate = $betModel->superior6dCommRate-$total6dCommRate;
        $superiorGdCommRate = $betModel->superiorGdCommRate-$totalGdCommRate;

        $dbTrans = Bet::getDb()->beginTransaction();
        try {
            if (!$betModel->save()) {
                Yii::error($betModel->errors);
                return $betModel;
            }

            $grandTotalSales = $grandTotalOwnCommission = $grandTotalExtraCommission = $grandTotalCommission = $grandTotalSuperiorCommission = 0;

            $i = 0;
            while ($i<count($betFormRowsArray)) {
                $betFormRow = $betFormRowsArray[$i];

                $number = $betFormRow["number"] ?? null;
                if (empty($number)) {
                    array_splice($betFormRowsArray, $i, 1);
                    continue;
                }
                $bn = new BetNumber();
                $bn->rowIndex = $i;
                $bn->number = $number;
                $bn->betOption = $betFormRow["betOption"]["id"];
                $bn->status = Yii::$app->params['BET']['NUMBER']['STATUS']['ACCEPTED'];

                $big = $betFormRow["big"] ?? null;
                $small = $betFormRow["small"] ?? null;
                $amount4a = $betFormRow["4a"] ?? null;
                $amount4b = $betFormRow["4b"] ?? null;
                $amount4c = $betFormRow["4c"] ?? null;
                $amount4d = $betFormRow["4d"] ?? null;
                $amount4e = $betFormRow["4e"] ?? null;
                $amount4f = $betFormRow["4f"] ?? null;
                $amount3abc = $betFormRow["3abc"] ?? null;
                $amount3a = $betFormRow["3a"] ?? null;
                $amount3b = $betFormRow["3b"] ?? null;
                $amount3c = $betFormRow["3c"] ?? null;
                $amount3d = $betFormRow["3d"] ?? null;
                $amount3e = $betFormRow["3e"] ?? null;
                $amount5d = $betFormRow["5d"] ?? null;
                $amount6d = $betFormRow["6d"] ?? null;

                $bn->big = $big;
                $bn->small = $small;
                $bn->{'4a'} = $amount4a;
                $bn->{'4b'} = $amount4b;
                $bn->{'4c'} = $amount4c;
                $bn->{'4d'} = $amount4d;
                $bn->{'4e'} = $amount4e;
                $bn->{'4f'} = $amount4f;
                $bn->{'3abc'} = $amount3abc;
                $bn->{'3a'} = $amount3a;
                $bn->{'3b'} = $amount3b;
                $bn->{'3c'} = $amount3c;
                $bn->{'3d'} = $amount3d;
                $bn->{'3e'} = $amount3e;
                $bn->{'5d'} = $amount5d;
                $bn->{'6d'} = $amount6d;

                $companyCodes = [];
                foreach (Yii::$app->params['COMPANY']['CODE'] as $companyCode) {
                    if (isset($betFormRow[$companyCode])) {
                        if (!in_array($companyCode,$companyCodes)) {
                            $companyCodes[] = $companyCode;
                        }
                    }
                }

                $bn->companyCodes = $companyCodes;
                $bn->drawDates = $betFormRow["drawDateArray"];
                $totalBet = $big+$small+$amount4a+$amount4b+$amount4c+$amount4d+$amount4e+$amount4f;
                $totalBet += $amount3abc+$amount3a+$amount3b+$amount3c+$amount3d+$amount3e+$amount5d+$amount6d;
                if ($bn->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['RETURN']) {
                    $numbers = [];
                    self::permute($number,0,strlen($number)-1,$numbers);
                    if (count($numbers) > 1) { //Make sure user did not enter a number with same 4 digits (1111, 2222 etc)
                        $totalBet = $totalBet * 2;
                    }
                } else if ($bn->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['BOX']) {
                    $numbers = [];
                    self::permute($number,0,strlen($number)-1,$numbers);
                    $totalBet = $totalBet * count($numbers);
                } else if ($bn->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['PH']) {
                    $woFirstDigitArray = [];
                    self::permute(substr($number,1),0,strlen(substr($number,1))-1,$woFirstDigitArray);
                    $totalBet = $totalBet * count($woFirstDigitArray);
                }
                $totalBet = $totalBet * count($companyCodes) * count($betFormRow["drawDateArray"]);
                $bn->totalBet = $totalBet;
                $bn->betId = $betModel->id;

                $grandTotalSales += $totalBet;

                if (!$bn->save()) {
                    Yii::error($bn->errors);
                    return $bn;
                }
                $i++;
            }

            //Check if user has enough credit to place the bet(s)
            //Calculate user available credit
            $creditAvailable = $userDetail->creditLimit - $userDetail->creditGranted + $userDetail->balance - $userDetail->outstandingBet;
            if ($grandTotalSales > $creditAvailable) {
                //Reject all the bets
                throw new UnprocessableEntityHttpException("Bet(s) rejected due to insufficient credit. Only ".$userDetail->creditAvailable." left, please re-adjust your bet(s).");
            }

            foreach ($betArray as $bet) {
                //Rule 1 : Check stop bet time for each company
                $betAllowed = true;
                $companyCode = $bet["companyCode"];
                $company = Company::findOne(['code' => $companyCode]);
                $drawDate = $bet["drawDate"];
                $drawDate = new \DateTime($drawDate);
                if ($drawDate == $today) { //Same day detected
                    $stopBetTime = $company->stopBetTime;
                    if (time() > strtotime($stopBetTime)) {
                        $betAllowed = false;
                    }
                } else if ($drawDate < $today) {
                    $betAllowed = false;
                }

                if (!$betAllowed) {
                    continue;
                }

                //Rule 2 : Check bet max limit and available balance to bet
                $betOption = $bet["betOption"];
                $betNumber = $bet["number"];
                $bn = BetNumber::findOne(['betId'=>$betModel->id,'rowIndex'=>$bet['rowIndex']]);
                //Check betOption
                if ($betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['SINGLE']) {
                    $number = $betNumber;
                    $resultsArray = self::insertBetDetail($number,$bet,$drawDate,$company->id,$companyCode,$masterId,$betModel->id,$bn->id,
                        $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                        $totalGdCommRate,$superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                        $ownGdCommRate,$extraGdCommRate);

                    if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                        $grandTotalSales -= $resultsArray["totalReject"];
                    }
                    $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                    $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                    $grandTotalCommission += $resultsArray["totalCommission"];
                    $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];
                } else if ($betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['RETURN']) {
                    $number = $betNumber;
                    $resultsArray = self::insertBetDetail($number,$bet,$drawDate,$company->id,$companyCode,$masterId,$betModel->id,$bn->id,
                        $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                        $totalGdCommRate,$superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                        $ownGdCommRate,$extraGdCommRate);

                    if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                        $grandTotalSales -= $resultsArray["totalReject"];
                    }
                    $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                    $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                    $grandTotalCommission += $resultsArray["totalCommission"];
                    $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];

                    //Reverse the number (1234 becomes 4321)
                    if (strrev($betNumber) != $number) { //Make sure user did not enter a number with same 4 digits (1111, 2222 etc)
                        $number = strrev($betNumber);
                        $resultsArray = self::insertBetDetail($number,$bet,$drawDate,$company->id,$companyCode,$masterId,$betModel->id,$bn->id,
                            $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                            $totalGdCommRate,$superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                            $ownGdCommRate,$extraGdCommRate);

                        if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                            $grandTotalSales -= $resultsArray["totalReject"];
                        }
                        $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                        $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                        $grandTotalCommission += $resultsArray["totalCommission"];
                        $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];
                    }
                } else if ($betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['BOX']) {
                    $numbers = [];
                    self::permute($betNumber,0,strlen($betNumber)-1,$numbers);
                    foreach ($numbers as $number) {
                        $resultsArray = self::insertBetDetail($number,$bet,$drawDate,$company->id,$companyCode,$masterId,$betModel->id,$bn->id,
                            $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                            $totalGdCommRate,$superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                            $ownGdCommRate,$extraGdCommRate);

                        if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                            $grandTotalSales -= $resultsArray["totalReject"];
                        }
                        $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                        $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                        $grandTotalCommission += $resultsArray["totalCommission"];
                        $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];
                    }
                } else if ($betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['IBOX']) {
                    $numbers = [];
                    self::permute($betNumber,0,strlen($betNumber)-1,$numbers);
                    foreach ($numbers as $number) {
                        $resultsArray = self::insertBetDetail($number, $bet, $drawDate, $company->id, $companyCode, $masterId, $betModel->id,$bn->id,
                            $total4dCommRate, $superior4dCommRate, $total6dCommRate, $superior6dCommRate,
                            $totalGdCommRate, $superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                            $ownGdCommRate,$extraGdCommRate, true, count($numbers));

                        if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                            $grandTotalSales -= $resultsArray["totalReject"];
                        }
                        $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                        $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                        $grandTotalCommission += $resultsArray["totalCommission"];
                        $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];
                    }
                } else if ($betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['PH']) { //It's like BOX, but without looping the first digit
                    $firstDigit =  substr($betNumber,0,1);
                    $woFirstDigitArray = [];
                    self::permute(substr($betNumber,1),0,strlen(substr($betNumber,1))-1,$woFirstDigitArray);
                    foreach ($woFirstDigitArray as $woFirstDigit) {
                        $number = $firstDigit.$woFirstDigit;
                        $resultsArray = self::insertBetDetail($number,$bet,$drawDate,$company->id,$companyCode,$masterId,$betModel->id,$bn->id,
                            $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                            $totalGdCommRate,$superiorGdCommRate,$own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                            $ownGdCommRate,$extraGdCommRate);

                        if ($resultsArray["status"] != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                            $grandTotalSales -= $resultsArray["totalReject"];
                        }
                        $grandTotalOwnCommission += $resultsArray["totalOwnCommission"];
                        $grandTotalExtraCommission += $resultsArray["totalExtraCommission"];
                        $grandTotalCommission += $resultsArray["totalCommission"];
                        $grandTotalSuperiorCommission += $resultsArray["totalSuperiorCommission"];
                    }
                }
            } //End foreach ($betArray as $bet)

            $grandTotalSales = 0;
            for ($i=0;$i<count($betFormRowsArray);$i++) {
                $bn = BetNumber::find()
                    ->where(['betId'=>$betModel->id,'rowIndex'=>$i])
                    ->with('betDetails.betDetailReject')
                    ->one();
                $bds = $bn->betDetails;
                $totalSales = $bn->totalBet;
                $companyCodesCount = count($bn->companyCodes);
                $drawDatesCount = count($bn->drawDates);
                $totalReject = 0;

                /*$soldBig = $bn->big;
                $soldSmall = $bn->small;
                $sold4a  = $bn->{'4a'};
                $sold4b  = $bn->{'4b'};
                $sold4c  = $bn->{'4c'};
                $sold4d  = $bn->{'4d'};
                $sold4e  = $bn->{'4e'};
                $sold4f  = $bn->{'4f'};
                $sold3abc  = $bn->{'3abc'};
                $sold3a  = $bn->{'3a'};
                $sold3b  = $bn->{'3b'};
                $sold3c  = $bn->{'3c'};
                $sold3d  = $bn->{'3d'};
                $sold3e  = $bn->{'3e'};
                $sold5d  = $bn->{'5d'};
                $sold6d  = $bn->{'6d'};*/

                $soldBig = null;
                $soldSmall = null;
                $sold4a  = null;
                $sold4b  = null;
                $sold4c  = null;
                $sold4d  = null;
                $sold4e  = null;
                $sold4f  = null;
                $sold3abc  = null;
                $sold3a  = null;
                $sold3b  = null;
                $sold3c  = null;
                $sold3d  = null;
                $sold3e  = null;
                $sold5d  = null;
                $sold6d  = null;

                if (!empty($bds) && is_array($bds)) {
                    foreach ($bds as $bd) {
                        if ($bd->status != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                            $totalSales -= $bd->totalReject;
                            $totalReject += $bd->totalReject;
                        }

                        if ($bd->status != Yii::$app->params['BET']['DETAIL']['STATUS']['REJECTED']) {
                            $soldBig += $bd->big;
                            $soldSmall += $bd->small;
                            $sold4a += $bd->{'4a'};
                            $sold4b += $bd->{'4b'};
                            $sold4c += $bd->{'4c'};
                            $sold4d += $bd->{'4d'};
                            $sold4e += $bd->{'4e'};
                            $sold4f += $bd->{'4f'};
                            $sold3abc += $bd->{'3abc'};
                            $sold3a += $bd->{'3a'};
                            $sold3b += $bd->{'3b'};
                            $sold3c += $bd->{'3c'};
                            $sold3d += $bd->{'3d'};
                            $sold3e += $bd->{'3e'};
                            $sold5d += $bd->{'5d'};
                            $sold6d += $bd->{'6d'};
                        }
                    }

                    if ($totalSales <= 0) { //Means the number is totally rejected
                        $bn->status = Yii::$app->params['BET']['NUMBER']['STATUS']['REJECTED'];
                    } else if ($totalSales > 0 && $totalReject > 0) {
                        $bn->status = Yii::$app->params['BET']['NUMBER']['STATUS']['LIMITED'];
                    } else if ($totalSales > 0 && $totalReject == 0) {
                        $bn->status = Yii::$app->params['BET']['NUMBER']['STATUS']['ACCEPTED'];
                    }
                } else {
                    $bn->status = Yii::$app->params['BET']['NUMBER']['STATUS']['REJECTED'];
                }

                $soldBig = CommonClass::adjustSalesBet($soldBig,$bn->big*$companyCodesCount*$drawDatesCount);
                $soldSmall = CommonClass::adjustSalesBet($soldSmall,$bn->small*$companyCodesCount*$drawDatesCount);
                $sold4a = CommonClass::adjustSalesBet($sold4a,$bn->{'4a'}*$companyCodesCount*$drawDatesCount);
                $sold4b = CommonClass::adjustSalesBet($sold4b,$bn->{'4b'}*$companyCodesCount*$drawDatesCount);
                $sold4c = CommonClass::adjustSalesBet($sold4c,$bn->{'4c'}*$companyCodesCount*$drawDatesCount);
                $sold4d = CommonClass::adjustSalesBet($sold4d,$bn->{'4d'}*$companyCodesCount*$drawDatesCount);
                $sold4e = CommonClass::adjustSalesBet($sold4e,$bn->{'4e'}*$companyCodesCount*$drawDatesCount);
                $sold4f = CommonClass::adjustSalesBet($sold4f,$bn->{'4f'}*$companyCodesCount*$drawDatesCount);
                $sold3abc = CommonClass::adjustSalesBet($sold3abc,$bn->{'3abc'}*$companyCodesCount*$drawDatesCount);
                $sold3a = CommonClass::adjustSalesBet($sold3a,$bn->{'3a'}*$companyCodesCount*$drawDatesCount);
                $sold3b = CommonClass::adjustSalesBet($sold3b,$bn->{'3b'}*$companyCodesCount*$drawDatesCount);
                $sold3c = CommonClass::adjustSalesBet($sold3c,$bn->{'3c'}*$companyCodesCount*$drawDatesCount);
                $sold3d = CommonClass::adjustSalesBet($sold3d,$bn->{'3d'}*$companyCodesCount*$drawDatesCount);
                $sold3e = CommonClass::adjustSalesBet($sold3e,$bn->{'3e'}*$companyCodesCount*$drawDatesCount);
                $sold5d = CommonClass::adjustSalesBet($sold5d,$bn->{'5d'}*$companyCodesCount*$drawDatesCount);
                $sold6d = CommonClass::adjustSalesBet($sold6d,$bn->{'6d'}*$companyCodesCount*$drawDatesCount);
                
                $bn->soldBig = $soldBig;
                $bn->soldSmall = $soldSmall;
                $bn->sold4a = $sold4a;
                $bn->sold4b = $sold4b;
                $bn->sold4c = $sold4c;
                $bn->sold4d = $sold4d;
                $bn->sold4e = $sold4e;
                $bn->sold4f = $sold4f;
                $bn->sold3abc = $sold3abc;
                $bn->sold3a = $sold3a;
                $bn->sold3b = $sold3b;
                $bn->sold3c = $sold3c;
                $bn->sold3d = $sold3d;
                $bn->sold3e = $sold3e;
                $bn->sold5d = $sold5d;
                $bn->sold6d = $sold6d;

                $totalSales = $soldBig+$soldSmall+$sold4a+$sold4b+$sold4c+$sold4d+$sold4e+$sold4f;
                $totalSales += $sold3abc+$sold3a+$sold3b+$sold3c+$sold3d+$sold3e;
                $totalSales += $sold5d+$sold6d;

                $bn->totalSales = $totalSales;
                $bn->totalReject = round($bn->totalBet-$totalSales,2);
                $grandTotalSales += $totalSales;

                if (!$bn->save()) {
                    Yii::error($bn->errors);
                    return $bn;
                }
            }

            //Update the totalSales and totalCommission columns
            $grandTotalSales = round($grandTotalSales,2);
            $betModel->totalSales = $grandTotalSales;
            $betModel->ownCommission = round($grandTotalOwnCommission,2);
            $betModel->totalCommission = round($grandTotalCommission,2);
            if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
                $betModel->extraCommission = round($grandTotalExtraCommission,2);
                $betModel->totalSuperiorCommission = round($grandTotalSuperiorCommission,2);
            }
            if (!$betModel->save()) {
                Yii::error($betModel->errors);
                return $betModel;
            }

            if ($grandTotalSales > 0) {
                //Proceed to update the user balance
                $userDetail->outstandingBet += $grandTotalSales;
                if (!$userDetail->save()) {
                    Yii::error($userDetail->errors);
                    return $userDetail;
                }
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

    public function actionVoid() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $betVoidAllowMinutes = Yii::$app->params['GLOBAL']['BET_VOID_ALLOW_MINUTES'];
        $master = Master::findOne(['id'=>Yii::$app->user->identity->masterId]);
        if ($master) {
            $betVoidAllowMinutes = $master->voidBetMinutes;
        }

        $voidArray = $params['voidArray']; //Array consists of bet id(s)
        //Look for voidable bet(s)
        $bds = BetDetail::find()
            ->where(['id'=>$voidArray])
            ->andWhere('date_add(createdAt, interval '.$betVoidAllowMinutes.' minute) > now()')
            ->all();

        $voidCount = 0;
        $totalVoid = 0;
        foreach ($bds as $bd) {
            $bd->status = Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED'];
            $bd->voidDate = new Expression('NOW()');
            if (!$bd->save()) {
                Yii::error($bd->errors);
                return $bd;
            }
            $totalVoid += $bd->totalSales;
            $voidCount++;
        }

        if ($totalVoid > 0) {
            $totalVoid = round($totalVoid,2);
            //Proceed to update the user balance
            $userDetail = UserDetail::findOne(['userId'=>Yii::$app->user->identity->getId()]);
            $userDetail->outstandingBet -= $totalVoid;
            if (!$userDetail->save()) {
                Yii::error($userDetail->errors);
                return $userDetail;
            }
        }


        $betIdCount = count($voidArray);
        $result = ["betIdCount"=>$betIdCount,"voidCount"=>$voidCount,"betDetailIdArray"=>$voidArray];

        return $result;
    }

    //Get voidable bets (based on the global params BET_VOID_ALLOW_MINUTES)
    public function actionGetVoidableBets() {
        $request = Yii::$app->request;
        $params = $request->get();

        $betVoidAllowMinutes = Yii::$app->params['GLOBAL']['BET_VOID_ALLOW_MINUTES'];
        $master = Master::findOne(['id'=>Yii::$app->user->identity->masterId]);
        if ($master) {
            $betVoidAllowMinutes = $master->voidBetMinutes;
        }

        $bds = BetDetail::find()
            ->with(['companyDraw.company'])
            ->where(['status'=>[Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED'],Yii::$app->params['BET']['DETAIL']['STATUS']['LIMITED']]])
            ->andWhere(['createdBy'=>Yii::$app->user->identity->getId()])
            ->andWhere('date_add(createdAt, interval '.$betVoidAllowMinutes.' minute) > now()')
            ->asArray()
            ->all();

        return $bds;
    }

    public function actionGetVoidedBets() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $betDetailIdArray = $params['betDetailIdArray'] ?? null;

        $bds = BetDetail::find()
            ->with(['companyDraw.company'])
            ->where(['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']])
            ->andWhere(['createdBy'=>Yii::$app->user->identity->getId()]);
        if (!empty($betDetailIdArray)) {
            $bds = $bds->andWhere(['id'=>$betDetailIdArray]);
        }
        $bds = $bds->all();

        return $bds;
    }

    public function actionGetBetSlipHistory() {
        $request = Yii::$app->request;
        $params = $request->get();

        $mode = $params["mode"] ?? 1; //1 - Get the records (with pagination applied); 2 - Get total items count
        $pageSize = $params["pageSize"] ?? Yii::$app->params["GLOBAL"]["RECORDS_PER_PAGE"];
        $offset = $params["offset"] ?? 0;
        $drawDateStart = !empty($params["drawDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateStart"])) : null;
        $drawDateEnd = !empty($params["drawDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["drawDateEnd"])) : null;
        $betDateStart = !empty($params["betDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["betDateStart"])) : null;
        $betDateEnd = !empty($params["betDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["betDateEnd"])) : null;

        $createdByArray = $params["createdByArray"] ?? [];
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Get all the agents under the master
            $agents = User::findAll(['masterId'=>Yii::$app->user->identity->masterId,'userType'=>Yii::$app->params['USER']['TYPE']['AGENT']]);
            foreach ($agents as $agent) {
                $createdByArray[] = $agent->id;
                $players = $agent->players;
                foreach ($players as $player) {
                    $createdByArray[] = $player->id;
                }
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                $createdByArray[] = $player->id;
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
        }

        $bets = Bet::find()
            ->alias('b')
            ->where(['b.createdBy'=>$createdByArray])
            ->with(['creator','betDetails'])
            ->orderBy('b.createdAt');
        if (!empty($drawDateStart) && !empty($drawDateEnd)) {
            $subquery = (new Query())
                ->select('id')
                ->from('bet_detail bd')
                ->where('bd.betId = b.id')
                ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd]);
            $bets = $bets->andWhere(['exists', $subquery]);
        }
        if (!empty($betDateStart) && !empty($betDateEnd)) {
            $bets = $bets->andWhere(['between','createdAt',$betDateStart,$betDateEnd]);
        }
        if ($mode == 1) { //Get the records (with pagination applied)
            $bets = $bets->limit($pageSize)
                ->offset($offset)
                ->all();
        } else { //Get total items count
            $bets = $bets->count();
            return $bets;
        }

        $result = [];
        for ($i=0;$i<count($bets);$i++) {
            $result[$i] = ArrayHelper::toArray($bets[$i]);
            $voidedBetsCount = 0;
            foreach ($bets[$i]->betDetails as $betDetail) {
                if ($betDetail->status == Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']) {
                    $voidedBetsCount++;
                }
            }
            $result[$i]["creator"] = $bets[$i]->creator;
            $result[$i]["slipText"] = $bets[$i]->slipText;
            $result[$i]["voidedBetsCount"] = $voidedBetsCount;
        }

        return $result;
    }

    public function actionGetBetNumberHistory() {
        $request = Yii::$app->request;
        $params = $request->get();

        $mode = $params["mode"] ?? 1; //1 - Get the records (with pagination applied); 2 - Get total items count
        $pageSize = $params["pageSize"] ?? Yii::$app->params["GLOBAL"]["RECORDS_PER_PAGE"];
        $offset = $params["offset"] ?? 0;
        $drawDateStart = !empty($params["drawDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateStart"])) : null;
        $drawDateEnd = !empty($params["drawDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["drawDateEnd"])) : null;
        $betDateStart = !empty($params["betDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["betDateStart"])) : null;
        $betDateEnd = !empty($params["betDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["betDateEnd"])) : null;
        $number = $params["number"] ?? null;

        $createdByArray = $params["createdByArray"] ?? [];
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Get all the agents under the master
            $agents = User::findAll(['masterId'=>Yii::$app->user->identity->masterId,'userType'=>Yii::$app->params['USER']['TYPE']['AGENT']]);
            foreach ($agents as $agent) {
                $createdByArray[] = $agent->id;
                $players = $agent->players;
                foreach ($players as $player) {
                    $createdByArray[] = $player->id;
                }
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                $createdByArray[] = $player->id;
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
        }

        $bds = BetDetail::find()
            ->with(['creator','companyDraw.company'])
            ->where(['createdBy'=>$createdByArray]);
        if (!empty($drawDateStart) && !empty($drawDateEnd)) {
            $bds = $bds->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd]);
        }
        if (!empty($betDateStart) && !empty($betDateEnd)) {
            $bds = $bds->andWhere(['between','createdAt',$betDateStart,$betDateEnd]);
        }
        if (!empty($number)) {
            $bds = $bds->andWhere(['number'=>$number]);
        }
        if ($mode == 1) { //Get the records (with pagination applied)
            $bds = $bds->limit($pageSize)
                ->offset($offset)
                ->all();
        } else { //Get total items count
            $bds = $bds->count();
            return $bds;
        }

        $result = [];
        $totalBig = $totalSmall = $total4a = $total4b = $total4c = $total4d = $total4e = $total4f = 0;
        $total3abc = $total3a = $total3b = $total3c = $total3d = $total3e = $total5d = $total6d = 0;
        $grandTotal = 0;
        for ($i=0;$i<count($bds);$i++) {
            $result[$i] = ArrayHelper::toArray($bds[$i]);
            $total = $result[$i]["big"]+$result[$i]["small"]+$result[$i]["4a"]+$result[$i]["4b"]+$result[$i]["4c"];
            $total += $result[$i]["4d"]+$result[$i]["4e"]+$result[$i]["4f"];
            $total += $result[$i]["3abc"]+$result[$i]["3a"]+$result[$i]["3b"];
            $total += $result[$i]["3c"]+$result[$i]["3d"]+$result[$i]["3e"];
            $total += $result[$i]["5d"]+$result[$i]["6d"];
            $result[$i]["total"] = round($total,3);
            $result[$i]["creator"] = $bds[$i]->creator;
            $result[$i]["companyDraw"] = $bds[$i]->companyDraw;

            $totalBig += $result[$i]["big"];
            $totalSmall += $result[$i]["small"];
            $total4a += $result[$i]["4a"];
            $total4b += $result[$i]["4b"];
            $total4c += $result[$i]["4c"];
            $total4d += $result[$i]["4d"];
            $total4e += $result[$i]["4e"];
            $total4f += $result[$i]["4f"];
            $total3abc += $result[$i]["3abc"];
            $total3a += $result[$i]["3a"];
            $total3b += $result[$i]["3b"];
            $total3c += $result[$i]["3c"];
            $total3d += $result[$i]["3d"];
            $total3e += $result[$i]["3e"];
            $total5d += $result[$i]["5d"];
            $total6d += $result[$i]["6d"];

            $grandTotal += $total;
        }

        $resultArray = [
            'numbers'=>$result,
            'totalBig' => round($totalBig,3),
            'totalSmall' => round($totalSmall,3),
            'total4a' => round($total4a,3),
            'total4b' => round($total4b,3),
            'total4c' => round($total4c,3),
            'total4d' => round($total4d,3),
            'total4e' => round($total4e,3),
            'total4f' => round($total4f,3),
            'total3abc' => round($total3abc,3),
            'total3a' => round($total3a,3),
            'total3b' => round($total3b,3),
            'total3c' => round($total3c,3),
            'total3d' => round($total3d,3),
            'total3e' => round($total3e,3),
            'total5d' => round($total5d,3),
            'total6d' => round($total6d,3),
            'grandTotal'=> round($grandTotal,3)
        ];
        return $resultArray;
    }

    public function actionGetVoidBetHistory() {
        $request = Yii::$app->request;
        $params = $request->get();

        $mode = $params["mode"] ?? 1; //1 - Get the records (with pagination applied); 2 - Get total items count
        $pageSize = $params["pageSize"] ?? Yii::$app->params["GLOBAL"]["RECORDS_PER_PAGE"];
        $offset = $params["offset"] ?? 0;
        $drawDateStart = !empty($params["drawDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateStart"])) : null;
        $drawDateEnd = !empty($params["drawDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["drawDateEnd"])) : null;
        $betDateStart = !empty($params["betDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["betDateStart"])) : null;
        $betDateEnd = !empty($params["betDateEnd"]) ? Date('Y-m-d 23:59:59', strtotime($params["betDateEnd"])) : null;
        $number = $params["number"] ?? null;

        $createdByArray = $params["createdByArray"] ?? [];
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Get all the agents under the master
            $agents = User::findAll(['masterId'=>Yii::$app->user->identity->masterId,'userType'=>Yii::$app->params['USER']['TYPE']['AGENT']]);
            foreach ($agents as $agent) {
                $createdByArray[] = $agent->id;
                $players = $agent->players;
                foreach ($players as $player) {
                    $createdByArray[] = $player->id;
                }
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                $createdByArray[] = $player->id;
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $createdByArray[] = Yii::$app->user->identity->getId();
        }

        $bds = BetDetail::find()
            ->with(['creator','companyDraw.company'])
            ->where(['createdBy'=>$createdByArray,'status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]);
        if (!empty($drawDateStart) && !empty($drawDateEnd)) {
            $bds = $bds->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd]);
        }
        if (!empty($betDateStart) && !empty($betDateEnd)) {
            $bds = $bds->andWhere(['between','createdAt',$betDateStart,$betDateEnd]);
        }
        if (!empty($number)) {
            $bds = $bds->andWhere(['number'=>$number]);
        }
        if ($mode == 1) { //Get the records (with pagination applied)
            $bds = $bds->limit($pageSize)
                ->offset($offset)
                ->all();
        } else { //Get total items count
            $bds = $bds->count();
            return $bds;
        }

        $result = [];
        $totalBig = $totalSmall = $total4a = $total4b = $total4c = $total4d = $total4e = $total4f = 0;
        $total3abc = $total3a = $total3b = $total3c = $total3d = $total3e = $total5d = $total6d = 0;
        $grandTotal = 0;
        for ($i=0;$i<count($bds);$i++) {
            $result[$i] = ArrayHelper::toArray($bds[$i]);
            $total = $result[$i]["big"]+$result[$i]["small"]+$result[$i]["4a"]+$result[$i]["4b"]+$result[$i]["4c"];
            $total += $result[$i]["4d"]+$result[$i]["4e"]+$result[$i]["4f"];
            $total += $result[$i]["3abc"]+$result[$i]["3a"]+$result[$i]["3b"];
            $total += $result[$i]["3c"]+$result[$i]["3d"]+$result[$i]["3e"];
            $total += $result[$i]["5d"]+$result[$i]["6d"];
            $result[$i]["total"] = round($total,3);
            $result[$i]["creator"] = $bds[$i]->creator;
            $result[$i]["companyDraw"] = $bds[$i]->companyDraw;

            $totalBig += $result[$i]["big"];
            $totalSmall += $result[$i]["small"];
            $total4a += $result[$i]["4a"];
            $total4b += $result[$i]["4b"];
            $total4c += $result[$i]["4c"];
            $total4d += $result[$i]["4d"];
            $total4e += $result[$i]["4e"];
            $total4f += $result[$i]["4f"];
            $total3abc += $result[$i]["3abc"];
            $total3a += $result[$i]["3a"];
            $total3b += $result[$i]["3b"];
            $total3c += $result[$i]["3c"];
            $total3d += $result[$i]["3d"];
            $total3e += $result[$i]["3e"];
            $total5d += $result[$i]["5d"];
            $total6d += $result[$i]["6d"];

            $grandTotal += $total;
        }

        $resultArray = [
            'numbers'=>$result,
            'totalBig' => round($totalBig,3),
            'totalSmall' => round($totalSmall,3),
            'total4a' => round($total4a,3),
            'total4b' => round($total4b,3),
            'total4c' => round($total4c,3),
            'total4d' => round($total4d,3),
            'total4e' => round($total4e,3),
            'total4f' => round($total4f,3),
            'total3abc' => round($total3abc,3),
            'total3a' => round($total3a,3),
            'total3b' => round($total3b,3),
            'total3c' => round($total3c,3),
            'total3d' => round($total3d,3),
            'total3e' => round($total3e,3),
            'total5d' => round($total5d,3),
            'total6d' => round($total6d,3),
            'grandTotal'=> round($grandTotal,3)
        ];
        return $resultArray;
    }

    private function insertBetDetail($number,$bet,$drawDate,$companyId,$companyCode,$masterId,$betModelId,$betNumberId,
                                     $total4dCommRate,$superior4dCommRate,$total6dCommRate,$superior6dCommRate,
                                     $totalGdCommRate,$superiorGdCommRate,
                                     $own4dCommRate,$extra4dCommRate,$own6dCommRate,$extra6dCommRate,
                                     $ownGdCommRate,$extraGdCommRate,
                                     $isIBox = false,$iBoxNumbersCount = null) {
        $balanceArray = CommonClass::getAvailableBalance($number,$drawDate,$companyId,$masterId);

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

        if ($isIBox) {
            $big = !empty($big) ? round($big/$iBoxNumbersCount,3) : null;
            $small = !empty($small) ? round($small/$iBoxNumbersCount,3) : null;
            $amount4a = !empty($amount4a) ? round($amount4a/$iBoxNumbersCount,3) : null;
            $amount4b = !empty($amount4b) ? round($amount4b/$iBoxNumbersCount,3) : null;
            $amount4c = !empty($amount4c) ? round($amount4c/$iBoxNumbersCount,3) : null;
            $amount4d = !empty($amount4d) ? round($amount4d/$iBoxNumbersCount,3) : null;
            $amount4e = !empty($amount4e) ? round($amount4e/$iBoxNumbersCount,3) : null;
            $amount4f = !empty($amount4f) ? round($amount4f/$iBoxNumbersCount,3) : null;
            $amount3abc = !empty($amount3abc) ? round($amount3abc/$iBoxNumbersCount,3) : null;
            $amount3a = !empty($amount3a) ? round($amount3a/$iBoxNumbersCount,3) : null;
            $amount3b = !empty($amount3b) ? round($amount3b/$iBoxNumbersCount,3) : null;
            $amount3c = !empty($amount3c) ? round($amount3c/$iBoxNumbersCount,3) : null;
            $amount3d = !empty($amount3d) ? round($amount3d/$iBoxNumbersCount,3) : null;
            $amount3e = !empty($amount3e) ? round($amount3e/$iBoxNumbersCount,3) : null;
            $amount5d = !empty($amount5d) ? round($amount5d/$iBoxNumbersCount,3) : null;
            $amount6d = !empty($amount6d) ? round($amount6d/$iBoxNumbersCount,3) : null;
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

        $total4dSales = $big+$small+$amount4a+$amount4b+$amount4c+$amount4d+$amount4e+$amount4f;
        $total4dSales += $amount3abc+$amount3a+$amount3b+$amount3c+$amount3d+$amount3e;
        $total6dSales = $amount5d+$amount6d;
        $totalSales = $total4dSales+$total6dSales;

        $bd = new BetDetail();
        $bd->number = $number;
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

        $total4dOwnCommission = 0;
        $total4dExtraCommission = 0;
        $total4dCommission = 0;
        $total4dSuperiorCommission = 0;
        if ($total4dSales > 0) {
            if ($companyCode == Yii::$app->params['COMPANY']['CODE']['GD']) {
                $total4dOwnCommission = round($total4dSales*$ownGdCommRate/100,3);
                $total4dExtraCommission = $extraGdCommRate > 0 ? round($total4dSales*$extraGdCommRate/100,3) : null;
                $total4dCommission = $total4dOwnCommission+$total4dExtraCommission;
                //$total4dCommission = round($total4dSales*$totalGdCommRate/100,2);
                if ($superiorGdCommRate > 0) {
                    $total4dSuperiorCommission = round($total4dSales * $superiorGdCommRate/100,3);
                }
            } else {
                $total4dOwnCommission = round($total4dSales*$own4dCommRate/100,3);
                $total4dExtraCommission = $extra4dCommRate > 0 ? round($total4dSales*$extra4dCommRate/100,3) : null;
                $total4dCommission = $total4dOwnCommission+$total4dExtraCommission;
                //$total4dCommission = round($total4dSales*$total4dCommRate/100,2);
                if ($superior4dCommRate > 0) {
                    $total4dSuperiorCommission = round($total4dSales * $superior4dCommRate / 100, 3);
                }
            }
        }

        $total6dOwnCommission = 0;
        $total6dExtraCommission = 0;
        $total6dCommission = 0;
        $total6dSuperiorCommission = 0;
        if ($total6dSales > 0) {
            $total6dOwnCommission = round($total6dSales*$own6dCommRate/100,3);
            $total6dExtraCommission = $extra6dCommRate > 0 ? round($total6dSales*$extra6dCommRate/100,3) : null;
            $total6dCommission = $total6dOwnCommission+$total6dExtraCommission;
            //$total6dCommission = round($total6dSales*$total6dCommRate/100,2);
            if ($superior6dCommRate > 0) {
                $total6dSuperiorCommission = round($total6dSales * $superior6dCommRate / 100, 3);
            }
        }
        $totalOwnCommission = $total4dOwnCommission+$total6dOwnCommission;
        $totalExtraCommission = $total4dExtraCommission+$total6dExtraCommission;
        $totalCommission = $total4dCommission+$total6dCommission;
        $totalSuperiorCommission = $total4dSuperiorCommission+$total6dSuperiorCommission;

        $bd->totalSales = $totalSales;
        $totalReject = 0;
        if ($status != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
            $totalReject = $rejectBig+$rejectSmall+$reject4a+$reject4b+$reject4c+$reject4e+$reject4e+$reject4f;
            $totalReject += $reject3abc+$reject3a+$reject3b+$reject3c+$reject3d+$reject3e+$reject5d+$reject6d;
            $bd->totalReject = $totalReject;
        }
        $bd->ownCommission = $totalOwnCommission;
        $bd->totalCommission = $totalCommission;
        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $bd->extraCommission = $totalExtraCommission;
            $bd->totalSuperiorCommission = $totalSuperiorCommission;
        }
        $bd->drawDate = $drawDate->format('Y-m-d');
        $bd->companyDrawId = $balanceArray['companyDrawId'];
        $bd->betId = $betModelId;
        $bd->betNumberId = $betNumberId;
        if (!$bd->save()) {
            Yii::error($bd->errors);
            return $bd;
        }

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

            $bdr->totalReject = $totalReject;
            $bdr->betDetailId = $bd->id;
            if (!$bdr->save()) {
                Yii::error($bdr->errors);
                return $bdr;
            }
        }

        return array('status'=>$status,'totalSales'=>$totalSales,'totalReject'=>$totalReject,'totalOwnCommission'=>$totalOwnCommission,'totalExtraCommission'=>$totalExtraCommission,'totalCommission'=>$totalCommission,'totalSuperiorCommission'=>$totalSuperiorCommission);
    }

    /**
     * permutation function
     * @param str string to
     *  calculate permutation for
     * @param l starting index
     * @param r end index
     */
    private function permute($str, $l, $r, &$resultArray = [])
    {
        if ($l == $r) {
            if (!in_array($str, $resultArray)) {
                $resultArray[] = $str;
            }
        } else {
            for ($i = $l; $i <= $r; $i++)
            {
                $str = self::swap($str, $l, $i);
                self::permute($str, $l + 1, $r, $resultArray);
                $str = self::swap($str, $l, $i);
            }
        }
    }

    /**
     * Swap Characters at position
     * @param a string value
     * @param i position 1
     * @param j position 2
     * @return swapped string
     */
    private function swap($a, $i, $j)
    {
        $charArray = str_split($a);
        $temp = $charArray[$i] ;
        $charArray[$i] = $charArray[$j];
        $charArray[$j] = $temp;
        return implode($charArray);
    }
}
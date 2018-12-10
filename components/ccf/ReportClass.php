<?php

namespace app\components\ccf;

use app\models\Bet;
use app\models\BetDetail;
use app\models\CompanyDraw;
use app\models\User;
use Yii;
use yii\base\BaseObject;
use yii\db\Query;

class ReportClass extends BaseObject
{
    /*
     * Author : Chong Chee Fei
     * Created At : 2018-08-16
     * Report : ['FILE_TEMPLATE']['REPORT']['WIN_LOSS_DETAILS']
     * Format : Array
     * Description :
     * Construct content for the report and return the content as array
     */
    public static function getWinLossDetails($params) {
        $result = [];
        $drawDateStart = !empty($params["drawDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateStart"])) : null;
        $drawDateEnd = !empty($params["drawDateEnd"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateEnd"])) : null;

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Get all the agents under the master
            $agents = User::findAll(['masterId'=>Yii::$app->user->identity->masterId,'userType'=>Yii::$app->params['USER']['TYPE']['AGENT']]);
            $grandTotalUserSales = $grandTotalUserCommission = $grandTotalUserPayout = $grandTotalCollect = $grandTotalExtraCommission = 0;
            $grandTotalCompanySales = $grandTotalCompanyCommission = $grandTotalCompanySuperiorCommission = 0;
            $grandTotalCompanyPayout = $grandTotalCompanySuperiorBonus = $grandTotalCompanyBalance = 0;
            foreach ($agents as $agent) {
                //Calculate own account first
                $userTotalSales = $userTotalOwnCommission = $userTotalExtraCommission = $userTotalCommission = $userTotalWin = $userTotalSuperiorBonus = 0;

                $betDetails = BetDetail::find()
                    ->where(['createdBy'=>$agent->id])
                    ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd])
                    ->andWhere(['not',['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]])
                    ->all();

                //$processedBets = Yii::$app->user->identity->processedBets;
                foreach ($betDetails as $betDetail) {
                    $totalSales = $betDetail->totalSales;
                    $ownCommission = $betDetail->ownCommission;
                    $extraCommission = $betDetail->extraCommission;
                    $totalWin = $betDetail->totalWin;

                    $userTotalSales += $totalSales;
                    $userTotalOwnCommission += $ownCommission;
                    $userTotalExtraCommission += $extraCommission;
                    $userTotalCommission = $userTotalOwnCommission+$userTotalExtraCommission;
                    $userTotalWin += $totalWin;
                }

                $userTotalCollect = $userTotalWin+$userTotalOwnCommission+$userTotalExtraCommission-$userTotalSales;
                $companyTotalSales = $userTotalSales;
                $companyTotalCommission = $userTotalCommission;
                $companyTotalPayout = $userTotalWin;

                $companyTotalBalance = $userTotalSales-$userTotalCommission-$userTotalWin-$userTotalSuperiorBonus;
                $companyTotalBalance = $companyTotalBalance*-1;

                $rowArray[] = [
                    "name" => $agent->name,
                    "username" => $agent->username,
                    "sales" => $userTotalSales,
                    "commission" => $userTotalOwnCommission,
                    "payout" => $userTotalWin,
                    "collect" => $userTotalCollect,
                    "extraCommission" => $userTotalExtraCommission,
                    "companySales" => $companyTotalSales,
                    "companyCommission" => $companyTotalCommission,
                    "companyPayout" => $companyTotalPayout,
                    "balance" => $companyTotalBalance,
                ];

                $grandTotalUserSales += $userTotalSales;
                $grandTotalUserCommission += $userTotalOwnCommission;
                $grandTotalUserPayout += $userTotalWin;
                $grandTotalCollect += $userTotalCollect;
                $grandTotalExtraCommission += $userTotalExtraCommission;
                $grandTotalCompanySales += $companyTotalSales;
                $grandTotalCompanyCommission += $companyTotalCommission;
                $grandTotalCompanyPayout += $companyTotalPayout;
                $grandTotalCompanyBalance += $companyTotalBalance;

                //Proceed to calculate the players under this agent account
                $players = $agent->players;
                foreach ($players as $player) {
                    $userTotalSales = 0;
                    $userTotalOwnCommission = 0;
                    $userTotalExtraCommission = 0;
                    $userTotalCommission = 0;
                    $userTotalSuperiorCommission = 0;
                    $userTotalWin = 0;
                    $userTotalSuperiorBonus = 0;

                    $betDetails = BetDetail::find()
                        ->where(['createdBy'=>$player->id])
                        ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd])
                        ->andWhere(['not',['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]])
                        ->all();

                    foreach ($betDetails as $betDetail) {
                        $totalSales = $betDetail->totalSales;
                        $ownCommission = $betDetail->ownCommission;
                        $extraCommission = $betDetail->extraCommission;
                        $totalWin = $betDetail->totalWin;
                        $superiorCommission = $betDetail->totalSuperiorCommission;
                        $totalSuperiorBonus = $betDetail->totalSuperiorBonus;

                        $userTotalSales += $totalSales;
                        $userTotalOwnCommission += $ownCommission;
                        $userTotalExtraCommission += $extraCommission;
                        $userTotalSuperiorCommission += $superiorCommission;
                        $userTotalCommission = $userTotalOwnCommission+$userTotalExtraCommission;
                        $userTotalWin += $totalWin;
                        $userTotalSuperiorBonus += $totalSuperiorBonus;
                    }

                    $userTotalCollect = $userTotalWin+$userTotalOwnCommission+$userTotalExtraCommission-$userTotalSales;
                    $companyTotalSales = $userTotalSales;
                    $companyTotalCommission = $userTotalCommission;
                    $companyTotalSuperiorCommission = $userTotalSuperiorCommission;
                    $companyTotalPayout = $userTotalWin;
                    $companyTotalSuperiorBonus = $userTotalSuperiorBonus;

                    $companyTotalBalance = $userTotalSales-$userTotalCommission-$companyTotalSuperiorCommission-$userTotalWin-$userTotalSuperiorBonus;
                    $companyTotalBalance = $companyTotalBalance*-1;

                    $rowArray[] = [
                        "name" => $player->name,
                        "username" => $player->username,
                        "sales" => $userTotalSales,
                        "commission" => $userTotalOwnCommission,
                        "payout" => $userTotalWin,
                        "collect" => $userTotalCollect,
                        "extraCommission" => $userTotalExtraCommission,
                        "companySales" => $companyTotalSales,
                        "companyCommission" => $companyTotalCommission,
                        "superiorCommission" => $companyTotalSuperiorCommission,
                        "companyPayout" => $companyTotalPayout,
                        "companyTotalSuperiorBonus" => $companyTotalSuperiorBonus,
                        "balance" => $companyTotalBalance,
                    ];

                    $grandTotalUserSales += $userTotalSales;
                    $grandTotalUserCommission += $userTotalOwnCommission;
                    $grandTotalUserPayout += $userTotalWin;
                    $grandTotalCollect += $userTotalCollect;
                    $grandTotalExtraCommission += $userTotalExtraCommission;
                    $grandTotalCompanySales += $companyTotalSales;
                    $grandTotalCompanyCommission += $companyTotalCommission;
                    $grandTotalCompanySuperiorCommission += $companyTotalSuperiorCommission;
                    $grandTotalCompanyPayout += $companyTotalPayout;
                    $grandTotalCompanySuperiorBonus += $companyTotalSuperiorBonus;
                    $grandTotalCompanyBalance += $companyTotalBalance;
                }
            }
            $result["rowArray"] = $rowArray;
            $result["grandTotalUserSales"] = $grandTotalUserSales;
            $result["grandTotalUserCommission"] = $grandTotalUserCommission;
            $result["grandTotalUserPayout"] = $grandTotalUserPayout;
            $result["grandTotalCollect"] = $grandTotalCollect;
            $result["grandTotalExtraCommission"] = $grandTotalExtraCommission;
            $result["grandTotalCompanySales"] = $grandTotalCompanySales;
            $result["grandTotalCompanyCommission"] = $grandTotalCompanyCommission;
            $result["grandTotalCompanySuperiorCommission"] = $grandTotalCompanySuperiorCommission;
            $result["grandTotalCompanyPayout"] = $grandTotalCompanyPayout;
            $result["grandTotalCompanySuperiorBonus"] = $grandTotalCompanySuperiorBonus;
            $result["grandTotalCompanyBalance"] = $grandTotalCompanyBalance;
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $grandTotalUserSales = $grandTotalUserCommission = $grandTotalUserPayout = $grandTotalCollect = $grandTotalExtraCommission = 0;
            $grandTotalCompanySales = $grandTotalCompanyCommission = $grandTotalCompanySuperiorCommission = 0;
            $grandTotalCompanySuperiorBonus = $grandTotalCompanyPayout = $grandTotalCompanyBalance = 0;

            //Calculate own account first
            $userTotalSales = $userTotalOwnCommission = $userTotalExtraCommission = $userTotalCommission = $userTotalWin = 0;

            $betDetails = BetDetail::find()
                ->where(['createdBy'=>Yii::$app->user->identity->getId()])
                ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd])
                ->andWhere(['not',['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]])
                ->all();

            //$processedBets = Yii::$app->user->identity->processedBets;
            foreach ($betDetails as $betDetail) {
                $totalSales = $betDetail->totalSales;
                $ownCommission = $betDetail->ownCommission;
                $extraCommission = $betDetail->extraCommission;
                $totalWin = $betDetail->totalWin;

                $userTotalSales += $totalSales;
                $userTotalOwnCommission += $ownCommission;
                $userTotalExtraCommission += $extraCommission;
                $userTotalCommission = $userTotalOwnCommission+$userTotalExtraCommission;
                $userTotalWin += $totalWin;
            }

            $userTotalCollect = $userTotalWin+$userTotalOwnCommission-$userTotalSales;
            $companyTotalSales = $userTotalSales;
            $companyTotalCommission = $userTotalCommission;
            $companyTotalPayout = $userTotalWin;

            $companyTotalBalance = $userTotalSales-$userTotalCommission+$userTotalExtraCommission-$userTotalWin;
            $companyTotalBalance = $companyTotalBalance*-1;

            $rowArray[] = [
                "name" => Yii::$app->user->identity->name,
                "username" => Yii::$app->user->identity->username,
                "sales" => $userTotalSales,
                "commission" => $userTotalOwnCommission,
                "payout" => $userTotalWin,
                "collect" => $userTotalCollect,
                "extraCommission" => $userTotalExtraCommission,
                "companySales" => $companyTotalSales,
                "companyCommission" => $companyTotalCommission,
                "companyPayout" => $companyTotalPayout,
                "balance" => $companyTotalBalance,
            ];

            $grandTotalUserSales += $userTotalSales;
            $grandTotalUserCommission += $userTotalOwnCommission;
            $grandTotalUserPayout += $userTotalWin;
            $grandTotalCollect += $userTotalCollect;
            $grandTotalExtraCommission += $userTotalExtraCommission;
            $grandTotalCompanySales += $companyTotalSales;
            $grandTotalCompanyCommission += $companyTotalCommission;
            $grandTotalCompanyPayout += $companyTotalPayout;
            $grandTotalCompanyBalance += $companyTotalBalance;

            //Proceed to calculate the players under this agent account
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                $userTotalSales = 0;
                $userTotalOwnCommission = 0;
                $userTotalExtraCommission = 0;
                $userTotalCommission = 0;
                $userTotalSuperiorCommission = 0;
                $userTotalWin = 0;
                $userTotalSuperiorBonus = 0;

                $betDetails = BetDetail::find()
                    ->where(['createdBy'=>$player->id])
                    ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd])
                    ->andWhere(['not',['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]])
                    ->all();

                foreach ($betDetails as $betDetail) {
                    $totalSales = $betDetail->totalSales;
                    $ownCommission = $betDetail->ownCommission;
                    $extraCommission = $betDetail->extraCommission;
                    $totalWin = $betDetail->totalWin;
                    $superiorCommission = $betDetail->totalSuperiorCommission;
                    $totalSuperiorBonus = $betDetail->totalSuperiorBonus;

                    $userTotalSales += $totalSales;
                    $userTotalOwnCommission += $ownCommission;
                    $userTotalExtraCommission += $extraCommission;
                    $userTotalSuperiorCommission += $superiorCommission;
                    $userTotalCommission = $userTotalOwnCommission+$userTotalExtraCommission;
                    $userTotalWin += $totalWin;
                    $userTotalSuperiorBonus += $totalSuperiorBonus;
                }

                $userTotalCollect = $userTotalWin+$userTotalOwnCommission+$userTotalExtraCommission-$userTotalSales;
                $companyTotalSales = $userTotalSales;
                $companyTotalCommission = $userTotalCommission;
                $companyTotalSuperiorCommission = $userTotalSuperiorCommission;
                $companyTotalPayout = $userTotalWin;
                $companyTotalSuperiorBonus = $userTotalSuperiorBonus;

                $companyTotalBalance = $userTotalSales-$userTotalCommission-$companyTotalSuperiorCommission-$userTotalWin-$userTotalSuperiorBonus;
                $companyTotalBalance = $companyTotalBalance*-1;

                $rowArray[] = [
                    "name" => $player->name,
                    "username" => $player->username,
                    "sales" => $userTotalSales,
                    "commission" => $userTotalOwnCommission,
                    "payout" => $userTotalWin,
                    "collect" => $userTotalCollect,
                    "extraCommission" => $userTotalExtraCommission,
                    "companySales" => $companyTotalSales,
                    "companyCommission" => $companyTotalCommission,
                    "superiorCommission" => $companyTotalSuperiorCommission,
                    "companyPayout" => $companyTotalPayout,
                    "companyTotalSuperiorBonus" => $companyTotalSuperiorBonus,
                    "balance" => $companyTotalBalance,
                ];

                $grandTotalUserSales += $userTotalSales;
                $grandTotalUserCommission += $userTotalOwnCommission;
                $grandTotalUserPayout += $userTotalWin;
                $grandTotalCollect += $userTotalCollect;
                $grandTotalExtraCommission += $userTotalExtraCommission;
                $grandTotalCompanySales += $companyTotalSales;
                $grandTotalCompanyCommission += $companyTotalCommission;
                $grandTotalCompanySuperiorCommission += $companyTotalSuperiorCommission;
                $grandTotalCompanyPayout += $companyTotalPayout;
                $grandTotalCompanySuperiorBonus += $companyTotalSuperiorBonus;
                $grandTotalCompanyBalance += $companyTotalBalance;
            }
            $result["rowArray"] = $rowArray;
            $result["grandTotalUserSales"] = $grandTotalUserSales;
            $result["grandTotalUserCommission"] = $grandTotalUserCommission;
            $result["grandTotalUserPayout"] = $grandTotalUserPayout;
            $result["grandTotalCollect"] = $grandTotalCollect;
            $result["grandTotalExtraCommission"] = $grandTotalExtraCommission;
            $result["grandTotalCompanySales"] = $grandTotalCompanySales;
            $result["grandTotalCompanyCommission"] = $grandTotalCompanyCommission;
            $result["grandTotalCompanySuperiorCommission"] = $grandTotalCompanySuperiorCommission;
            $result["grandTotalCompanyPayout"] = $grandTotalCompanyPayout;
            $result["grandTotalCompanySuperiorBonus"] = $grandTotalCompanySuperiorBonus;
            $result["grandTotalCompanyBalance"] = $grandTotalCompanyBalance;
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $betDetails = BetDetail::find()
                ->where(['createdBy'=>Yii::$app->user->identity->getId()])
                ->andWhere(['between','drawDate',$drawDateStart,$drawDateEnd])
                ->andWhere(['not',['status'=>Yii::$app->params['BET']['DETAIL']['STATUS']['VOIDED']]])
                ->all();

            $grandTotalSales = 0;
            $grandTotalCommission = 0;
            $grandTotalWin = 0;
            foreach ($betDetails as $betDetail) {
                $totalSales = $betDetail->totalSales;
                $totalCommission = $betDetail->totalCommission;
                $totalWin = $betDetail->totalWin;

                $grandTotalSales += $totalSales;
                $grandTotalCommission += $totalCommission;
                $grandTotalWin += $totalWin;
            }

            $grandTotalBalance = $grandTotalSales-$grandTotalCommission-$grandTotalWin;
            $grandTotalBalance = $grandTotalBalance*-1;

            $rowArray[] = [
                "name" => Yii::$app->user->identity->name,
                "username" => Yii::$app->user->identity->username,
                "sales" => $grandTotalSales,
                "commission" => $grandTotalCommission,
                "payout" => $grandTotalWin,
                "balance" => $grandTotalBalance,
            ];
            $result["rowArray"] = $rowArray;
        }

        return $result;
    }

    /*
     * Author : Chong Chee Fei
     * Created At : 2018-08-16
     * Report : ['FILE_TEMPLATE']['REPORT']['DRAW_WINNING_NUMBER']
     * Format : Array
     * Description :
     * Construct content for the report and return the content as array
     */
    public static function getDrawWinningNumber($params) {
        $result = $rowArray = [];
        $grandTotalWin = $grandTotalSuperiorBonus = 0;
        $drawDateStart = !empty($params["drawDateStart"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateStart"])) : null;
        $drawDateEnd = !empty($params["drawDateEnd"]) ? Date('Y-m-d 00:00:00', strtotime($params["drawDateEnd"])) : null;

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['MASTER']) {
            //Get all the agents under the master
            $agents = User::findAll(['masterId'=>Yii::$app->user->identity->masterId,'userType'=>Yii::$app->params['USER']['TYPE']['AGENT']]);
            foreach ($agents as $agent) {
                $bds = BetDetail::find()
                    ->alias('bd')
                    ->with(['betDetailWins','companyDraw.company'])
                    ->where(['bd.createdBy'=>$agent->id])
                    ->andWhere(['bd.won'=>1])
                    ->andWhere(['between','bd.drawDate',$drawDateStart,$drawDateEnd])
                    ->all();

                foreach ($bds as $bd) {
                    $bdws = $bd->betDetailWins;
                    $drawDate = new \DateTime($bd->drawDate);
                    $drawDate = $drawDate->format('Y-m-d');
                    $betDate = new \DateTime($bd->createdAt);
                    $betDate = $betDate->format('Y-m-d');
                    foreach ($bdws as $bdw) {
                        $winPrizeType = null;
                        $winPrizeAmount = null;
                        $winPrizeType = CommonClass::getWinPrizeTypeText($bdw->winPrizeType);
                        $threeDigitPrize = false;
                        if ($bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE']) {
                            $threeDigitPrize = true;
                        }

                        $rowArray[] = [
                            "username" => $agent->username,
                            "drawDate" => $drawDate,
                            "company" => $bd->companyDraw->company->code,
                            "number" => $bd->number,
                            "betAmount" => $bdw->betAmount,
                            "prize" => $winPrizeType,
                            "threeDigitPrize" => $threeDigitPrize,
                            "prizeAmount" => $bdw->winPrizeAmount,
                            "totalWin" => $bdw->totalWin,
                            "betDate" => $betDate,
                            "remarks" => $bd->remarks
                        ];

                        $grandTotalWin += $bdw->totalWin;
                        $grandTotalSuperiorBonus += $bdw->superiorBonus;
                    }
                }

                //Proceed to calculate the players under this agent account
                $players = $agent->players;
                foreach ($players as $player) {
                    $bds = BetDetail::find()
                        ->alias('bd')
                        ->with(['betDetailWins','companyDraw.company'])
                        ->where(['bd.createdBy'=>$player->id])
                        ->andWhere(['bd.won'=>1])
                        ->andWhere(['between','bd.drawDate',$drawDateStart,$drawDateEnd])
                        ->all();

                    foreach ($bds as $bd) {
                        $bdws = $bd->betDetailWins;
                        foreach ($bdws as $bdw) {
                            $winPrizeType = null;
                            $winPrizeAmount = null;
                            $winPrizeType = CommonClass::getWinPrizeTypeText($bdw->winPrizeType);
                            $threeDigitPrize = false;
                            if ($bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE']
                                || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE']) {
                                $threeDigitPrize = true;
                            }

                            $rowArray[] = [
                                "username" => $player->username,
                                "drawDate" => $bd->drawDate,
                                "company" => $bd->companyDraw->company->code,
                                "number" => $bd->number,
                                "betAmount" => $bdw->betAmount,
                                "prize" => $winPrizeType,
                                "threeDigitPrize" => $threeDigitPrize,
                                "prizeAmount" => $bdw->winPrizeAmount,
                                "totalWin" => $bdw->totalWin,
                                "superiorBonus" => $bdw->superiorBonus,
                                "betDate" => $bd->createdAt,
                                "remarks" => $bd->remarks
                            ];

                            $grandTotalWin += $bdw->totalWin;
                            $grandTotalSuperiorBonus += $bdw->superiorBonus;
                        }
                    }
                }
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            $bds = BetDetail::find()
                ->alias('bd')
                ->with(['betDetailWins','companyDraw.company'])
                ->where(['bd.createdBy'=>Yii::$app->user->identity->getId()])
                ->andWhere(['bd.won'=>1])
                ->andWhere(['between','bd.drawDate',$drawDateStart,$drawDateEnd])
                ->all();

            foreach ($bds as $bd) {
                $bdws = $bd->betDetailWins;
                $drawDate = new \DateTime($bd->drawDate);
                $drawDate = $drawDate->format('Y-m-d');
                $betDate = new \DateTime($bd->createdAt);
                $betDate = $betDate->format('Y-m-d');
                foreach ($bdws as $bdw) {
                    $winPrizeType = null;
                    $winPrizeAmount = null;
                    $winPrizeType = CommonClass::getWinPrizeTypeText($bdw->winPrizeType);
                    $threeDigitPrize = false;
                    if ($bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE']) {
                        $threeDigitPrize = true;
                    }

                    $rowArray[] = [
                        "username" => Yii::$app->user->identity->username,
                        "drawDate" => $drawDate,
                        "company" => $bd->companyDraw->company->code,
                        "number" => $bd->number,
                        "betAmount" => $bdw->betAmount,
                        "prize" => $winPrizeType,
                        "threeDigitPrize" => $threeDigitPrize,
                        "prizeAmount" => $bdw->winPrizeAmount,
                        "totalWin" => $bdw->totalWin,
                        "betDate" => $betDate,
                        "remarks" => $bd->remarks
                    ];

                    $grandTotalWin += $bdw->totalWin;
                    $grandTotalSuperiorBonus += $bdw->superiorBonus;
                }
            }

            //Proceed to calculate the players under this agent account
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                $bds = BetDetail::find()
                    ->alias('bd')
                    ->with(['betDetailWins','companyDraw.company'])
                    ->where(['bd.createdBy'=>$player->id])
                    ->andWhere(['bd.won'=>1])
                    ->andWhere(['between','bd.drawDate',$drawDateStart,$drawDateEnd])
                    ->all();

                foreach ($bds as $bd) {
                    $bdws = $bd->betDetailWins;
                    foreach ($bdws as $bdw) {
                        $winPrizeType = null;
                        $winPrizeAmount = null;
                        $winPrizeType = CommonClass::getWinPrizeTypeText($bdw->winPrizeType);
                        $threeDigitPrize = false;
                        if ($bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE']
                            || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE']) {
                            $threeDigitPrize = true;
                        }

                        $rowArray[] = [
                            "username" => $player->username,
                            "drawDate" => $bd->drawDate,
                            "company" => $bd->companyDraw->company->code,
                            "number" => $bd->number,
                            "betAmount" => $bdw->betAmount,
                            "prize" => $winPrizeType,
                            "threeDigitPrize" => $threeDigitPrize,
                            "prizeAmount" => $bdw->winPrizeAmount,
                            "totalWin" => $bdw->totalWin,
                            "superiorBonus" => $bdw->superiorBonus,
                            "betDate" => $bd->createdAt,
                            "remarks" => $bd->remarks
                        ];

                        $grandTotalWin += $bdw->totalWin;
                        $grandTotalSuperiorBonus += $bdw->superiorBonus;
                    }
                }
            }
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $bds = BetDetail::find()
                ->alias('bd')
                ->with(['betDetailWins','companyDraw.company'])
                ->where(['bd.createdBy'=>Yii::$app->user->identity->getId()])
                ->andWhere(['bd.won'=>1])
                ->andWhere(['between','bd.drawDate',$drawDateStart,$drawDateEnd])
                ->all();

            foreach ($bds as $bd) {
                $bdws = $bd->betDetailWins;
                foreach ($bdws as $bdw) {
                    $winPrizeType = null;
                    $winPrizeAmount = null;
                    $winPrizeType = CommonClass::getWinPrizeTypeText($bdw->winPrizeType);
                    $threeDigitPrize = false;
                    if ($bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE']
                        || $bdw->winPrizeType == Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE']) {
                        $threeDigitPrize = true;
                    }

                    $rowArray[] = [
                        "username" => Yii::$app->user->identity->username,
                        "drawDate" => $bd->drawDate,
                        "company" => $bd->companyDraw->company->code,
                        "number" => $bd->number,
                        "betAmount" => $bdw->betAmount,
                        "prize" => $winPrizeType,
                        "threeDigitPrize" => $threeDigitPrize,
                        "prizeAmount" => $bdw->winPrizeAmount,
                        "totalWin" => $bdw->totalWin,
                        "betDate" => $bd->createdAt,
                        "remarks" => $bd->remarks
                    ];

                    $grandTotalWin += $bdw->totalWin;
                }
            }
        }
        $result["rowArray"] = $rowArray;
        $result["grandTotalWin"] = $grandTotalWin;
        $result["grandTotalSuperiorBonus"] = $grandTotalSuperiorBonus;

        return $result;
    }

    /*
     * Author : Chong Chee Fei
     * Created At : 2018-09-02
     * Report : ['FILE_TEMPLATE']['REPORT']['COMPANY_DRAW_RESULTS']
     * Format : Array
     * Description :
     * Construct content for the report and return the content as array
     */
    public static function getCompanyDrawResults($params) {
        $result = $magnumArray = $pmpArray = $totoArray = $singaporeArray = $sabahArray = $sandakanArray = $sarawakArray =[];
        $drawDate = new \DateTime($params["drawDate"]);
        $drawDate = $drawDate->format('Y-m-d');

        $companyDraws = CompanyDraw::findAll(['drawDate'=>$drawDate,'status'=>Yii::$app->params['COMPANY']['DRAW']['STATUS']['DRAWN']]);

        foreach ($companyDraws as $companyDraw) {
            switch ($companyDraw->company->code) {
                case Yii::$app->params['COMPANY']['CODE']['MAGNUM']:
                    $magnumArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $magnumArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $magnumArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $magnumArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $magnumArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $magnumArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $magnumArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $magnumArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $magnumArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $magnumArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $magnumArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $magnumArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $magnumArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $magnumArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $magnumArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $magnumArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $magnumArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $magnumArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $magnumArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $magnumArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $magnumArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $magnumArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $magnumArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['PMP']:
                    $pmpArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $pmpArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $pmpArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $pmpArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $pmpArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $pmpArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $pmpArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $pmpArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $pmpArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $pmpArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $pmpArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $pmpArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $pmpArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $pmpArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $pmpArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $pmpArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $pmpArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $pmpArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $pmpArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $pmpArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $pmpArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $pmpArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $pmpArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['TOTO']:
                    $totoArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $totoArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $totoArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $totoArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $totoArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $totoArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $totoArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $totoArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $totoArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $totoArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $totoArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $totoArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $totoArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $totoArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $totoArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $totoArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $totoArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $totoArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $totoArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $totoArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $totoArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $totoArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $totoArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    $totoArray["5d1stPrize"] = $companyDraw->{'5d1stPrize'};
                    $totoArray["5d2ndPrize"] = $companyDraw->{'5d2ndPrize'};
                    $totoArray["5d3rdPrize"] = $companyDraw->{'5d3rdPrize'};
                    $totoArray["5d4thPrize"] = $companyDraw->{'5d4thPrize'};
                    $totoArray["5d5thPrize"] = $companyDraw->{'5d5thPrize'};
                    $totoArray["5d6thPrize"] = $companyDraw->{'5d6thPrize'};
                    $totoArray["6d1stPrize"] = $companyDraw->{'6d1stPrize'};
                    $totoArray["6d2nd1Prize"] = $companyDraw->{'6d2nd1Prize'};
                    $totoArray["6d2nd2Prize"] = $companyDraw->{'6d2nd2Prize'};
                    $totoArray["6d3rd1Prize"] = $companyDraw->{'6d3rd1Prize'};
                    $totoArray["6d3rd2Prize"] = $companyDraw->{'6d3rd2Prize'};
                    $totoArray["6d4th1Prize"] = $companyDraw->{'6d4th1Prize'};
                    $totoArray["6d4th2Prize"] = $companyDraw->{'6d4th2Prize'};
                    $totoArray["6d5th1Prize"] = $companyDraw->{'6d5th1Prize'};
                    $totoArray["6d5th2Prize"] = $companyDraw->{'6d5th2Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['SINGAPORE']:
                    $singaporeArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $singaporeArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $singaporeArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $singaporeArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $singaporeArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $singaporeArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $singaporeArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $singaporeArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $singaporeArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $singaporeArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $singaporeArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $singaporeArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $singaporeArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $singaporeArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $singaporeArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $singaporeArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $singaporeArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $singaporeArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $singaporeArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $singaporeArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $singaporeArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $singaporeArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $singaporeArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['SABAH']:
                    $sabahArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $sabahArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $sabahArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $sabahArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $sabahArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $sabahArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $sabahArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $sabahArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $sabahArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $sabahArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $sabahArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $sabahArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $sabahArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $sabahArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $sabahArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $sabahArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $sabahArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $sabahArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $sabahArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $sabahArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $sabahArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $sabahArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $sabahArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['SANDAKAN']:
                    $sandakanArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $sandakanArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $sandakanArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $sandakanArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $sandakanArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $sandakanArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $sandakanArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $sandakanArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $sandakanArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $sandakanArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $sandakanArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $sandakanArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $sandakanArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $sandakanArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $sandakanArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $sandakanArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $sandakanArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $sandakanArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $sandakanArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $sandakanArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $sandakanArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $sandakanArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $sandakanArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
                case Yii::$app->params['COMPANY']['CODE']['SARAWAK']:
                    $sarawakArray["1stPrize"] = $companyDraw->{'1stPrize'};
                    $sarawakArray["2ndPrize"] = $companyDraw->{'2ndPrize'};
                    $sarawakArray["3rdPrize"] = $companyDraw->{'3rdPrize'};
                    $sarawakArray["special1Prize"] = $companyDraw->{'special1Prize'};
                    $sarawakArray["special2Prize"] = $companyDraw->{'special2Prize'};
                    $sarawakArray["special3Prize"] = $companyDraw->{'special3Prize'};
                    $sarawakArray["special4Prize"] = $companyDraw->{'special4Prize'};
                    $sarawakArray["special5Prize"] = $companyDraw->{'special5Prize'};
                    $sarawakArray["special6Prize"] = $companyDraw->{'special6Prize'};
                    $sarawakArray["special7Prize"] = $companyDraw->{'special7Prize'};
                    $sarawakArray["special8Prize"] = $companyDraw->{'special8Prize'};
                    $sarawakArray["special9Prize"] = $companyDraw->{'special9Prize'};
                    $sarawakArray["special10Prize"] = $companyDraw->{'special10Prize'};
                    $sarawakArray["consolation1Prize"] = $companyDraw->{'consolation1Prize'};
                    $sarawakArray["consolation2Prize"] = $companyDraw->{'consolation2Prize'};
                    $sarawakArray["consolation3Prize"] = $companyDraw->{'consolation3Prize'};
                    $sarawakArray["consolation4Prize"] = $companyDraw->{'consolation4Prize'};
                    $sarawakArray["consolation5Prize"] = $companyDraw->{'consolation5Prize'};
                    $sarawakArray["consolation6Prize"] = $companyDraw->{'consolation6Prize'};
                    $sarawakArray["consolation7Prize"] = $companyDraw->{'consolation7Prize'};
                    $sarawakArray["consolation8Prize"] = $companyDraw->{'consolation8Prize'};
                    $sarawakArray["consolation9Prize"] = $companyDraw->{'consolation9Prize'};
                    $sarawakArray["consolation10Prize"] = $companyDraw->{'consolation10Prize'};
                    break;
            }
        }

        $result["magnum"] = $magnumArray;
        $result["pmp"] = $pmpArray;
        $result["toto"] = $totoArray;
        $result["singapore"] = $singaporeArray;
        $result["sabah"] = $sabahArray;
        $result["sandakan"] = $sandakanArray;
        $result["sarawak"] = $sarawakArray;

        return $result;
    }
}
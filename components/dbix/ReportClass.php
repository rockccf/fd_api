<?php

namespace app\components\dbix;

use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class ReportClass extends BaseObject
{
    /*
     * Author : Chong Chee Fei
     * Created At : 2018-02-14
     * Report : ['FILE_TEMPLATE']['REPORT']['WIN_LOSS_DETAILS']
     * Format : Array
     * Description :
     * Construct content for the report and return the content as array
     */
    public static function getWinLossDetails($params) {
        $result = [];

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
            //Get the players under this agent account
            $players = Yii::$app->user->identity->players;
            foreach ($players as $player) {
                Yii::info("name = ".$player->name);
            }

            $rowArray = [];

           /* $rowArray["name"];
            $rowArray["username"];
            $rowArray["sales"];
            $rowArray["commission"];
            $rowArray["payout"];
            $rowArray["collect"];
            $rowArray["earnedCommission"]; //Superior commission, that the agent earns under this player
            $rowArray["companySales"];
            $rowArray["companyCommission"];
            $rowArray["companyPayout"];
            $rowArray["companyBalance"];

            $result["rowArray"] = $rowArray;
            $result["totalSales"];
            $result["totalCommission"];
            $result["totalPayout"];
            $result["totalCollect"];
            $result["totalEarnedCommission"];
            $result["totalCompanySales"];
            $result["totalCompanyCommission"];
            $result["totalPayout"];
            $result["totalSales"];*/
        } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $processedBets =  Yii::$app->user->identity->processedBets;

            $grandTotalSales = 0;
            $grandTotalCommission = 0;
            $grandTotalWin = 0;
            foreach ($processedBets as $processedBet) {
                $totalSales = $processedBet->totalSales;
                $totalCommission = $processedBet->totalCommission;
                $totalWin = $processedBet->totalWin;

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

}
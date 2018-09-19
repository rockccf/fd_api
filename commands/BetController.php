<?php
namespace app\commands;

use app\components\ccf\CommonClass;
use app\models\Bet;
use app\models\BetDetail;
use app\models\BetDetailWin;
use app\models\Company;
use app\models\CompanyDraw;
use app\models\Package;
use app\models\UserDetail;
use Yii;
use Goutte\Client;
use yii\console\Controller;
use yii\console\ExitCode;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
use yii\web\ServerErrorHttpException;

class BetController extends Controller
{
    //Use web scraper to get the company draw results
    public function actionIndex($drawDate = null) {
        $client = new Client();

        /*
         * http://www.win4d.com.my
         */
        //Check for Magnum/Toto/PMP/Sabah/Sandakan/Sarawak/Singapore 4D and Toto 5D 6D
        $crawler = $client->request('GET', 'http://www.win4d.com.my/');
        //$mCompleted = $pCompleted = $tCompleted = $sCompleted = $bCompleted = $kCompleted = $wCompleted = false;

        /*$contentArray = $crawler->filter('div.panel-heading');
        $companyCodeArray = [];
        foreach ($contentArray as $content) {
            $nodeValue = $content->nodeValue;

            if (strpos($nodeValue,'Magnum') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['MAGNUM'];
            } else if (strpos($nodeValue,'PMP') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['PMP'];
            } else if (strpos($nodeValue,'TOTO') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['TOTO'];
            } else if (strpos($nodeValue,'Singapore') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['SINGAPORE'];
            } else if (strpos($nodeValue,'Sabah') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['SABAH'];
            } else if (strpos($nodeValue,'Sandakan') !== false) {
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['SANDAKAN'];
            } else if (strpos($nodeValue,'Cash') !== false) { //Sarawak
                $companyCodeArray[] = Yii::$app->params['COMPANY']['CODE']['SARAWAK'];
            }
        }*/

        if (empty($drawDate)) {
            $drawDate = new \DateTime();
        } else {
            $drawDate = new \DateTime($drawDate);
        }
        $drawDate->setTime(0,0);
        $today = new \DateTime();
        Yii::info("commands\BetController starts, today = ".$today->format('Y-m-d').", drawDate = ".$drawDate->format('Y-m-d'));

        $companyDraws = CompanyDraw::findAll(['status'=>Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW'],'drawDate'=>$drawDate->format('Y-m-d')]);
        if (empty($companyDraws) || count($companyDraws) <= 0) {
            Yii::error("No available company draws.");
            return ExitCode::SOFTWARE;
        }

        $magnumArray["code"] = Yii::$app->params['COMPANY']['CODE']['MAGNUM'];
        $pmpArray["code"] = Yii::$app->params['COMPANY']['CODE']['PMP'];
        $totoArray["code"] = Yii::$app->params['COMPANY']['CODE']['TOTO'];
        $singaporeArray["code"] = Yii::$app->params['COMPANY']['CODE']['SINGAPORE'];
        $sabahArray["code"] = Yii::$app->params['COMPANY']['CODE']['SABAH'];
        $sandakanArray["code"] = Yii::$app->params['COMPANY']['CODE']['SANDAKAN'];
        $sarawakArray["code"] = Yii::$app->params['COMPANY']['CODE']['SARAWAK'];

        $dates = $crawler->filter('b.f_16'); //Draw dates for all the companies except 5d/6d
        $i = 0;
        foreach ($dates as $dateObj) {
            $value = trim($dateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,-10);
            if (empty($value)) {
                Yii::error("dateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value)) {
                Yii::error("dateObj, invalid date. i = $i, value = $value");
                continue;
            }
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("dateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }

            $i++;
        }
        //Make sure there are 9 dates found
        if ($i != 9) {
            Yii::error("dates, $i dates found.");
            return ExitCode::SOFTWARE;
        }

        $sixdDate = $crawler->filter('td.resultdrawdate'); //Draw date for toto 5d/6d
        $i = 0;
        foreach ($sixdDate as $sixdDateObj) {
            $value = trim($sixdDateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,-10);
            if (empty($value)) {
                Yii::error("sixdDateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value)) {
                Yii::error("sixdDateObj, invalid date. i = $i, value = $value");
                continue;
            }
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("sixdDateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }
            $i++;
        }
        //Make sure there's 1 date found
        if ($i != 1) {
            Yii::error("sixdDate, $i dates found.");
            return ExitCode::SOFTWARE;
        }

        $drawDate = $drawDate->format('Y-m-d');

        $fstNumbers = $crawler->filter('th.f_40'); //First, Second, and Third Prize Numbers
        $i = 0;
        foreach ($fstNumbers as $fstNumber) {
            $value = trim($fstNumber->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value)) {
                Yii::error("fstNumber, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            switch ($i) {
                case 0:
                    $magnumArray["1st"] = $value;
                    break;
                case 1:
                    $magnumArray["2nd"] = $value;
                    break;
                case 2:
                    $magnumArray["3rd"] = $value;
                    break;
                case 3:
                    $pmpArray["1st"] = $value;
                    break;
                case 4:
                    $pmpArray["2nd"] = $value;
                    break;
                case 5:
                    $pmpArray["3rd"] = $value;
                    break;
                case 6:
                    $totoArray["1st"] = $value;
                    break;
                case 7:
                    $totoArray["2nd"] = $value;
                    break;
                case 8:
                    $totoArray["3rd"] = $value;
                    break;
                case 9:
                    $singaporeArray["1st"] = $value;
                    break;
                case 10:
                    $singaporeArray["2nd"] = $value;
                    break;
                case 11:
                    $singaporeArray["3rd"] = $value;
                    break;
                case 12:
                    $sarawakArray["1st"] = $value;
                    break;
                case 13:
                    $sarawakArray["2nd"] = $value;
                    break;
                case 14:
                    $sarawakArray["3rd"] = $value;
                    break;
                case 15:
                    $sandakanArray["1st"] = $value;
                    break;
                case 16:
                    $sandakanArray["2nd"] = $value;
                    break;
                case 17:
                    $sandakanArray["3rd"] = $value;
                    break;
                case 18:
                    $sabahArray["1st"] = $value;
                    break;
                case 19:
                    $sabahArray["2nd"] = $value;
                    break;
                case 20:
                    $sabahArray["3rd"] = $value;
                    break;
            }
            $i++;
        }

        $specialConNumbers = $crawler->filter('tr.f_20 > td'); //Special & Consolation Prize Numbers
        $i = 0;
        foreach ($specialConNumbers as $specialConNumber) {
            $value = trim($specialConNumber->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value)) {
                Yii::error("specialConNumber, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            switch (true) {
                case ($i <= 9):
                    $magnumArray["specialArray"][] = $value;
                    break;
                case ($i <= 19):
                    $magnumArray["consoArray"][] = $value;
                    break;
                case ($i <= 29):
                    $pmpArray["specialArray"][] = $value;
                    break;
                case ($i <= 39):
                    $pmpArray["consoArray"][] = $value;
                    break;
                case ($i <= 49):
                    $totoArray["specialArray"][] = $value;
                    break;
                case ($i <= 59):
                    $totoArray["consoArray"][] = $value;
                    break;
                case ($i <= 69):
                    $singaporeArray["specialArray"][] = $value;
                    break;
                case ($i <= 79):
                    $singaporeArray["consoArray"][] = $value;
                    break;
                case ($i <= 89):
                    $sarawakArray["specialArray"][] = $value;
                    break;
                case ($i <= 99):
                    $sarawakArray["consoArray"][] = $value;
                    break;
                case ($i <= 109):
                    $sandakanArray["specialArray"][] = $value;
                    break;
                case ($i <= 119):
                    $sandakanArray["consoArray"][] = $value;
                    break;
                case ($i <= 129):
                    $sabahArray["specialArray"][] = $value;
                    break;
                case ($i <= 139):
                    $sabahArray["consoArray"][] = $value;
                    break;
            }
            $i++;
        }

        $toto5dArray["code"] = "T5";
        $toto6dArray["code"] = "T6";
        $toto5d6dNumbers = $crawler->filter('td.resultbottom'); //5D 6D numbers
        $i = 0;
        foreach ($toto5d6dNumbers as $toto5d6dNumber) {
            $value = trim($toto5d6dNumber->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value)) {
                Yii::error("toto5d6dNumber, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            switch ($i) {
                case 0:
                    $toto5dArray["1st"] = $value;
                    break;
                case 1:
                    $toto5dArray["4th"] = $value;
                    break;
                case 2:
                    $toto5dArray["2nd"] = $value;
                    break;
                case 3:
                    $toto5dArray["5th"] = $value;
                    break;
                case 4:
                    $toto5dArray["3rd"] = $value;
                    break;
                case 5:
                    $toto5dArray["6th"] = $value;
                    break;
                case 6:
                    $toto6dArray["1st"] = $value;
                    break;
                case 7:
                    $toto6dArray["2nd1"] = $value;
                    break;
                case 8:
                    $toto6dArray["2nd2"] = $value;
                    break;
                case 9:
                    $toto6dArray["3rd1"] = $value;
                    break;
                case 10:
                    $toto6dArray["3rd2"] = $value;
                    break;
                case 11:
                    $toto6dArray["4th1"] = $value;
                    break;
                case 12:
                    $toto6dArray["4th2"] = $value;
                    break;
                case 13:
                    $toto6dArray["5th1"] = $value;
                    break;
                case 14:
                    $toto6dArray["5th2"] = $value;
                    break;
            }
            $i++;
        }

        //print_r($magnumArray);
        //print_r($pmpArray);
        //print_r($totoArray);
        //print_r($singaporeArray);
        //print_r($sarawakArray);
        //print_r($sandakanArray);
        //print_r($sabahArray);
        //print_r($toto5dArray);
        //print_r($toto6dArray);

        if (!self::checkCompanyResults($magnumArray) || !self::checkCompanyResults($pmpArray) || !self::checkCompanyResults($totoArray)
            || !self::checkCompanyResults($singaporeArray) || !self::checkCompanyResults($sarawakArray) || !self::checkCompanyResults($sandakanArray)
            || !self::checkCompanyResults($sabahArray) || !self::checkCompanyResults($toto5dArray) || !self::checkCompanyResults($toto6dArray)) {
            Yii::error("checkCompanyResults false.");
            return ExitCode::SOFTWARE;
        }

        //Insert Results
        $dbTrans = CompanyDraw::getDb()->beginTransaction();
        try {
            self::insertResults($magnumArray,$drawDate);
            self::insertResults($pmpArray,$drawDate);
            self::insertResults($totoArray,$drawDate);
            self::insertResults($singaporeArray,$drawDate);
            self::insertResults($sabahArray,$drawDate);
            self::insertResults($sarawakArray,$drawDate);
            self::insertResults($sandakanArray,$drawDate);
            self::insertResults($toto5dArray,$drawDate);
            self::insertResults($toto6dArray,$drawDate);

            $dbTrans->commit();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        //Process Results
        $dbTrans = Bet::getDb()->beginTransaction();
        try {
            self::processResults($drawDate);

            $dbTrans->commit();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        Yii::info("commands\BetController ends");

        /*
         * https://www.check4d.com
         */
        //Check for Magnum/Toto/PMP 4D and Toto 5D 6D
        /*$crawler = $client->request('GET', 'https://www.check4d.com/');
        $crawler->filter('tr > a')->each(function ($node) {
            print $node->text()."\n";
        });

        //Check for Sabah Sarawak Sandakan
        $crawler = $client->request('GET', 'https://www.check4d.com/sabah-sarawak-4d-results/');

        //Check for Singapore
        $crawler = $client->request('GET', 'https://www.check4d.com/singapore-4d-results/');*/

        return ExitCode::OK;
    }

    //Check if the results for each company are retrieved and populated correctly
    private function checkCompanyResults($resultsArray) {
        $result = true;
        $code = $resultsArray["code"];

        switch ($code) {
            case Yii::$app->params['COMPANY']['CODE']['MAGNUM']:
            case Yii::$app->params['COMPANY']['CODE']['PMP']:
            case Yii::$app->params['COMPANY']['CODE']['TOTO']:
            case Yii::$app->params['COMPANY']['CODE']['SINGAPORE']:
            case Yii::$app->params['COMPANY']['CODE']['SABAH']:
            case Yii::$app->params['COMPANY']['CODE']['SANDAKAN']:
            case Yii::$app->params['COMPANY']['CODE']['SARAWAK']:
                foreach ($resultsArray as $key => $results) {
                    if ($key == "code") {
                        continue;
                    } else if ($key == "specialArray" || $key == "consoArray") {
                        foreach ($results as $subkey => $result) {
                            //Make sure each result length is 4
                            if (strlen($result) != 4) {
                                Yii::error("strlen(result) != 4, key = ".$key.", subkey = ".$subkey.", code = ".$code.", result = ".$result);
                                $result = false;
                                break;
                            }
                        }
                    } else {
                        //Make sure each result length is 4
                        if (strlen($results) != 4) {
                            Yii::error("strlen(results) != 4, key = ".$key.", code = ".$code.", results = ".$results);
                            $result = false;
                            break;
                        }
                    }
                }
                break;
            case 'T5': //Toto 5D
                if (strlen($resultsArray["1st"]) != 5) {
                    Yii::error("resultsArray['1st'] != 5, code = ".$code.", resultsArray['1st'] = ".$resultsArray['1st']);
                    $result = false;
                }
                if (strlen($resultsArray["2nd"]) != 5) {
                    Yii::error("resultsArray['2nd'] != 5, code = ".$code.", resultsArray['2nd'] = ".$resultsArray['2nd']);
                    $result = false;
                }
                if (strlen($resultsArray["3rd"]) != 5) {
                    Yii::error("resultsArray['3rd'] != 5, code = ".$code.", resultsArray['3rd'] = ".$resultsArray['3rd']);
                    $result = false;
                }
                if (strlen($resultsArray["4th"]) != 4) {
                    Yii::error("resultsArray['4th'] != 4, code = ".$code.", resultsArray['4th'] = ".$resultsArray['4th']);
                    $result = false;
                }
                if (strlen($resultsArray["5th"]) != 3) {
                    Yii::error("resultsArray['5th'] != 3, code = ".$code.", resultsArray['5th'] = ".$resultsArray['5th']);
                    $result = false;
                }
                if (strlen($resultsArray["6th"]) != 2) {
                    Yii::error("resultsArray['6th'] != 2, code = ".$code.", resultsArray['6th'] = ".$resultsArray['6th']);
                    $result = false;
                }
                break;
            case 'T6': //Toto 6D
                if (strlen($resultsArray["1st"]) != 6) {
                    Yii::error("resultsArray['1st'] != 6, code = ".$code.", resultsArray['1st'] = ".$resultsArray['1st']);
                    $result = false;
                }
                if (strlen($resultsArray["2nd1"]) != 5) {
                    Yii::error("resultsArray['2nd1'] != 5, code = ".$code.", resultsArray['2nd1'] = ".$resultsArray['2nd1']);
                    $result = false;
                }
                if (strlen($resultsArray["2nd2"]) != 5) {
                    Yii::error("resultsArray['2nd2'] != 5, code = ".$code.", resultsArray['2nd2'] = ".$resultsArray['2nd2']);
                    $result = false;
                }
                if (strlen($resultsArray["3rd1"]) != 4) {
                    Yii::error("resultsArray['3rd1'] != 4, code = ".$code.", resultsArray['3rd1'] = ".$resultsArray['3rd1']);
                    $result = false;
                }
                if (strlen($resultsArray["3rd2"]) != 4) {
                    Yii::error("resultsArray['3rd2'] != 4, code = ".$code.", resultsArray['3rd2'] = ".$resultsArray['3rd2']);
                    $result = false;
                }
                if (strlen($resultsArray["4th1"]) != 3) {
                    Yii::error("resultsArray['4th1'] != 3, code = ".$code.", resultsArray['4th1'] = ".$resultsArray['4th1']);
                    $result = false;
                }
                if (strlen($resultsArray["4th2"]) != 3) {
                    Yii::error("resultsArray['4th2'] != 3, code = ".$code.", resultsArray['4th2'] = ".$resultsArray['4th2']);
                    $result = false;
                }
                if (strlen($resultsArray["5th1"]) != 2) {
                    Yii::error("resultsArray['5th1'] != 2, code = ".$code.", resultsArray['5th1'] = ".$resultsArray['5th1']);
                    $result = false;
                }
                if (strlen($resultsArray["5th2"]) != 2) {
                    Yii::error("resultsArray['5th2'] != 2, code = ".$code.", resultsArray['5th2'] = ".$resultsArray['5th2']);
                    $result = false;
                }
                break;
        }

        return $result;
    }

    //Proceed to insert the results
    private function insertResults($resultsArray,$drawDate) {
        $code = $resultsArray["code"];

        //Proceed to insert the results
        switch ($code) {
            case Yii::$app->params['COMPANY']['CODE']['MAGNUM']:
            case Yii::$app->params['COMPANY']['CODE']['PMP']:
            case Yii::$app->params['COMPANY']['CODE']['TOTO']:
            case Yii::$app->params['COMPANY']['CODE']['SINGAPORE']:
            case Yii::$app->params['COMPANY']['CODE']['SABAH']:
            case Yii::$app->params['COMPANY']['CODE']['SANDAKAN']:
            case Yii::$app->params['COMPANY']['CODE']['SARAWAK']:
                $company = Company::findOne(['code'=>$code]);
                $companyDraw = CompanyDraw::findOne(['companyId'=>$company->id,'drawDate'=>$drawDate,'status'=>Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW']]);
                if ($companyDraw) {
                    $companyDraw->status = Yii::$app->params['COMPANY']['DRAW']['STATUS']['DRAWN'];
                    $companyDraw->checkResultsDate = new Expression('Now()');
                    $companyDraw->{'1stPrize'} = $resultsArray["1st"];
                    $companyDraw->{'2ndPrize'} = $resultsArray["2nd"];
                    $companyDraw->{'3rdPrize'} = $resultsArray["3rd"];
                    $companyDraw->special1Prize = $resultsArray["specialArray"][0];
                    $companyDraw->special2Prize = $resultsArray["specialArray"][1];
                    $companyDraw->special3Prize = $resultsArray["specialArray"][2];
                    $companyDraw->special4Prize = $resultsArray["specialArray"][3];
                    $companyDraw->special5Prize = $resultsArray["specialArray"][4];
                    $companyDraw->special6Prize = $resultsArray["specialArray"][5];
                    $companyDraw->special7Prize = $resultsArray["specialArray"][6];
                    $companyDraw->special8Prize = $resultsArray["specialArray"][7];
                    $companyDraw->special9Prize = $resultsArray["specialArray"][8];
                    $companyDraw->special10Prize = $resultsArray["specialArray"][9];
                    $companyDraw->consolation1Prize = $resultsArray["consoArray"][0];
                    $companyDraw->consolation2Prize = $resultsArray["consoArray"][1];
                    $companyDraw->consolation3Prize = $resultsArray["consoArray"][2];
                    $companyDraw->consolation4Prize = $resultsArray["consoArray"][3];
                    $companyDraw->consolation5Prize = $resultsArray["consoArray"][4];
                    $companyDraw->consolation6Prize = $resultsArray["consoArray"][5];
                    $companyDraw->consolation7Prize = $resultsArray["consoArray"][6];
                    $companyDraw->consolation8Prize = $resultsArray["consoArray"][7];
                    $companyDraw->consolation9Prize = $resultsArray["consoArray"][8];
                    $companyDraw->consolation10Prize = $resultsArray["consoArray"][9];

                    if (!$companyDraw->save()) {
                        Yii::error($companyDraw->errors);
                        throw new ServerErrorHttpException('Error processing results. code = ' . $code);
                    }
                }

                break;
            case 'T5':
                $company = Company::findOne(['code'=>Yii::$app->params['COMPANY']['CODE']['TOTO']]);
                $companyDraw = CompanyDraw::findOne(['companyId'=>$company->id,'drawDate'=>$drawDate]);
                if ($companyDraw) {
                    $companyDraw->checkResultsDate = new Expression('Now()');
                    $companyDraw->{'5d1stPrize'} = $resultsArray["1st"];
                    $companyDraw->{'5d2ndPrize'} = $resultsArray["2nd"];
                    $companyDraw->{'5d3rdPrize'} = $resultsArray["3rd"];
                    $companyDraw->{'5d4thPrize'} = $resultsArray["4th"];
                    $companyDraw->{'5d5thPrize'} = $resultsArray["5th"];
                    $companyDraw->{'5d6thPrize'} = $resultsArray["6th"];
                    if (!$companyDraw->save()) {
                        Yii::error($companyDraw->errors);
                        throw new ServerErrorHttpException('Error processing results. code = T5');
                    }
                }
                break;
            case 'T6':
                $company = Company::findOne(['code'=>Yii::$app->params['COMPANY']['CODE']['TOTO']]);
                $companyDraw = CompanyDraw::findOne(['companyId'=>$company->id,'drawDate'=>$drawDate]);
                if ($companyDraw) {
                    $companyDraw->checkResultsDate = new Expression('Now()');
                    $companyDraw->{'6d1stPrize'} = $resultsArray["1st"];
                    $companyDraw->{'6d2nd1Prize'} = $resultsArray["2nd1"];
                    $companyDraw->{'6d2nd2Prize'} = $resultsArray["2nd2"];
                    $companyDraw->{'6d3rd1Prize'} = $resultsArray["3rd1"];
                    $companyDraw->{'6d3rd2Prize'} = $resultsArray["3rd2"];
                    $companyDraw->{'6d4th1Prize'} = $resultsArray["4th1"];
                    $companyDraw->{'6d4th2Prize'} = $resultsArray["4th2"];
                    $companyDraw->{'6d5th1Prize'} = $resultsArray["5th1"];
                    $companyDraw->{'6d5th2Prize'} = $resultsArray["5th2"];
                    if (!$companyDraw->save()) {
                        Yii::error($companyDraw->errors);
                        throw new ServerErrorHttpException('Error processing results. code = T6');
                    }
                }
                break;
        }
    }

    private function processResults($drawDate) {
        //Proceed to scan the bets to see if they are won or lost
        $btStatusArray = array(Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED'],Yii::$app->params['BET']['DETAIL']['STATUS']['LIMITED']);
        $bets = Bet::find()
            ->innerJoinWith('betDetails')
            ->where(['bet.status'=>Yii::$app->params['BET']['STATUS']['NEW']])
            ->andWhere(['bet_detail.drawDate'=>$drawDate,'bet_detail.status'=>$btStatusArray])
            ->with(['betDetails.companyDraw'])
            ->all();

        foreach ($bets as $bet) {
            $grandTotalWin = 0;
            $bds = $bet->betDetails;
            if (count($bds) > 0) {
                //Found accepted or limited bets
                $package = Package::findOne($bet->packageId);
                foreach ($bds as $bd) {
                    $totalWin = 0;
                    $cd = $bd->companyDraw;
                    if ($cd->status == Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW']) {
                        Yii::error("Error processing results due to company draw not ready.");
                        throw new ServerErrorHttpException('Error processing results due to company draw not ready.');
                    }

                    //Bet Details Attributes
                    $bdNumber = $bd->number;
                    $bdBig = $bd->big;
                    $bdSmall = $bd->small;
                    $bd4a = $bd->{'4a'};
                    $bd4b = $bd->{'4b'};
                    $bd4c = $bd->{'4c'};
                    $bd4d = $bd->{'4d'};
                    $bd4e = $bd->{'4e'};
                    $bd4f = $bd->{'4f'};
                    $bd3abc = $bd->{'3abc'};
                    $bd3a = $bd->{'3a'};
                    $bd3b = $bd->{'3b'};
                    $bd3c = $bd->{'3c'};
                    $bd3d = $bd->{'3d'};
                    $bd3e = $bd->{'3e'};
                    $bd5d = $bd->{'5d'};
                    $bd6d = $bd->{'6d'};

                    if (!empty($bdBig)) {
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $winAmount = $bdBig * $package->{'4dBigPrize1'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_1'],
                                $package->{'4dBigPrize1'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $winAmount = $bdBig * $package->{'4dBigPrize2'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_2'],
                                $package->{'4dBigPrize2'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $winAmount = $bdBig * $package->{'4dBigPrize3'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_3'],
                                $package->{'4dBigPrize3'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'special1Prize'} || $bdNumber == $cd->{'special2Prize'} || $bdNumber == $cd->{'special3Prize'}
                            || $bdNumber == $cd->{'special4Prize'} || $bdNumber == $cd->{'special5Prize'} || $bdNumber == $cd->{'special6Prize'}
                            || $bdNumber == $cd->{'special7Prize'} || $bdNumber == $cd->{'special8Prize'} || $bdNumber == $cd->{'special9Prize'}
                            || $bdNumber == $cd->{'special10Prize'}) {
                            $winAmount = $bdBig * $package->{'4dBigStarters'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_STARTERS'],
                                $package->{'4dBigStarters'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'consolation1Prize'} || $bdNumber == $cd->{'consolation2Prize'} || $bdNumber == $cd->{'consolation3Prize'}
                            || $bdNumber == $cd->{'consolation4Prize'} || $bdNumber == $cd->{'consolation5Prize'} || $bdNumber == $cd->{'consolation6Prize'}
                            || $bdNumber == $cd->{'consolation7Prize'} || $bdNumber == $cd->{'consolation8Prize'} || $bdNumber == $cd->{'consolation9Prize'}
                            || $bdNumber == $cd->{'consolation10Prize'}) {
                            $winAmount = $bdBig * $package->{'4dBigConsolation'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_CONSOLATION'],
                                $package->{'4dBigConsolation'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bdSmall)) {
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $winAmount = $bdSmall * $package->{'4dSmallPrize1'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_1'],
                                $package->{'4dSmallPrize1'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $winAmount = $bdSmall * $package->{'4dSmallPrize2'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_2'],
                                $package->{'4dSmallPrize2'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $winAmount = $bdSmall * $package->{'4dSmallPrize3'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_3'],
                                $package->{'4dSmallPrize3'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4a)) { //1st prize only
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $winAmount = $bd4a * $package->{'4d4aPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4a,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4A_PRIZE'],
                                $package->{'4d4aPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4b)) { //2nd prize only
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $winAmount = $bd4b * $package->{'4d4bPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4b,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4B_PRIZE'],
                                $package->{'4d4bPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4c)) { //3rd prize only
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $winAmount = $bd4c * $package->{'4d4cPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4c,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4C_PRIZE'],
                                $package->{'4d4cPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4d)) { //Special only
                        if ($bdNumber == $cd->{'special1Prize'} || $bdNumber == $cd->{'special2Prize'} || $bdNumber == $cd->{'special3Prize'}
                        || $bdNumber == $cd->{'special4Prize'} || $bdNumber == $cd->{'special5Prize'} || $bdNumber == $cd->{'special6Prize'}
                        || $bdNumber == $cd->{'special7Prize'} || $bdNumber == $cd->{'special8Prize'} || $bdNumber == $cd->{'special9Prize'}
                        || $bdNumber == $cd->{'special10Prize'}) {
                            $winAmount = $bd4d * $package->{'4d4dPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4D_PRIZE'],
                                $package->{'4d4dPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4e)) { //Consolation only
                        if ($bdNumber == $cd->{'consolation1Prize'} || $bdNumber == $cd->{'consolation2Prize'} || $bdNumber == $cd->{'consolation3Prize'}
                            || $bdNumber == $cd->{'consolation4Prize'} || $bdNumber == $cd->{'consolation5Prize'} || $bdNumber == $cd->{'consolation6Prize'}
                            || $bdNumber == $cd->{'consolation7Prize'} || $bdNumber == $cd->{'consolation8Prize'} || $bdNumber == $cd->{'consolation9Prize'}
                            || $bdNumber == $cd->{'consolation10Prize'}) {
                            $winAmount = $bd4e * $package->{'4d4ePrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4e,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4E_PRIZE'],
                                $package->{'4d4ePrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd4f)) { //1st, 2nd and 3rd prize
                        if ($bdNumber == $cd->{'1stPrize'} || $bdNumber == $cd->{'2ndPrize'} || $bdNumber == $cd->{'3rdPrize'}) {
                            $winAmount = $bd4f * $package->{'4d4fPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd4f,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4F_PRIZE'],
                                $package->{'4d4fPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3abc)) { //1st, 2nd and 3rd prize (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'1stPrize'},-3)) {
                            $winAmount = $bd3abc * $package->{'3dAbcPrize1'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1'],
                                $package->{'3dAbcPrize1'},$winAmount,$bd->id);
                        }
                        if (substr($bdNumber,-3) == substr($cd->{'2ndPrize'},-3)) {
                            $winAmount = $bd3abc * $package->{'3dAbcPrize2'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2'],
                                $package->{'3dAbcPrize2'},$winAmount,$bd->id);
                        }
                        if (substr($bdNumber,-3) == substr($cd->{'3rdPrize'},-3)) {
                            $winAmount = $bd3abc * $package->{'3dAbcPrize3'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3'],
                                $package->{'3dAbcPrize3'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3a)) { //1st prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'1stPrize'},-3)) {
                            $winAmount = $bd3a * $package->{'3d3aPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3a,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE'],
                                $package->{'3d3aPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3b)) { //2nd prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'2ndPrize'},-3)) {
                            $winAmount = $bd3b * $package->{'3d3bPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3b,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE'],
                                $package->{'3d3bPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3c)) { //3rd prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'3rdPrize'},-3)) {
                            $winAmount = $bd3c * $package->{'3d3cPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3c,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE'],
                                $package->{'3d3cPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3d)) { //Special only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'special1Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special2Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special3Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special4Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special5Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special6Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special7Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special8Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special9Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special10Prize'},-3)) {
                            $winAmount = $bd3d * $package->{'3d3dPrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE'],
                                $package->{'3d3dPrize'},$winAmount,$bd->id);
                        }
                    }
                    if (!empty($bd3e)) { //Consolation only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'consolation1Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation2Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation3Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation4Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation5Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation6Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation7Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation8Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation9Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation10Prize'},-3)) {
                            $winAmount = $bd3e * $package->{'3d3ePrize'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd3e,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE'],
                                $package->{'3d3ePrize'},$winAmount,$bd->id);
                        }
                    }

                    if (!empty($bd5d)) {
                        //Putting if-else if in here because if the number matches first prize, it's no longer considered to win the rest (4th,5th and 6th)
                        if ($bdNumber == $cd->{'5d1stPrize'}) {
                            $winAmount = $bd5d * $package->{'5dPrize1'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_1'],
                                $package->{'5dPrize1'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,-4) == $cd->{'5d4thPrize'}) { //Last 4 digits
                            $winAmount = $bd5d * $package->{'5dPrize4'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_4'],
                                $package->{'5dPrize4'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,-3) == $cd->{'5d5thPrize'}) { //Last 3 digits
                            $winAmount = $bd5d * $package->{'5dPrize5'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_5'],
                                $package->{'5dPrize5'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,-2) == $cd->{'5d6thPrize'}) { //Last 2 digits
                            $winAmount = $bd5d * $package->{'5dPrize6'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_6'],
                                $package->{'5dPrize6'},$winAmount,$bd->id);
                        }

                        if ($bdNumber == $cd->{'5d2ndPrize'}) {
                            $winAmount = $bd5d * $package->{'5dPrize2'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_2'],
                                $package->{'5dPrize2'},$winAmount,$bd->id);
                        }
                        if ($bdNumber == $cd->{'5d3rdPrize'}) {
                            $winAmount = $bd5d * $package->{'5dPrize3'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_3'],
                                $package->{'5dPrize3'},$winAmount,$bd->id);
                        }

                    }

                    if (!empty($bd6d)) {
                        //Putting if-else if in here because if the number matches first prize, it's no longer considered to win the rest
                        if ($bdNumber == $cd->{'6d1stPrize'}) {
                            $winAmount = $bd6d * $package->{'6dPrize1'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_1'],
                                $package->{'6dPrize1'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,0,5) == $cd->{'6d2nd1Prize'} || substr($bdNumber,-5) == $cd->{'6d2nd2Prize'}) {
                            $winAmount = $bd6d * $package->{'6dPrize2'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_2'],
                                $package->{'6dPrize2'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,0,4) == $cd->{'6d3rd1Prize'} || substr($bdNumber, -4) == $cd->{'6d3rd2Prize'}) {
                            $winAmount = $bd6d * $package->{'6dPrize3'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_3'],
                                $package->{'6dPrize3'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,0,3) == $cd->{'6d4th1Prize'} || substr($bdNumber,-3) == $cd->{'6d4th2Prize'}) {
                            $winAmount = $bd6d * $package->{'6dPrize4'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_4'],
                                $package->{'6dPrize4'},$winAmount,$bd->id);
                        } else if (substr($bdNumber,0,2) == $cd->{'6d5th1Prize'} || substr($bdNumber,-2) == $cd->{'6d5th2Prize'}) {
                            $winAmount = $bd6d * $package->{'6dPrize5'};
                            $totalWin += $winAmount;
                            self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_5'],
                                $package->{'6dPrize5'},$winAmount,$bd->id);
                        }
                    }

                    if ($totalWin > 0) {
                        $bd->won = 1;
                    } else {
                        $bd->won = 0;
                    }
                    $bd->totalWin = $totalWin;
                    if (!$bd->save()) {
                        Yii::error($bd->errors);
                        throw new ServerErrorHttpException('Error saving betDetail. Id = '.$bd->id);
                    }
                    $grandTotalWin += $totalWin;
                } //End foreach ($bds as $bd) {
            }
            $bet->totalWin += $grandTotalWin;
            //Make sure all the betDetails under the bet are processed
            $oustandingBdCount = BetDetail::find()
                ->where(['betId'=>$bet->id,'won'=>null])
                ->count();
            if ($oustandingBdCount <= 0) {
                $bet->status = Yii::$app->params['BET']['STATUS']['PROCESSED'];
            }
            if (!$bet->save()) {
                Yii::error($bet->errors);
                throw new ServerErrorHttpException('Error saving bet. Id = '.$bet->id);
            }

            //Update user balance and outstanding bet
            $ud = UserDetail::findOne(['userId'=>$bet->createdBy]);
            $ud->outstandingBet -= $bet->totalSales;
            $ud->balance -= $bet->totalSales;
            $ud->balance += $bet->totalCommission; //After the bets are processed, give the commission back to user as credit available to bet
            if ($grandTotalWin > 0) {
                $ud->balance += $grandTotalWin;
            }
            if (!$ud->save()) {
                Yii::error($ud->errors);
                throw new ServerErrorHttpException('Error saving userDetail. userId = '.$bet->createdBy);
            }
        }
    }

    private function insertBetDetailWin($betAmount,$winPrizeType,$winPrizeAmount,$totalWin,$betDetailId) {
        $bdw = new BetDetailWin();

        $bdw->betAmount = $betAmount;
        $bdw->winPrizeType = $winPrizeType;
        $bdw->winPrizeAmount = $winPrizeAmount;
        $bdw->totalWin = $totalWin;
        $bdw->betDetailId = $betDetailId;
        if (!$bdw->save()) {
            Yii::error("Failed to save BetDetailWin. winPrizeType = $winPrizeType, totalWin = $totalWin, betDetailId = $betDetailId");
            throw new ServerErrorHttpException('Failed to save BetDetailWin.');
        }
    }
}
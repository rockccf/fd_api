<?php
namespace app\commands;

use app\components\ccf\CommonClass;
use app\models\Bet;
use app\models\BetDetail;
use app\models\BetDetailWin;
use app\models\Company;
use app\models\CompanyDraw;
use app\models\Package;
use app\models\User;
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
        if (empty($drawDate)) {
            $drawDate = new \DateTime();
        } else {
            $drawDate = new \DateTime($drawDate);
        }
        $drawDate->setTime(0,0);
        $day = $drawDate->format('N');
        $specialDraw = false;
        if ($day == 2) {
            $specialDraw = true;
        }
        $today = new \DateTime();
        Yii::info("commands\BetController starts, today = ".$today->format('Y-m-d').", drawDate = ".$drawDate->format('Y-m-d'));

        $companyDraws = CompanyDraw::findAll(['status'=>Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW'],'drawDate'=>$drawDate->format('Y-m-d')]);
        $pendingCompanyDrawsCount = count($companyDraws);
        if ($pendingCompanyDrawsCount > 0) {
            //Proceed to get the results
            $magnumArray["code"] = Yii::$app->params['COMPANY']['CODE']['MAGNUM'];
            $pmpArray["code"] = Yii::$app->params['COMPANY']['CODE']['PMP'];
            $totoArray["code"] = Yii::$app->params['COMPANY']['CODE']['TOTO'];
            $singaporeArray["code"] = Yii::$app->params['COMPANY']['CODE']['SINGAPORE'];
            $sabahArray["code"] = Yii::$app->params['COMPANY']['CODE']['SABAH'];
            $sandakanArray["code"] = Yii::$app->params['COMPANY']['CODE']['SANDAKAN'];
            $sarawakArray["code"] = Yii::$app->params['COMPANY']['CODE']['SARAWAK'];
            $toto5dArray["code"] = "T5";
            $toto6dArray["code"] = "T6";

            $i = 1;
            while ($i <= 3) {
                self::getResultsFromCheck4D($specialDraw,$drawDate,$magnumArray,$pmpArray,$totoArray,$singaporeArray,$sabahArray,$sandakanArray,$sarawakArray,$toto5dArray,$toto6dArray);

                if ($specialDraw) { //Do not check singapore results as singapore does not have special draws
                    if (!self::checkCompanyResults($magnumArray) || !self::checkCompanyResults($pmpArray) || !self::checkCompanyResults($totoArray)
                        || !self::checkCompanyResults($sarawakArray) || !self::checkCompanyResults($sandakanArray)
                        || !self::checkCompanyResults($sabahArray) || !self::checkCompanyResults($toto5dArray) || !self::checkCompanyResults($toto6dArray)) {

                        self::getResultsFromWin4D($specialDraw,$drawDate,$magnumArray,$pmpArray,$totoArray,$singaporeArray,$sabahArray,$sandakanArray,$sarawakArray,$toto5dArray,$toto6dArray);

                        if (!self::checkCompanyResults($magnumArray) || !self::checkCompanyResults($pmpArray) || !self::checkCompanyResults($totoArray)
                            || !self::checkCompanyResults($sarawakArray) || !self::checkCompanyResults($sandakanArray)
                            || !self::checkCompanyResults($sabahArray) || !self::checkCompanyResults($toto5dArray) || !self::checkCompanyResults($toto6dArray)) {
                            if ($i == 3) {
                                Yii::error("checkCompanyResults false.");
                                return ExitCode::SOFTWARE;
                            }
                        } else {
                            break;
                        }

                        $i++;
                    } else {
                        break;
                    }
                } else {
                    if (!self::checkCompanyResults($magnumArray) || !self::checkCompanyResults($pmpArray) || !self::checkCompanyResults($totoArray)
                        || !self::checkCompanyResults($singaporeArray) || !self::checkCompanyResults($sarawakArray) || !self::checkCompanyResults($sandakanArray)
                        || !self::checkCompanyResults($sabahArray) || !self::checkCompanyResults($toto5dArray) || !self::checkCompanyResults($toto6dArray)) {

                        self::getResultsFromWin4D($specialDraw,$drawDate,$magnumArray,$pmpArray,$totoArray,$singaporeArray,$sabahArray,$sandakanArray,$sarawakArray,$toto5dArray,$toto6dArray);

                        if (!self::checkCompanyResults($magnumArray) || !self::checkCompanyResults($pmpArray) || !self::checkCompanyResults($totoArray)
                            || !self::checkCompanyResults($singaporeArray) || !self::checkCompanyResults($sarawakArray) || !self::checkCompanyResults($sandakanArray)
                            || !self::checkCompanyResults($sabahArray) || !self::checkCompanyResults($toto5dArray) || !self::checkCompanyResults($toto6dArray)) {
                            if ($i == 3) {
                                Yii::error("checkCompanyResults false.");
                                return ExitCode::SOFTWARE;
                            }
                        } else {
                            break;
                        }

                        $i++;
                    } else {
                        break;
                    }
                }
            }

            /*
            print_r($magnumArray);
            print_r($pmpArray);
            print_r($totoArray);
            print_r($singaporeArray);
            print_r($sarawakArray);
            print_r($sandakanArray);
            print_r($sabahArray);
            print_r($toto5dArray);
            print_r($toto6dArray);
            */

            //Insert Results
            $dbTrans = CompanyDraw::getDb()->beginTransaction();
            try {
                self::insertResults($magnumArray,$drawDate);
                self::insertResults($pmpArray,$drawDate);
                self::insertResults($totoArray,$drawDate);
                if (!$specialDraw) {
                    self::insertResults($singaporeArray,$drawDate);
                }
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

        $drawDate = $drawDate->format('Y-m-d');
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

    private function getResultsFromCheck4D($specialDraw,$drawDate,&$magnumArray,&$pmpArray,&$totoArray,&$singaporeArray,&$sabahArray,&$sandakanArray,&$sarawakArray,&$toto5dArray,&$toto6dArray) {
        $client = new Client();

        $pmResultsReady = $emResultsReady = $singResultsReady = false;

        /* Magnum, PMP, Toto, Toto 5D & 6D */
        $crawler = $client->request('GET', 'https://www.check4d.com/');
        /*
         * Order of the results table
         * 1. Magnum 4D
         * 2. PMP 4D
         * 3. Toto 4D
         * 4. Toto 5D & 6D
         * 5. PMP 3+3D (not relevant)
         * 6. Magnum Life (not relevant)
         * 7. Magnum 4D Jackpot Gold (not relevant)
        */
        $dates = $crawler->filter('td.resultdrawdate'); //Draw dates for all the companies
        $validDatesCount = 0;
        $i = 0;
        foreach ($dates as $dateObj) {
            $i++;
            $value = trim($dateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,5,10);
            if (empty($value)) {
                Yii::error("dateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value)) {
                Yii::error("dateObj, invalid date. i = $i, value = $value");
                continue;
            }

            if ($i >= 9) { //Ignore the non-relevant results
                continue;
            }

            $date = \DateTime::createFromFormat('d-m-Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("dateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }

            $validDatesCount++;
        }

        //Make sure there are 4 dates found
        if ($validDatesCount != 4) {
            Yii::error("dates, $validDatesCount dates found.");
            return ExitCode::SOFTWARE;
        }

        $topResults = $crawler->filter('td.resulttop'); //1st, 2nd & 3rd Prizes
        /*
         * 4 sets will be retrieved
         * 1. Magnum 4D
         * 2. PMP 4D
         * 3. Toto 4D
         * 4. PMP 3+3D (not relevant)
         */
        $i = 0;
        foreach ($topResults as $topNumber) {
            $value = trim($topNumber->nodeValue);
            $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value)) {
                Yii::error("topNumber, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }

            if ($i >= 9) {
                break; //Ignore the non-relevant results
            }
            //echo "$i = ".$value."\n";

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
            }
            $i++;
        }

        $bottomResults = $crawler->filter('td.resultbottom'); //Special, Consolation & Other Prizes
        /*
         * 5 sets will be retrieved
         * 1. Magnum 4D
         * 2. PMP 4D
         * 3. Toto 4D
         * 4. Toto 5D & 6D
         * 5. PMP 3+3D (not relevant)
         */
        $i = 0;
        foreach ($bottomResults as $number) {
            $value = trim($number->nodeValue);
            $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value) || $value == "****" || $value == "----" || $value == "-") {
                $i++;
                continue;
            }

            if ($i >= 82) {
                break; //Ignore the non-relevant results
            }
            //echo "$i = ".$value."\n";

            switch (true) {
                case ($i <= 12):
                    $magnumArray["specialArray"][] = $value;
                    break;
                case ($i <= 22):
                    $magnumArray["consoArray"][] = $value;
                    break;
                case ($i <= 33):
                    $pmpArray["specialArray"][] = $value;
                    break;
                case ($i <= 43):
                    $pmpArray["consoArray"][] = $value;
                    break;
                case ($i <= 56):
                    $totoArray["specialArray"][] = $value;
                    break;
                case ($i <= 66):
                    $totoArray["consoArray"][] = $value;
                    break;
                case ($i == 67):
                    $toto5dArray["1st"] = $value;
                    break;
                case ($i == 68):
                    $toto5dArray["4th"] = $value;
                    break;
                case ($i == 69):
                    $toto5dArray["2nd"] = $value;
                    break;
                case ($i == 70):
                    $toto5dArray["5th"] = $value;
                    break;
                case ($i == 71):
                    $toto5dArray["3rd"] = $value;
                    break;
                case ($i == 72):
                    $toto5dArray["6th"] = $value;
                    break;
                case ($i == 73):
                    $toto6dArray["1st"] = $value;
                    break;
                case ($i == 74):
                    $toto6dArray["2nd1"] = $value;
                    break;
                case ($i == 75):
                    $toto6dArray["2nd2"] = $value;
                    break;
                case ($i == 76):
                    $toto6dArray["3rd1"] = $value;
                    break;
                case ($i == 77):
                    $toto6dArray["3rd2"] = $value;
                    break;
                case ($i == 78):
                    $toto6dArray["4th1"] = $value;
                    break;
                case ($i == 79):
                    $toto6dArray["4th2"] = $value;
                    break;
                case ($i == 80):
                    $toto6dArray["5th1"] = $value;
                    break;
                case ($i == 81):
                    $toto6dArray["5th2"] = $value;
                    break;
            }
            $i++;
        }

        /*print_r($magnumArray);
        print_r($pmpArray);
        print_r($totoArray);
        print_r($toto5dArray);
        print_r($toto6dArray);*/

        /* Sandakan, Sarawak, Sabah */
        $crawler = $client->request('GET', 'https://www.check4d.com/sabah-sarawak-4d-results/');
        /*
         * Order of the results table
         * 1. Sandakan
         * 2. Sarawak
         * 3. Sabah
        */
        $dates = $crawler->filter('td.resultdrawdate'); //Draw dates for all the companies
        $validDatesCount = 0;
        $i = 0;
        foreach ($dates as $dateObj) {
            $i++;
            $value = trim($dateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,5,10);
            if (empty($value)) {
                Yii::error("dateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value)) {
                Yii::error("dateObj, invalid date. i = $i, value = $value");
                continue;
            }

            $date = \DateTime::createFromFormat('d-m-Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("dateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }

            $validDatesCount++;
        }

        //Make sure there are 3 dates found
        if ($validDatesCount != 3) {
            Yii::error("dates, $validDatesCount dates found.");
            return ExitCode::SOFTWARE;
        }

        $topResults = $crawler->filter('td.resulttop'); //1st, 2nd & 3rd Prizes
        /*
         * 4 sets will be retrieved
         * 1. Sandakan
         * 2. Sarawak
         * 3. Sabah
         * 4. Sabah 3D (not relevant)
         */
        $i = 0;
        foreach ($topResults as $topNumber) {
            $value = trim($topNumber->nodeValue);
            $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value)) {
                Yii::error("topNumber, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }

            if ($i >= 9) {
                break; //Ignore the non-relevant results
            }
            //echo "$i = ".$value."\n";

            switch ($i) {
                case 0:
                    $sandakanArray["1st"] = $value;
                    break;
                case 1:
                    $sandakanArray["2nd"] = $value;
                    break;
                case 2:
                    $sandakanArray["3rd"] = $value;
                    break;
                case 3:
                    $sarawakArray["1st"] = $value;
                    break;
                case 4:
                    $sarawakArray["2nd"] = $value;
                    break;
                case 5:
                    $sarawakArray["3rd"] = $value;
                    break;
                case 6:
                    $sabahArray["1st"] = $value;
                    break;
                case 7:
                    $sabahArray["2nd"] = $value;
                    break;
                case 8:
                    $sabahArray["3rd"] = $value;
                    break;
            }
            $i++;
        }

        $bottomResults = $crawler->filter('td.resultbottom'); //Special, Consolation & Other Prizes
        /*
         * 3 sets will be retrieved
         * 1. Sandakan
         * 2. Sarawak
         * 3. Sabah
         */
        $i = 0;
        foreach ($bottomResults as $number) {
            $value = trim($number->nodeValue);
            $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            if (empty($value) || $value == "****" || $value == "----" || $value == "-") {
                $i++;
                continue;
            }

            switch (true) {
                case ($i <= 12):
                    $sandakanArray["specialArray"][] = $value;
                    break;
                case ($i <= 22):
                    $sandakanArray["consoArray"][] = $value;
                    break;
                case ($i <= 33):
                    $sarawakArray["specialArray"][] = $value;
                    break;
                case ($i <= 43):
                    $sarawakArray["consoArray"][] = $value;
                    break;
                case ($i <= 56):
                    $sabahArray["specialArray"][] = $value;
                    break;
                case ($i <= 66):
                    $sabahArray["consoArray"][] = $value;
                    break;
            }
            $i++;
        }

        /* Singapore Starts */
        if (!$specialDraw) {
            $crawler = $client->request('GET', 'https://www.check4d.com/singapore-4d-results/');
            /*
             * Order of the results table
             * 1. Singapore 4D
             * 2. Singapore Toto (not relevant)
            */
            $dates = $crawler->filter('td.resultdrawdate'); //Draw dates for all the companies
            $validDatesCount = 0;
            $i = 0;
            foreach ($dates as $dateObj) {
                $i++;
                $value = trim($dateObj->nodeValue);
                $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
                $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
                $value = substr($value, 5, 10);
                if (empty($value)) {
                    Yii::error("dateObj, value is empty. i = $i");
                    return ExitCode::SOFTWARE;
                }
                if (!CommonClass::validateDate($value)) {
                    Yii::error("dateObj, invalid date. i = $i, value = $value");
                    continue;
                }

                if ($i >= 3) { //Ignore the non-relevant results
                    continue;
                }

                $date = \DateTime::createFromFormat('d-m-Y', $value);
                $date->setTime(0, 0);
                if ($date != $drawDate) {
                    Yii::error("dateObj, date is not the same as drawDate. i = $i, value = $value");
                    return ExitCode::SOFTWARE;
                }

                $validDatesCount++;
            }

            //Make sure there is 1 date found
            if ($validDatesCount != 1) {
                Yii::error("dates, $validDatesCount dates found.");
                return ExitCode::SOFTWARE;
            }

            $topResults = $crawler->filter('td.resulttop'); //1st, 2nd & 3rd Prizes
            /*
             * 1 set will be retrieved
             * 1. Singapore
             */
            $i = 0;
            foreach ($topResults as $topNumber) {
                $value = trim($topNumber->nodeValue);
                $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
                $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
                if (empty($value)) {
                    Yii::error("topNumber, value is empty. i = $i");
                    return ExitCode::SOFTWARE;
                }

                switch ($i) {
                    case 0:
                        $singaporeArray["1st"] = $value;
                        break;
                    case 1:
                        $singaporeArray["2nd"] = $value;
                        break;
                    case 2:
                        $singaporeArray["3rd"] = $value;
                        break;
                }
                $i++;
            }

            $bottomResults = $crawler->filter('td.resultbottom'); //Special, Consolation & Other Prizes
            /*
             * 1 set will be retrieved
             * 1. Singapore
             */
            $i = 0;
            foreach ($bottomResults as $number) {
                $value = trim($number->nodeValue);
                $value = preg_replace('/\xc2\xa0/', '', $value); //Replace non breaking space
                $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
                if (empty($value) || $value == "****" || $value == "----" || $value == "-") {
                    $i++;
                    continue;
                }

                switch (true) {
                    case ($i <= 9):
                        $singaporeArray["specialArray"][] = $value;
                        break;
                    case ($i <= 19):
                        $singaporeArray["consoArray"][] = $value;
                        break;
                }
                $i++;
            }
        }
    }

    private function getResultsFromWin4D($specialDraw,$drawDate,&$magnumArray,&$pmpArray,&$totoArray,&$singaporeArray,&$sabahArray,&$sandakanArray,&$sarawakArray,&$toto5dArray,&$toto6dArray) {
        $client = new Client();
        $crawler = $client->request('GET', 'http://www.win4d.com.my/');
        $dates = $crawler->filter('b.f_16'); //Draw dates for all the companies except 5d/6d
        $validDatesCount = 0;
        $i = 0;
        foreach ($dates as $dateObj) {
            $i++;
            $value = trim($dateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,-10);
            if (empty($value)) {
                Yii::error("dateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value,'d/m/Y')) {
                Yii::error("dateObj, invalid date. i = $i, value = $value");
                continue;
            }

            if ($specialDraw && $i == 6) { //if it's singapore and it's special draw (tuesday), ignore singapore results
                continue;
            }

            $date = \DateTime::createFromFormat('d/m/Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("dateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }

            $validDatesCount++;
        }

        if ($specialDraw) { //Make sure there are 8 dates found (singapore is excluded on special draws)
            if ($validDatesCount != 8) {
                Yii::error("dates, $validDatesCount dates found.");
                return ExitCode::SOFTWARE;
            }
        } else { //Make sure there are 9 dates found
            if ($validDatesCount != 9) {
                Yii::error("dates, $validDatesCount dates found.");
                return ExitCode::SOFTWARE;
            }
        }


        $sixdDate = $crawler->filter('td.resultdrawdate'); //Draw date for toto 5d/6d
        $validDatesCount = 0;
        foreach ($sixdDate as $sixdDateObj) {
            $i++;
            $value = trim($sixdDateObj->nodeValue);
            $value = preg_replace('/\xc2\xa0/','',$value); //Replace non breaking space
            $value = preg_replace('/[\s\+]/', '', $value); //Replace plus sign
            $value = substr($value,-10);
            if (empty($value)) {
                Yii::error("sixdDateObj, value is empty. i = $i");
                return ExitCode::SOFTWARE;
            }
            if (!CommonClass::validateDate($value,'d/m/Y')) {
                Yii::error("sixdDateObj, invalid date. i = $i, value = $value");
                continue;
            }
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            $date->setTime(0,0);
            if ($date != $drawDate) {
                Yii::error("sixdDateObj, date is not the same as drawDate. i = $i, value = $value");
                return ExitCode::SOFTWARE;
            }
            $validDatesCount++;
        }
        //Make sure there's 1 date found
        if ($validDatesCount != 1) {
            Yii::error("sixdDate, $validDatesCount dates found.");
            return ExitCode::SOFTWARE;
        }

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
    }

    private function processResults($drawDate) {
        //Proceed to scan the bets to see if they are won or lost
        $btStatusArray = array(Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED'],Yii::$app->params['BET']['DETAIL']['STATUS']['LIMITED']);

        $drawDate = $drawDate->format('Y-m-d');
        $bets = Bet::find()
            ->innerJoinWith([
                'betDetails' => function ($query) use ($drawDate,$btStatusArray) {
                    $query->andWhere(['bet_detail.drawDate'=>$drawDate,'bet_detail.status'=>$btStatusArray,'bet_detail.won'=>null]);
                },
            ])
            ->where(['bet.status'=>Yii::$app->params['BET']['STATUS']['NEW']])
            ->with([
                'betDetails.companyDraw'
            ])
            ->all();

        foreach ($bets as $bet) {
            $user = User::findOne($bet->createdBy);
            $grandTotalWin = 0;
            $grandTotalSuperiorBonus = 0;
            $bds = $bet->betDetails;
            $totalCommission = 0;
            if (count($bds) > 0) {
                //Found accepted or limited bets
                $package = Package::findOne($bet->packageId);
                foreach ($bds as $bd) {
                    $totalWin = 0;
                    $totalSuperiorBonus = 0;
                    $cd = $bd->companyDraw;
                    if ($cd->status == Yii::$app->params['COMPANY']['DRAW']['STATUS']['NEW']) {
                        Yii::error("Error processing results due to company draw not ready.");
                        throw new ServerErrorHttpException('Error processing results due to company draw not ready.');
                    }

                    //Bet Details Attributes
                    $totalCommission += $bd->totalCommission;
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

                    if (!empty($bdBig) && $bdBig > 0) {
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $result = self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_1'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $result = self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_2'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $result = self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_PRIZE_3'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'special1Prize'} || $bdNumber == $cd->{'special2Prize'} || $bdNumber == $cd->{'special3Prize'}
                            || $bdNumber == $cd->{'special4Prize'} || $bdNumber == $cd->{'special5Prize'} || $bdNumber == $cd->{'special6Prize'}
                            || $bdNumber == $cd->{'special7Prize'} || $bdNumber == $cd->{'special8Prize'} || $bdNumber == $cd->{'special9Prize'}
                            || $bdNumber == $cd->{'special10Prize'}) {
                            $result = self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_STARTERS'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'consolation1Prize'} || $bdNumber == $cd->{'consolation2Prize'} || $bdNumber == $cd->{'consolation3Prize'}
                            || $bdNumber == $cd->{'consolation4Prize'} || $bdNumber == $cd->{'consolation5Prize'} || $bdNumber == $cd->{'consolation6Prize'}
                            || $bdNumber == $cd->{'consolation7Prize'} || $bdNumber == $cd->{'consolation8Prize'} || $bdNumber == $cd->{'consolation9Prize'}
                            || $bdNumber == $cd->{'consolation10Prize'}) {
                            $result = self::insertBetDetailWin($bdBig,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_BIG_CONSOLATION'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bdSmall) && $bdSmall > 0) {
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $result = self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_1'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $result = self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_2'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $result = self::insertBetDetailWin($bdSmall,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_SMALL_PRIZE_3'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4a) && $bd4a > 0) { //1st prize only
                        if ($bdNumber == $cd->{'1stPrize'}) {
                            $result = self::insertBetDetailWin($bd4a,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4A_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4b) && $bd4b > 0) { //2nd prize only
                        if ($bdNumber == $cd->{'2ndPrize'}) {
                            $result = self::insertBetDetailWin($bd4b,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4B_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4c) && $bd4c > 0) { //3rd prize only
                        if ($bdNumber == $cd->{'3rdPrize'}) {
                            $result = self::insertBetDetailWin($bd4c,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4C_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4d) && $bd4d > 0) { //Special only
                        if ($bdNumber == $cd->{'special1Prize'} || $bdNumber == $cd->{'special2Prize'} || $bdNumber == $cd->{'special3Prize'}
                        || $bdNumber == $cd->{'special4Prize'} || $bdNumber == $cd->{'special5Prize'} || $bdNumber == $cd->{'special6Prize'}
                        || $bdNumber == $cd->{'special7Prize'} || $bdNumber == $cd->{'special8Prize'} || $bdNumber == $cd->{'special9Prize'}
                        || $bdNumber == $cd->{'special10Prize'}) {
                            $result = self::insertBetDetailWin($bd4d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4D_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4e) && $bd4e > 0) { //Consolation only
                        if ($bdNumber == $cd->{'consolation1Prize'} || $bdNumber == $cd->{'consolation2Prize'} || $bdNumber == $cd->{'consolation3Prize'}
                            || $bdNumber == $cd->{'consolation4Prize'} || $bdNumber == $cd->{'consolation5Prize'} || $bdNumber == $cd->{'consolation6Prize'}
                            || $bdNumber == $cd->{'consolation7Prize'} || $bdNumber == $cd->{'consolation8Prize'} || $bdNumber == $cd->{'consolation9Prize'}
                            || $bdNumber == $cd->{'consolation10Prize'}) {
                            $result = self::insertBetDetailWin($bd4e,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4E_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd4f) && $bd4f > 0) { //1st, 2nd and 3rd prize
                        if ($bdNumber == $cd->{'1stPrize'} || $bdNumber == $cd->{'2ndPrize'} || $bdNumber == $cd->{'3rdPrize'}) {
                            $result = self::insertBetDetailWin($bd4f,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['4D_4F_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3abc) && $bd3abc > 0) { //1st, 2nd and 3rd prize (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'1stPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_1'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if (substr($bdNumber,-3) == substr($cd->{'2ndPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_2'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if (substr($bdNumber,-3) == substr($cd->{'3rdPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3abc,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_ABC_PRIZE_3'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3a) && $bd3a > 0) { //1st prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'1stPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3a,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3A_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3b) && $bd3b > 0) { //2nd prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'2ndPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3b,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3B_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3c) && $bd3c > 0) { //3rd prize only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'3rdPrize'},-3)) {
                            $result = self::insertBetDetailWin($bd3c,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3C_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3d) && $bd3d > 0) { //Special only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'special1Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special2Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special3Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special4Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special5Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special6Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special7Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special8Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'special9Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'special10Prize'},-3)) {
                            $result = self::insertBetDetailWin($bd3d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3D_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }
                    if (!empty($bd3e) && $bd3e > 0) { //Consolation only (last 3 digits)
                        if (substr($bdNumber,-3) == substr($cd->{'consolation1Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation2Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation3Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation4Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation5Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation6Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation7Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation8Prize'},-3)
                            || substr($bdNumber,-3) == substr($cd->{'consolation9Prize'},-3) || substr($bdNumber,-3) == substr($cd->{'consolation10Prize'},-3)) {
                            $result = self::insertBetDetailWin($bd3e,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['3D_3E_PRIZE'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }

                    if (!empty($bd5d) && $bd5d > 0) {
                        //Putting if-else if in here because if the number matches first prize, it's no longer considered to win the rest (4th,5th and 6th)
                        if ($bdNumber == $cd->{'5d1stPrize'}) {
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_1'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,-4) == $cd->{'5d4thPrize'}) { //Last 4 digits
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_4'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,-3) == $cd->{'5d5thPrize'}) { //Last 3 digits
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_5'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,-2) == $cd->{'5d6thPrize'}) { //Last 2 digits
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_6'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }

                        if ($bdNumber == $cd->{'5d2ndPrize'}) {
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_2'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                        if ($bdNumber == $cd->{'5d3rdPrize'}) {
                            $result = self::insertBetDetailWin($bd5d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['5D_PRIZE_3'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }

                    }

                    if (!empty($bd6d) && $bd6d > 0) {
                        //Putting if-else if in here because if the number matches first prize, it's no longer considered to win the rest
                        if ($bdNumber == $cd->{'6d1stPrize'}) {
                            $result = self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_1'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,0,5) == $cd->{'6d2nd1Prize'} || substr($bdNumber,-5) == $cd->{'6d2nd2Prize'}) {
                            $result = self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_2'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,0,4) == $cd->{'6d3rd1Prize'} || substr($bdNumber, -4) == $cd->{'6d3rd2Prize'}) {
                            $result = self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_3'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,0,3) == $cd->{'6d4th1Prize'} || substr($bdNumber,-3) == $cd->{'6d4th2Prize'}) {
                            $result = self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_4'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        } else if (substr($bdNumber,0,2) == $cd->{'6d5th1Prize'} || substr($bdNumber,-2) == $cd->{'6d5th2Prize'}) {
                            $result = self::insertBetDetailWin($bd6d,Yii::$app->params['BET']['DETAIL']['WIN_PRIZE_TYPE']['6D_PRIZE_5'],
                                $package,$user,$bd->id);
                            $totalWin += $result["winAmount"];
                            $totalSuperiorBonus += $result["superiorBonus"];
                        }
                    }

                    if ($totalWin > 0) {
                        $bd->won = 1;
                    } else {
                        $bd->won = 0;
                    }
                    $bd->totalWin = $totalWin;
                    $bd->totalSuperiorBonus = $totalSuperiorBonus;
                    if (!$bd->save()) {
                        Yii::error($bd->errors);
                        throw new ServerErrorHttpException('Error saving betDetail. Id = '.$bd->id);
                    }
                    $grandTotalWin += $totalWin;
                    $grandTotalSuperiorBonus += $totalSuperiorBonus;
                } //End foreach ($bds as $bd) {
            }

            $bns = $bet->betNumbers;
            $totalSales = 0;
            foreach ($bns as $bn) {
                $bds = $bn->betDetails;
                $companyCodesCount = count($bn->companyCodes);
                $drawDatesCount = 1; //drawDatesCount is set to 1 in this case because the results are always processed on a daily basis

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
                        $date = \DateTime::createFromFormat('Y-m-d', $drawDate);
                        $date->setTime(0,0);
                        $bdDrawDate = new \DateTime($bd->drawDate);
                        $bdDrawDate->setTime(0,0);
                        if ($bdDrawDate == $date) {
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
                    }
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

                $totalSales += $soldBig+$soldSmall+$sold4a+$sold4b+$sold4c+$sold4d+$sold4e+$sold4f;
                $totalSales += $sold3abc+$sold3a+$sold3b+$sold3c+$sold3d+$sold3e;
                $totalSales += $sold5d+$sold6d;
            }

            $bet->totalWin += $grandTotalWin;
            $bet->totalSuperiorBonus += $grandTotalSuperiorBonus;
            //Make sure all the betDetails under the bet are processed
            $outstandingBdCount = BetDetail::find()
                ->where(['betId'=>$bet->id,'won'=>null])
                ->count();
            if ($outstandingBdCount <= 0) {
                $bet->status = Yii::$app->params['BET']['STATUS']['PROCESSED'];
            }
            if (!$bet->save()) {
                Yii::error($bet->errors);
                throw new ServerErrorHttpException('Error saving bet. Id = '.$bet->id);
            }

            //Update user balance and outstanding bet
            $ud = UserDetail::findOne(['userId'=>$bet->createdBy]);
            $ud->outstandingBet -= $totalSales;
            $ud->balance -= $totalSales;
            $ud->balance += $totalCommission; //After the bets are processed, give the commission back to user as credit available to bet
            if ($grandTotalWin > 0) {
                $ud->balance += $grandTotalWin;
            }
            if (!$ud->save()) {
                Yii::error($ud->errors);
                throw new ServerErrorHttpException('Error saving userDetail. userId = '.$bet->createdBy);
            }
        }
    }

    private function insertBetDetailWin($betAmount,$winPrizeType,$package,$user,$betDetailId) {
        $superiorWinPrizeAmount = $superiorBonus = $bonusWinPrizeAmount = null;

        $isPlayer = false;
        if ($user->userType == Yii::$app->params['USER']['TYPE']['PLAYER']) {
            $isPlayer = true;
        }
        $prizeTypeName = CommonClass::getWinPrizeTypeObjectName($winPrizeType);

        if ($isPlayer) {
            $superiorWinPrizeAmount = $package->{$prizeTypeName};
            if (!empty($user->userDetail->{$prizeTypeName})) {
                $winPrizeAmount = $user->userDetail->{$prizeTypeName};
                //Check if there's any extra bonus for superior (agent)
                if ($superiorWinPrizeAmount > $winPrizeAmount) {
                    $bonusWinPrizeAmount = $superiorWinPrizeAmount-$winPrizeAmount;
                }
            } else {
                $winPrizeAmount = $package->{$prizeTypeName};
            }
        } else {
            $winPrizeAmount = $package->{$prizeTypeName};
        }

        if (!empty($bonusWinPrizeAmount)) {
            $superiorBonus = $betAmount * $bonusWinPrizeAmount;
        }
        $winAmount = $betAmount * $winPrizeAmount;

        $bdw = new BetDetailWin();

        $bdw->betAmount = $betAmount;
        $bdw->winPrizeType = $winPrizeType;
        $bdw->winPrizeAmount = $winPrizeAmount;
        $bdw->totalWin = $winAmount;
        $bdw->superiorWinPrizeAmount = $superiorWinPrizeAmount;
        $bdw->superiorBonus = $superiorBonus;
        $bdw->betDetailId = $betDetailId;
        if (!$bdw->save()) {
            Yii::error("Failed to save BetDetailWin. winPrizeType = $winPrizeType, totalWin = $winAmount, betDetailId = $betDetailId");
            throw new ServerErrorHttpException('Failed to save BetDetailWin.');
        }

        return array('winAmount'=>$winAmount,'superiorBonus'=>$superiorBonus);
    }
}
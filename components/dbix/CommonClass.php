<?php

namespace app\components\dbix;

use app\models\EmailRequest;
use app\models\ItemPriceHistory;
use app\models\RfqLog;
use app\models\Sequence;
use app\models\Tenant;
use app\models\TenderLog;
use app\models\Uom;
use app\models\UomConversion;
use app\models\UomFormula;
use app\models\User;
use app\models\Workflow;

use mikehaertl\wkhtmlto\Pdf;
use Yii;
use yii\base\BaseObject;
use yii\data\ActiveDataProvider;
use yii\data\Sort;
use yii\db\Expression;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\BadRequestHttpException;
use yii\db\Query;

class CommonClass extends BaseObject
{
    /*
     * Author : Chong Chee Fei
     * Created At : 2016-11-01
     * Description :
     * To prepare and return the data provider object which is mostly called by restful controller index action.
     */
    public static function prepareActiveQueryDataProvider($params,$model,$where,$join = null) {
        //$model is yii\db\ActiveQuery object
        /*
         * $where has to be 2 dimensional array is due to that you cannot define two types of condition at one time
         * because the array structures are different
         * For example, ['tenantId'=>1,'status'=>1] and ['like',"user.status",1]
         * $where[0] = ['tenantId'=>1,'status'=>1];
         * $where[0] = ['like',"user.status",1];
         * $where = [['tenantId'=>1,'status'=>1]];
         * $where = [['like','user.status',1]];
         */
        if (!empty($where) && count($where) > 0) { //If $where is passed by the action in controller
            for ($i=0;$i<count($where);$i++) {
                if ($i == 0) {
                    $model = $model->where($where[$i]);
                } else {
                    $model = $model->andWhere($where[$i]);
                }
            }

            //$params["where"] consists the main parameters to select the data
            if (!empty($params["where"])) {
                $whereArray = Json::decode($params["where"]);
                $model = $model->andWhere($whereArray);
            }
        } else {
            //$params["where"] consists the main parameters to select the data
            if (!empty($params["where"])) {
                $whereArray = Json::decode($params["where"]);
                $model = $model->where($whereArray);
            }
        }

        //Additional join conditions which are not passed by front end
        /*
         * $join[0]["model"] = "currentProcessWorkflow";
         * $join[0]["eagerLoading"] = true;
         * $join[0]["joinType"] = Yii::$app->params['GLOBAL']['JOIN_TYPE']['INNER'];
         * $join[1]["model"] = "currentProcessWorkflow.processWorkflowPaths";
         * $join[1]["eagerLoading"] = true;
         * $join[1]["joinType"] = Yii::$app->params['GLOBAL']['JOIN_TYPE']['INNER'];
         * $join[1]["andWhere"][0] = ['process_workflow_path.userId'=>Yii::$app->user->id];
         * $join[1]["andWhere"][1] = ['process_workflow_path.action'=>null];
         */
        if (!empty($join) && count($join) > 0) {
            for ($i=0;$i<count($join);$i++) {
                $model->joinWith($join[$i]["model"],$join[$i]["eagerLoading"],$join[$i]["joinType"]);
                if (!empty($join[$i]["andWhere"]) && count($join[$i]["andWhere"]) > 0) {
                    foreach ($join[$i]["andWhere"] as $andWhere) {
                        $model->andWhere($andWhere);
                    }
                }
            }
        }

        //$params["filter"] consists the parameters to filter and select the data
        if (!empty($params["filter"])) {
            $filter = Json::decode($params["filter"]);
            foreach ($filter as $key => $condition) {
                $column = $condition['attribute'];
                $operator = $condition['operator'];
                $value = $condition['value'];
                //For child object, the $condition['model'] is not the actual model class name in the backend Yii framework
                //It's actually the property name defined under the model for the relationship
                //For example, under User model, the correct one is authAssignments(property) instead of AuthAssignment(actual model class name)
                $filterModel = $condition['model'];
                $isChild = $condition['isChild'];
                $joinWhere = $condition['joinWhere'] ?? null;

                //andWhere will not ignore empty operands
                //andFilterWhere will ignore empty operands
                /*
                 * The value is considered "empty", if one of the following conditions is satisfied:
                 * it is null,
                 * an empty string (''),
                 * a string containing only whitespace characters,
                 * or an empty array.
                 */

                if ($isChild) { //If the value entered is to compare against the child model
                    //Join for child model classes with relationship defined under parent model class
                    $model = $model->innerJoinWith("$filterModel $filterModel");
                    if ($operator == "join") { //join and equal
                        $model = $model->andOnCondition("$filterModel.$column = $value");
                    } else if ($operator == "joinNot") { //join and not equal
                        $model = $model->andOnCondition("$filterModel.$column != $value");
                    } else if ($operator == "like") {
                        if (is_null($value)) { //Sometimes front-end will pass in null value condition to ensure that the column is really NULL
                            $model = $model->andWhere([$operator, "$filterModel.$column", $value]);
                        } else {
                            $model = $model->andFilterWhere([$operator, "$filterModel.$column", $value]);
                        }
                    } else if ($operator == "in") {
                        $value = Json::decode($value);
                        $model = $model->andFilterWhere([$operator, "$filterModel.$column", $value]);
                    } else if ($operator == "notEquals") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["not", ["$filterModel.$column" => $value]]);
                        } else {
                            $model = $model->andFilterWhere(["not", ["$filterModel.$column" => $value]]);
                        }
                    } else { //Equals
                        if (is_null($value)) {
                            $model = $model->andWhere(["$filterModel.$column" => $value]);
                        } else {
                            $model = $model->andFilterWhere(["$filterModel.$column" => $value]);
                        }
                    }
                } else { //Else the value is compared to the parent model
                    if ($operator == "exists") {
                        if (is_null($value)) {
                            $model = $model->andWhere(['exists', (new Query())->select('id')->from("$filterModel $filterModel")->where(["$column" => $value])->andWhere($joinWhere)]);
                        } else {
                            $model = $model->andFilterWhere(['exists', (new Query())->select('id')->from("$filterModel $filterModel")->where(["$column" => $value])->andWhere($joinWhere)]);
                        }
                    } else if ($operator == "like") {
                        if (is_null($value)) {
                            $model = $model->andWhere([$operator, "$filterModel.$column", $value]);
                        } else {
                            $model = $model->andFilterWhere([$operator, "$filterModel.$column", $value]);
                        }
                    } else if ($operator == "in") {
                        $value = Json::decode($value);
                        $model = $model->andFilterWhere([$operator, "$filterModel.$column", $value]);
                    } else if ($operator == "notEquals") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["not", ["$filterModel.$column" => $value]]);
                        } else {
                            $model = $model->andFilterWhere(["not", ["$filterModel.$column" => $value]]);
                        }
                    } else if ($operator == "gte") {
                        if (is_null($value)) {
                            $model = $model->andWhere([">=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere([">=", "$filterModel.$column",$value]);
                        }
                    } else { //Equals
                        if (is_null($value)) {
                            $model = $model->andWhere(["$filterModel.$column" => $value]);
                        } else {
                            $model = $model->andFilterWhere(["$filterModel.$column" => $value]);
                        }
                    }
                }
            }
        }
		
        //Initialize the pagination variables with default values
        $page = 1;
        $pageSize = Yii::$app->params['GLOBAL']['RECORDS_PER_PAGE']; //Default page size
        if (!empty($params["pagination"])) {
            $pagination  = Json::decode($params["pagination"]);
            $page = $pagination["page"]; //Page number
            $pageSize = $pagination["per-page"]; //Number of records per page
        }

        //Sorting section
        //Allow to sort by multiple columns
        $sortObj = null;
        if (!empty($params["sort"]) && count($params["sort"]) > 0) {
            $sortArray = Json::decode($params["sort"]);
            $attributes = array();
            $defaultOrder  = array();
            foreach ($sortArray as $key => $sort) {
                $sortColumn = $sort["attribute"];
                $sortModel = $sort["model"];
                $sortIsChild = $sort["isChild"];

                if ($sort["order"] == "SORT_DESC") {
                    $sortOrder = SORT_DESC;
                } else {
                    $sortOrder = SORT_ASC;
                }

                if ($sortIsChild) { //Sort the column of child model
                    $model = $model->innerJoinWith("$sortModel $sortModel");
                    //Declare the join relationship in case there's no filter passed in
                    //and there's no join relationship with the child model defined in above
                    $attributes["$sortModel.$sortColumn"] = [
                        'asc' => ["$sortModel.$sortColumn" => SORT_ASC],
                        'desc' => ["$sortModel.$sortColumn" => SORT_DESC],
                        'default' => $sortOrder
                    ];

                    $defaultOrder["$sortModel.$sortColumn"] = $sortOrder;
                } else {
                    $attributes[$sortColumn] = [
                        'asc' => ["$sortColumn" => SORT_ASC],
                        'desc' => ["$sortColumn" => SORT_DESC],
                        'default' => $sortOrder
                    ];

                    $defaultOrder["$sortColumn"] = $sortOrder;
                }

                //$defaultOrder = $attributes; //Set to order by 1 or multiple columns as defined in the $attributes
            }

            $sortObj = new Sort([
                'attributes' => $attributes,
                'defaultOrder' => $defaultOrder,
                'enableMultiSort' => true
            ]);
        }

        if( isset($params['groupby']) && !empty($params['groupby']) ){
            $model -> groupBy($params['groupby']);
        }

        if ($sortObj) {
            return new ActiveDataProvider([
                'query' => $model,
                'pagination' => [
                    'page' => $page-1, //Start from 0 index
                    'pageSize' => $pageSize
                ],
                'sort' => $sortObj
            ]);
        } else {
            return new ActiveDataProvider([
                'query' => $model,
                'pagination' => [
                    'page' => $page-1, //Start from 0 index
                    'pageSize' => $pageSize
                ]
            ]);
        }
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2016-12-20
    * Description :
    * Generate reference for Tender, Quotation, PR & PO
    * Example : PR-161220-0000001-ABCD
    * ABCD is a 4-letter short code set by DBIX for the tenant
    */
    public static function getReferenceTenant($name,$tenantId) {
        //$name : PR - PR ; PO - PO ; TE - Tender ; QU - Quotation
        $reference = null;

        //Get tenant short code
        $tenant = Tenant::findOne($tenantId);
        $shortCode = $tenant->shortCode;
        $shortCode = strtoupper($shortCode);

        //Get the latest sequence number
        $name = strtoupper($name);
        $sequence = Sequence::findOne(['name' => $name, 'tenantId' => $tenantId, 'type'=>Yii::$app->params['SEQUENCE']['TYPE']['TENANT']]);
        $sequenceNumber = str_pad($sequence->number,7,'0',STR_PAD_LEFT);

        $date = date("ymd");
        $reference = $name.'-'.$date.'-'.$sequenceNumber.'-'.$shortCode;

        //Proceed to update the increment the sequence number and update
        $sequence->number = $sequenceNumber+1;
        $sequence->save();

        return $reference;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2017-11-08
    * Description :
    * Generate reference for global running number (in the whole horecabid system)
    * Example : TX1234567, CN1234567
    */
    public static function getGlobalReference($name) {
        //$name : TX - TX ; CN - CN ;
        //TX - Transaction
        //CN - Contract Number
        $reference = null;

        //Get the latest sequence number
        $name = strtoupper($name);
        $sequence = Sequence::findOne(['name' => $name, 'type'=>Yii::$app->params['SEQUENCE']['TYPE']['GLOBAL']]);
        $sequenceNumber = str_pad($sequence->number,7,'0',STR_PAD_LEFT);

        $reference = $name.$sequenceNumber;

        //Proceed to update the increment the sequence number and update
        $sequence->number = $sequenceNumber+1;
        $sequence->save();

        return $reference;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2017-01-06
    * Description :
    * Generate PDF and download/view
    */
    public static function generatePDF($html,$fileName,$headerHtmlFile = null, $headerMarginTop = null, $orientation = 'Portrait') {
        /*
         * From author of the library :
         * For me wkhtmltopdf seems to create best results with smart shrinking turned off.
         * But then I had scaling issues which went away after I set all margins to zero and instead added the margins through CSS.
         * You can also use cm or in in CSS as this is more apropriate for print styles.
         */
        // Create a new Pdf object with some global PDF options
        $result = false;
        $now = date(DATE_RFC2822);
        $options = array(
            //'binary' => 'C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe',
            'no-outline',         // Make Chrome not complain
            //'margin-top'    => 0,
            //'margin-right'  => 0,
            'margin-bottom' => 15,
            //'margin-left'   => 0,
            'orientation' => $orientation,

            // Default page options
            //'disable-smart-shrinking',

            'footer-right' => 'Page [page] of [toPage]',
            'footer-font-name' => 'Helvetica',
            'footer-font-size' => '10',
            'footer-left' => 'Printed on '.$now,
            'footer-spacing' => 3,
        );

        if ($headerHtmlFile) {
            $options["margin-top"] = $headerMarginTop;
            $options["header-spacing"] = 3;
            $options["header-html"] = $headerHtmlFile;
        }

        /*if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') { //If it's windows server
            $options["commandOptions"] = array(
                "useExec" => true,
                "procOptions"=>array(
                    // This will bypass the cmd.exe which seems to be recommended on Windows
                    'bypass_shell' => true,
                    // Also worth a try if you get unexplainable errors
                    'suppress_errors' => true,
                )
            );
        }*/

        $pdf = new Pdf($options);
        $pdf->addPage($html);

        $tempFolder = Yii::getAlias('@globalTemp');
        if (!file_exists($tempFolder)) {
            mkdir($tempFolder, 0777, true);
        }
        $result = $pdf->saveAs("$tempFolder/$fileName");

        if (!$result) {
            Yii::error($pdf->getError());
        }

        return $result;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2017-07-25
    * Description :
    * To check if there's duplicated workflow
    */
    public static function checkDuplicateWorkflow($type, $levels, $hierarchy) {
        //$hierarchy - Json
        $result = false;

        $workflows = Workflow::findAll([
            'type' => $type,
            'levels' => $levels
        ]);

        if (!empty($workflows)) {
            //Workflows with same type and levels found
            //Proceed to check and compare the hierarchy
            foreach ($workflows as $workflow) {
                $existingHierarchy = Json::decode($workflow->hierarchy);
                for ($i=0;$i<$levels;$i++) { //Check level by level
                    $existingLevel = $existingHierarchy[$i]["level"];
                    $existingEnableThresholdAmount = $existingHierarchy[$i]["enableThresholdAmount"];
                    $existingMemberArray = $existingHierarchy[$i]["memberArray"];
                    $existingOperator = $existingHierarchy[$i]["operator"];

                    $level = $hierarchy[$i]["level"];
                    $enableThresholdAmount = $hierarchy[$i]["enableThresholdAmount"];
                    $memberArray = $hierarchy[$i]["memberArray"];
                    $operator = $hierarchy[$i]["operator"];

                    if ($existingEnableThresholdAmount != $enableThresholdAmount) {
                        return false;
                    }

                    if ($existingOperator != $operator) {
                        return false;
                    }

                    $existingMemberArrayCount = count($existingMemberArray);
                    $memberArrayCount = count($memberArray);

                    if ($memberArrayCount != $existingMemberArrayCount) {
                        return false;
                    }

                    //Check memberArray
                    $levelMemberSame = true; //See if all the members under the same level are the same
                    for ($m=0;$m<count($memberArray);$m++) {
                        $memberExists = false;
                        for ($e=0;$e<count($existingMemberArray);$e++) {
                            $arrayDiff = array_diff($memberArray[$m], $existingMemberArray[$e]);
                            if (empty($arrayDiff)) {
                                $memberExists = true;
                                break;
                            }
                        }

                        if (!$memberExists) {
                            return false;
                        }
                    }

                    if ($levelMemberSame) {
                        $result = true;
                    }
                }
            }
        }

        return $result;
    }

    /*
   * Author : Chong Chee Fei
   * Created At : 2017-09-03
   * Description :
   * To insert item price history
   */
    public static function insertItemPriceHistory($itemId, $priceDate, $unitPrice, $type, $processId, $supplierId) {
        $iph = new ItemPriceHistory();
        $iph->itemId = $itemId;
        $iph->type = $type;
        if (!empty($priceDate)) {
            $priceDate = new \DateTime($priceDate);
            $iph->priceDate = $priceDate->format('Y-m-d');
        } else {
            $iph->priceDate = new Expression('NOW()');
        }
        $iph->unitPrice = $unitPrice;
        $iph->unitTax = 0;
        if ($type == Yii::$app->params['ITEM']['PRICE_HISTORY']['TYPE']['PO']) {
            $iph->purchaseOrderId = $processId;
        } else if ($type == Yii::$app->params['ITEM']['PRICE_HISTORY']['TYPE']['TENDER_CATEGORY_ENTRY']) {
            $iph->tenderCategoryEntryId = $processId;
        } else if ($type == Yii::$app->params['ITEM']['PRICE_HISTORY']['TYPE']['RFQ_CATEGORY_ENTRY']) {
            $iph->rfqCategoryEntryId = $processId;
        }
        $iph->supplierId = $supplierId;

        if (!$iph->save()) {
            Yii::error($iph->errors);
            throw new ServerErrorHttpException("Failed to insert item price history.");
        } else {
            return true;
        }
    }

    /*
   * Author : Chong Chee Fei
   * Created At : 2017-10-26
   * Description :
   * To convert and calculate the price based on supplier's and tenant's uom if they are not the same
   */
    public static function convertEntryPrice($entryPrice,$fromUomId,$toUomId,$isUomFormula) {
        //$entryPrice - Entry price supplier has submitted
        //$fromUomId - Uom ID or Uom Formula ID
        //$toUomId - Uom ID or Uom Formula ID
        //$isUomFormula - true or false, determines if we should calculate based on uom formula or main uom
        $result = null;

        if ($isUomFormula) {
            $fromUom = UomFormula::findOne($fromUomId);
            $toUom = UomFormula::findOne($toUomId);
        } else {
            $fromUom = Uom::findOne($fromUomId);
            $toUom = Uom::findOne($toUomId);
        }

        if (!$fromUom || !$toUom) {
            Yii::error("Missing UOM(s). fromUomId = $fromUomId, toUomId = $toUomId");
            return false;
        }

        if ($isUomFormula) {
            if ($fromUom->uomId != $toUom->uomId) {
                Yii::error("Main UOM(s) do not match. fromUomId = $fromUomId, toUomId = $toUomId");
                return false;
            }
        } else {
            if ($fromUom->type != $toUom->type) {
                Yii::error("UOM types do not match. fromUomId = $fromUomId, toUomId = $toUomId");
                return false;
            }
        }

        if ($isUomFormula) {
            $fromUomFormulaArray = Json::decode($fromUom->formula);
            $toUomFormulaArray = Json::decode($toUom->formula);

            $fromUomLevels = count($fromUomFormulaArray);
            $toUomLevels = count($toUomFormulaArray);
            if ($fromUomLevels != $toUomLevels) {
                Yii::error("Different UOM levels detected. fromUomId = $fromUomId, toUomId = $toUomId");
                return false;
            }

            //Always take the last level of subuom and do the calculation
            $fromUomFormula = $fromUomFormulaArray[$fromUomLevels-1];
            $toUomFormula = $toUomFormulaArray[$toUomLevels-1];

            $fromSubuomId = $fromUomFormula["subuom"];
            $fromUnitVolume = $fromUomFormula["unitVolume"];
            $toSubuomId = $toUomFormula["subuom"];
            $toUnitVolume = $toUomFormula["unitVolume"];

            if ($fromSubuomId != $toSubuomId) { //Different UOM
                $uomConversion = UomConversion::findOne([
                    'fromUomId' => $fromSubuomId,
                    'toUomId' => $toSubuomId
                ]);

                if (!$uomConversion) {
                    Yii::error("Cannot find subuom conversion. fromSubuomId = $fromSubuomId, toSubuomId = $toSubuomId");
                    return false;
                }

                $factor = $uomConversion->factor;
                /*
                 * From
                 * 500G (Supplier Specs) - RM50
                 * to
                 * 1KG (Tenant Specs) - RM100 (After conversion)
                 */
                //Apply the conversion factor to fromUnitVolume (500G becomes 0.5KG)
                $convertedUnitVolume = $fromUnitVolume * $factor; //(500 x 0.001 = 0.5)

                //Now we have the supplier unit volume(500) converted from supplier specs(G) to tenant specs(KG)
                //Proceed to calculate the price
                $convertedPrice = $toUnitVolume / $convertedUnitVolume * $entryPrice;
                $result = round($convertedPrice, 3);
            } else { //Same UOM but with different unitVolume
                $convertedPrice = $toUnitVolume / $fromUnitVolume * $entryPrice;
                $result = round($convertedPrice, 3);
            }
        } else { //Based on main uom, so unitVolume for both is 1 hence unitVolume will not be part of the calculation
            $uomConversion = UomConversion::findOne([
                'fromUomId' => $fromUomId,
                'toUomId' => $toUomId
            ]);

            if (!$uomConversion) {
                Yii::error("Cannot find uom conversion. fromUomId = $fromUomId, toUomId = $toUomId");
                return false;
            }

            $factor = $uomConversion->factor;
            $result = round($entryPrice * $factor, 3);
        }

        return $result;
    }

    /*
   * Author : Chong Chee Fei
   * Created At : 2017-12-20
   * Description :
   * To insert rfq log
   */
    public static function insertRfqLog($action,$rfqId,array $params = null,$rfqCategoryId = null, $supplierId = null) {
        $rfqLog = new RfqLog();

        $rfqLog->action = $action;
        if (!empty($params)) {
            $rfqLog->params = Json::encode($params);
        }
        $rfqLog->rfqId = $rfqId;
        $rfqLog->rfqCategoryId = $rfqCategoryId;
        $rfqLog->supplierId = $supplierId;

        if (!$rfqLog->save()) {
            Yii::error($rfqLog->errors);
            throw new ServerErrorHttpException("Failed to insert rfq log.");
        } else {
            return true;
        }
    }

    /*
     * Author : Chong Chee Fei
     * Created At : 2017-12-27
     * Description :
     * To insert tender log
     */
    public static function insertTenderLog($action,$tenderId,array $params = null,$tenderCategoryId = null, $supplierId = null) {
        $tenderLog = new TenderLog();

        $tenderLog->action = $action;
        if (!empty($params)) {
            $tenderLog->params = Json::encode($params);
        }
        $tenderLog->tenderId = $tenderId;
        $tenderLog->tenderCategoryId = $tenderCategoryId;
        $tenderLog->supplierId = $supplierId;

        if (!$tenderLog->save()) {
            Yii::error($tenderLog->errors);
            throw new ServerErrorHttpException("Failed to insert tender log.");
        } else {
            return true;
        }
    }
    /*
     * Author : MJ
     * Created At : 2018-03-24
     * Description :
     * Create reset password request
     * 
     * CCF 20180322
     * once the password is reset, u need to delete the request from email_request table and u need to log the request
     * for example, user id 3 requested for password reset.
     * "user id 3 resets password successfully."
     * Yii::info("user id 3 reset password successfully.","emailRequest");
     * you can refer to this for the random token generation
     * https://stackoverflow.com/questions/18910814/best-practice-to-generate-random-token-for-forgot-password
     */    
    public static function requestResetPassword($userType, $email){
        $user = User::find() 
			-> where(['email' => $email])
			-> andWhere(['userType' => $userType])
			-> andWhere(['active' => 1])
			-> andWhere(['NOT', ['locked' => 1]])
			-> limit(1)
			-> one();
		
		if( !$user )
			throw new NotFoundHttpException('Email address could not be found.');

		## generate fresh unique token
		$length = 44; // end token will be twice of this length
		do{
			$token = bin2hex(random_bytes($length));		
			$emailRequest = EmailRequest::find()
				-> where(['code' => $token])
				-> andWhere(['type' => Yii::$app -> params['EMAIL']['TYPE']['RESET_PASSWORD']])
				-> limit(1)
				-> one();
		}while( $emailRequest );
		
		## update existing row or create new row
		$emailRequest = EmailRequest::find()
			-> where(['ownerId' => $user -> id])
			-> andWhere(['type' => Yii::$app -> params['EMAIL']['TYPE']['RESET_PASSWORD']])
			-> limit(1)
			-> one();

		if( !$emailRequest )
			$emailRequest = new EmailRequest;

		$emailRequest -> type = Yii::$app -> params['EMAIL']['TYPE']['RESET_PASSWORD'];
		$emailRequest -> code = $token;
		$emailRequest -> expiredBy = date("Y-m-d H:i:s", strtotime("+1 hour", strtotime(date('Y-m-d H:i:s'))));
		$emailRequest -> ownerId = $user -> id;
		
		if( !$emailRequest -> save() ){
			Yii::error($emailRequest -> errors);
			throw new BadRequestHttpException('Failed to generate reset password link.');
		}

        Yii::info("User id " . $user -> id . " requested for password reset.","emailRequest");
        
        ## supplier or tenant
        if( $userType == Yii::$app -> params['USER']['TYPE']['SUPPLIER'] ){
            $template = 'supplier_reset_password';
            $firstName = $user -> supplierUserProfile -> firstName;
            $lastName = $user -> supplierUserProfile -> lastName;
            $userTypePortalUrl = Yii::getAlias('@supplierPortalUrl');
        }else{
            $template = 'tenant_reset_password';
            $firstName = $user -> userProfile -> firstName;
            $lastName = $user -> userProfile -> lastName;
            $userTypePortalUrl = Yii::getAlias('@tenantPortalUrl');
        }

		## send email to user
		$sender = Yii::$app -> params['GLOBAL']['EMAIL_FROM'];
		$recipients = [ $email ];
		$subject = '[HorecaBid] Reset Password';
		
		$parameters = [
			'firstName' => $firstName,
			'lastName' => $lastName,
			'resetPasswordUrl' => $userTypePortalUrl . '/resetPassword/' . $user -> id . '/' . $token
		];
        return EmailClass::sendEmail($sender, $recipients, $subject, $template, $parameters);
    }   
    /*
     * Author : MJ
     * Created At : 2018-03-24
     * Description :
     * Verify token
     */    
    public static function verifyResetPasswordToken($userId, $token){
        $emailRequest = EmailRequest::find()
            -> where(['code' => $token])
            -> andWhere(['type' => Yii::$app -> params['EMAIL']['TYPE']['RESET_PASSWORD']])
            -> andWhere(['ownerId' => $userId])
            -> andWhere(['>', 'expiredBy', new Expression("NOW()")])
            -> limit(1)
            -> one();
    
        if( !$emailRequest )
          throw new BadRequestHttpException('Invalid link or expired link.');	

        return $token;
    }
    /*
     * Author : MJ
     * Created At : 2018-03-24
     * Description :
     * Set new password. This function is created for password reset
     */    
    public static function resetPassword($userId, $token, $password){
        $emailRequest = EmailRequest::find()
            -> where(['code' => $token])
            -> andWhere(['type' => Yii::$app -> params['EMAIL']['TYPE']['RESET_PASSWORD']])
            -> andWhere(['ownerId' => $userId])
            -> andWhere(['>', 'expiredBy', new Expression("NOW()")])
            -> limit(1)
            -> one();
    
        if( !$emailRequest )
          throw new BadRequestHttpException('Invalid link or expired link.');	
        
        $user = User::findOne($emailRequest -> ownerId);
        if( !$user )
            throw new BadRequestHttpException('Unable to retrieve owner information.');	
        
        $user -> scenario = User::SCENARIO_RESETPASSWORD;
        $user -> setPasswordHash($password);
        $user -> passwordExpiryDate = new \yii\db\Expression('NOW() + INTERVAL 10 YEAR');        
        if( !$user -> save() ){
            Yii::error($user -> errors);
            throw new ServerErrorHttpException('Unable to save new password.');	            
        }	

        Yii::info("User id " . $user -> id . " reset password successfully.","emailRequest");

        return $emailRequest -> delete();
    }
}

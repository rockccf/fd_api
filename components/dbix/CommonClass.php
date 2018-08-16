<?php

namespace app\components\dbix;

use app\models\Company;
use app\models\CompanyDraw;
use app\models\EmailRequest;
use app\models\ItemPriceHistory;
use app\models\Master;
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
                    } else if ($operator == "gte") {
                        if (is_null($value)) {
                            $model = $model->andWhere([">=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere([">=", "$filterModel.$column",$value]);
                        }
                    } else if ($operator == "gt") {
                        if (is_null($value)) {
                            $model = $model->andWhere([">", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere([">", "$filterModel.$column",$value]);
                        }
                    } else if ($operator == "lte") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["<=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere(["<=", "$filterModel.$column",$value]);
                        }
                    } else if ($operator == "lt") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["<=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere(["<=", "$filterModel.$column",$value]);
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
                    } else if ($operator == "gt") {
                        if (is_null($value)) {
                            $model = $model->andWhere([">", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere([">", "$filterModel.$column",$value]);
                        }
                    } else if ($operator == "lte") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["<=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere(["<=", "$filterModel.$column",$value]);
                        }
                    } else if ($operator == "lt") {
                        if (is_null($value)) {
                            $model = $model->andWhere(["<=", "$filterModel.$column",$value]);
                        } else {
                            $model = $model->andFilterWhere(["<=", "$filterModel.$column",$value]);
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
    * Author : CCF
    * Created At : 2018-07-28
    * Description :
    * Check how much is still available to bet for the certain number for certain company
    */
    public static function getAvailableBalance($number,$drawDate,$companyId,$masterId) {
        //$company = Company::findOne(['code'=>$companyCode]);
        $companyDraw = CompanyDraw::findOne(['companyId'=>$companyId,'drawDate'=>$drawDate->format('Y-m-d')]);
        $master = Master::findOne($masterId);
        $betMaxLimitBig = $master->betMaxLimitBig;
        $betMaxLimitSmall = $master->betMaxLimitSmall;
        $betMaxLimit4a = $master->betMaxLimit4a;
        $betMaxLimit4b = $master->betMaxLimit4b;
        $betMaxLimit4c = $master->betMaxLimit4c;
        $betMaxLimit4d = $master->betMaxLimit4d;
        $betMaxLimit4e = $master->betMaxLimit4e;
        $betMaxLimit4f = $master->betMaxLimit4f;
        $betMaxLimit3abc = $master->betMaxLimit3abc;
        $betMaxLimit3a = $master->betMaxLimit3a;
        $betMaxLimit3b = $master->betMaxLimit3b;
        $betMaxLimit3c = $master->betMaxLimit3c;
        $betMaxLimit3d = $master->betMaxLimit3d;
        $betMaxLimit3e = $master->betMaxLimit3e;
        $betMaxLimit5d = $master->betMaxLimit5d;
        $betMaxLimit6d = $master->betMaxLimit6d;

        $query = (new Query())->from('bet_detail')
            ->where(['companyDrawId'=>$companyDraw->id,'number'=>$number]);
        $totalBig = $query->sum('big');
        $totalSmall = $query->sum('small');
        $total4a = $query->sum('4a');
        $total4b = $query->sum('4b');
        $total4c = $query->sum('4c');
        $total4d = $query->sum('4d');
        $total4e = $query->sum('4e');
        $total4f = $query->sum('4f');
        $total3abc = $query->sum('3abc');
        $total3a = $query->sum('3a');
        $total3b = $query->sum('3b');
        $total3c = $query->sum('3c');
        $total3d = $query->sum('3d');
        $total3e = $query->sum('3e');
        $total5d = $query->sum('5d');
        $total6d = $query->sum('6d');

        $balanceBig = $betMaxLimitBig - $totalBig;
        $balanceSmall = $betMaxLimitSmall - $totalSmall;
        $balance4a = $betMaxLimit4a - $total4a;
        $balance4b = $betMaxLimit4b - $total4b;
        $balance4c = $betMaxLimit4c - $total4c;
        $balance4d = $betMaxLimit4d - $total4d;
        $balance4e = $betMaxLimit4e - $total4e;
        $balance4f = $betMaxLimit4f - $total4f;
        $balance3abc = $betMaxLimit3abc - $total3abc;
        $balance3a = $betMaxLimit3a - $total3a;
        $balance3b = $betMaxLimit3b - $total3b;
        $balance3c = $betMaxLimit3c - $total3c;
        $balance3d = $betMaxLimit3d - $total3d;
        $balance3e = $betMaxLimit3e - $total3e;
        $balance5d = $betMaxLimit5d - $total5d;
        $balance6d = $betMaxLimit6d - $total6d;

        $balanceArray = array(
            'balanceBig' => $balanceBig,
            'balanceSmall' => $balanceSmall,
            'balance4a' => $balance4a,
            'balance4b' => $balance4b,
            'balance4c' => $balance4c,
            'balance4d' => $balance4d,
            'balance4e' => $balance4e,
            'balance4f' => $balance4f,
            'balance3abc' => $balance3abc,
            'balance3a' => $balance3a,
            'balance3b' => $balance3b,
            'balance3c' => $balance3c,
            'balance3d' => $balance3d,
            'balance3e' => $balance3e,
            'balance5d' => $balance5d,
            'balance6d' => $balance6d,
            'companyDrawId' => $companyDraw->id
        );

        return $balanceArray;
    }
}

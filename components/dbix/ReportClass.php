<?php

namespace app\components\dbix;

use app\models\ItemPriceHistory;
use app\models\Tenant;
use app\models\TenderCategory;
use app\models\TenderCategoryEntry;
use app\models\TenderCategoryItem;
use app\models\TenderCategorySupplier;
use app\models\Uom;
use app\models\UomFormula;
use Yii;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

class TenantReportClass extends BaseObject
{
    /*
     * Author : Chong Chee Fei
     * Created At : 2018-02-14
     * Report : ['FILE_TEMPLATE']['REPORT']['TENANT']['TENDER']['TENDER_DETAILS']
     * Format : Excel
     * Description :
     * Construct content for the report and return content and fileName as array
     */
    public static function getTenderDetails($tenderCategoryId,$tenantId,$suppliersInvitedMode = null) {
        $returnArray = [];

        //Find out what the items are (pagination applicable)
        $tenderCategoryItems = TenderCategoryItem::find()
            ->where(['tenderCategoryId' => $tenderCategoryId])
            ->with(['item'])
            ->innerJoinWith(['item i'])
            ->orderBy('i.code')
            ->all();
        $itemIdArray = [];
        foreach ($tenderCategoryItems as $tenderCategoryItem) {
            $itemIdArray[] = $tenderCategoryItem->itemId;
        }

        //Find out who the suppliers are
        $tenderCategorySuppliers = TenderCategorySupplier::find()
            ->where(['tenderCategoryId' => $tenderCategoryId])
            ->andWhere(['not',['status'=>Yii::$app->params["TENDER"]["CATEGORY"]["SUPPLIER"]["STATUS"]["NEW"]]])
            ->with(['supplier'])
            ->innerJoinWith(['supplier s'])
            ->orderBy('invited desc,s.company')
            ->all();
        $suppliersCount = count($tenderCategorySuppliers);

        //Fetch the entries submitted by the suppliers
        $tenderCategoryEntries = TenderCategoryEntry::find()
            ->where(['tenderCategoryId' => $tenderCategoryId])
            ->andWhere(['itemId' => $itemIdArray])
            ->with(['product'])
            ->innerJoinWith(['item i','supplier s'])
            ->orderBy('i.code,s.company')
            ->all();

        //Convert object into array
        $result = ArrayHelper::toArray($tenderCategoryItems);
        for ($i=0;$i<count($result);$i++) {
            //Populate supplier array under the item
            $result[$i]["suppliers"] = ArrayHelper::toArray($tenderCategorySuppliers);
        }

        //Proceed to populate the entry by matching the item id and supplier id
        foreach ($tenderCategoryEntries as $tenderCategoryEntry) {
            for ($i=0;$i<count($result);$i++) {
                $item = $result[$i];
                for ($s=0;$s<count($item["suppliers"]);$s++) {
                    $itemSupplier = $item["suppliers"][$s];
                    if ($item["itemId"] == $tenderCategoryEntry->itemId && $itemSupplier["supplierId"] == $tenderCategoryEntry->supplierId) {
                        //Entry matched
                        $result[$i]["suppliers"][$s]["entry"] = ArrayHelper::toArray($tenderCategoryEntry);
                        break 2;
                    }
                }
            }
        }

        //Proceed to check the price and decide which supplier to recommend
        //Supplier with the lowest price will be recommended by system
        //$lastAwardEntryPrice - The award price of the supplier recommended by system (Suggest Award)
        //$tenantPackagingEntryPrice - Supplier unit price based on tenant uom
        for ($i=0;$i<count($result);$i++) {
            $item = $result[$i];
            $lastAwardEntryPrice = null;
            for ($s=0;$s<count($item["suppliers"]);$s++) {
                $currentEntry = $item["suppliers"][$s]["entry"] ?? null;
                if (empty($currentEntry)) {
                    continue;
                }

                if (!$lastAwardEntryPrice) {
                    if (!empty($currentEntry)) {
                        if ($currentEntry["differentPackaging"] == true) {
                            //Need to calculate the price based on tenant's uom
                            //Need to calculate the price based on tenant's uom
                            if (!empty($currentEntry["item"]["uomFormulaId"])) { //If there's UOM Formula defined, calculate based on the subuom
                                $tenantPackagingEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["entryPrice"],$currentEntry["product"]["uomFormulaId"],$currentEntry["item"]["uomFormulaId"],true);
                                $tenantPackagingLastEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["lastEntryPrice"],$currentEntry["product"]["uomFormulaId"],$currentEntry["item"]["uomFormulaId"],true);
                            } else { //No UOM Formula defined, calculate based on main uom
                                $tenantPackagingEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["entryPrice"],$currentEntry["product"]["uomId"],$currentEntry["item"]["uomId"],false);
                                $tenantPackagingLastEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["lastEntryPrice"],$currentEntry["product"]["uomId"],$currentEntry["item"]["uomId"],false);
                            }
                        } else {
                            $tenantPackagingEntryPrice = $currentEntry["entryPrice"];
                            $tenantPackagingLastEntryPrice = $currentEntry["lastEntryPrice"];
                        }

                        $result[$i]["suppliers"][$s]["entry"]["tenantPackagingEntryPrice"] = $tenantPackagingEntryPrice;
                        $result[$i]["suppliers"][$s]["entry"]["tenantPackagingLastEntryPrice"] = $tenantPackagingLastEntryPrice;

                        if ($item["awarded"]) { //Awarded has been updated in tender_category_item
                            if ($result[$i]["suppliers"][$s]["entry"]["awarded"] == true) {
                                $result[$i]["newAwardDiffPackaging"] = $currentEntry["differentPackaging"];
                                $result[$i]["newAwardSupplier"] = $item["suppliers"][$s]["supplier"]["company"];
                                $result[$i]["newAwardTenantPackagingPrice"] = $tenantPackagingLastEntryPrice;
                                $result[$i]["newAwardPrice"] = $result[$i]["suppliers"][$s]["entry"]["lastEntryPrice"];
                                $result[$i]["awardedEntryId"] = $result[$i]["suppliers"][$s]["entry"]["id"];
                            }
                        }

                        //Assign the supplier to be the new awarded supplier (only applicable for invited suppliers)
                        if ($result[$i]["suppliers"][$s]["invited"]) {
                            $result[$i]["suppliers"][$s]["entry"]["suggestAward"] = true;
                            //If item is never awarded, default it to as per suggestAward
                            if (!$item["awarded"]) {
                                $result[$i]["newAwardDiffPackaging"] = $currentEntry["differentPackaging"];
                                $result[$i]["newAwardSupplier"] = $item["suppliers"][$s]["supplier"]["company"];
                                $result[$i]["newAwardTenantPackagingPrice"] = $tenantPackagingLastEntryPrice;
                                $result[$i]["newAwardPrice"] = $result[$i]["suppliers"][$s]["entry"]["lastEntryPrice"];
                                $result[$i]["suppliers"][$s]["entry"]["awarded"] = true;
                                $result[$i]["awardedEntryId"] = $result[$i]["suppliers"][$s]["entry"]["id"];
                            }
                            $lastAwardEntryPrice = $tenantPackagingLastEntryPrice;
                        }
                    }
                } else {
                    if (!empty($currentEntry)) {
                        if ($currentEntry["differentPackaging"] == true) {
                            //Need to calculate the price based on tenant's uom
                            if (!empty($currentEntry["item"]["uomFormulaId"])) { //If there's UOM Formula defined, calculate based on the subuom
                                $tenantPackagingEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["entryPrice"],$currentEntry["product"]["uomFormulaId"],$currentEntry["item"]["uomFormulaId"],true);
                                $tenantPackagingLastEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["lastEntryPrice"],$currentEntry["product"]["uomFormulaId"],$currentEntry["item"]["uomFormulaId"],true);
                            } else { //No UOM Formula defined, calculate based on main uom
                                $tenantPackagingEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["entryPrice"],$currentEntry["product"]["uomId"],$currentEntry["item"]["uomId"],false);
                                $tenantPackagingLastEntryPrice = CommonClass::convertEntryPrice(
                                    $currentEntry["lastEntryPrice"],$currentEntry["product"]["uomId"],$currentEntry["item"]["uomId"],false);
                            }
                        } else {
                            $tenantPackagingEntryPrice = $currentEntry["entryPrice"];
                            $tenantPackagingLastEntryPrice = $currentEntry["lastEntryPrice"];
                        }

                        $result[$i]["suppliers"][$s]["entry"]["tenantPackagingEntryPrice"] = $tenantPackagingEntryPrice;
                        $result[$i]["suppliers"][$s]["entry"]["tenantPackagingLastEntryPrice"] = $tenantPackagingLastEntryPrice;

                        if ($item["awarded"]) { //Awarded has been updated in tender_category_item
                            if ($result[$i]["suppliers"][$s]["entry"]["awarded"] == true) {
                                $result[$i]["newAwardDiffPackaging"] = $currentEntry["differentPackaging"];
                                $result[$i]["newAwardSupplier"] = $item["suppliers"][$s]["supplier"]["company"];
                                $result[$i]["newAwardTenantPackagingPrice"] = $tenantPackagingLastEntryPrice;
                                $result[$i]["newAwardPrice"] = $result[$i]["suppliers"][$s]["entry"]["lastEntryPrice"];
                                $result[$i]["awardedEntryId"] = $result[$i]["suppliers"][$s]["entry"]["id"];
                            }
                        }

                        if ($tenantPackagingLastEntryPrice < $lastAwardEntryPrice  && $result[$i]["suppliers"][$s]["invited"]) {
                            $result[$i]["suppliers"][$s]["entry"]["suggestAward"] = true;
                            //If item is never awarded, default it to as per suggestAward
                            if (!$item["awarded"]) {
                                $result[$i]["newAwardDiffPackaging"] = $currentEntry["differentPackaging"];
                                $result[$i]["newAwardSupplier"] = $item["suppliers"][$s]["supplier"]["company"];
                                $result[$i]["newAwardTenantPackagingPrice"] = $tenantPackagingLastEntryPrice;
                                $result[$i]["newAwardPrice"] = $result[$i]["suppliers"][$s]["entry"]["lastEntryPrice"];
                                $result[$i]["suppliers"][$s]["entry"]["awarded"] = true;
                                $result[$i]["awardedEntryId"] = $result[$i]["suppliers"][$s]["entry"]["id"];
                            }
                            $lastAwardEntryPrice = $tenantPackagingLastEntryPrice;
                            //Find the previous entries and mark the suggestAward back to false
                            $k = $s - 1;
                            while ($k >= 0) {
                                $previousEntry = $result[$i]["suppliers"][$k]["entry"] ?? null;
                                if (!empty($previousEntry)) {
                                    unset($result[$i]["suppliers"][$k]["entry"]["suggestAward"]);
                                    unset($result[$i]["suppliers"][$k]["entry"]["awarded"]);
                                }
                                $k--;
                            }
                        }
                    }
                }
            }

            //Sort the price from lowest to highest
            usort($result[$i]["suppliers"], function ($item1, $item2) { //Sort in ascending order
                //To sort the null entries first
                //Item 1 entry is null, item 2 entry is not null, hence moving item2 up in the list
                if (!isset($item1['entry']) && isset($item2['entry'])) {
                    return 1;
                }
                //Item 1 entry is not null, item 2 entry is null, hence item 1 will stay where it is
                if (isset($item1['entry']) && !isset($item2['entry'])) {
                    return -1;
                }
                //Now, proceed to sort by prices if both entries are not null
                if (isset($item1['entry']) && isset($item2['entry'])) {
                    //Item 1 entry is null, item 2 entry is not null, hence moving item2 up in the list
                    if (!isset($item1['entry']['tenantPackagingLastEntryPrice']) && isset($item2['entry']['tenantPackagingLastEntryPrice'])) {
                        return 1;
                    }

                    //Item 1 entry is not null, item 2 entry is null, hence item 1 will stay where it is
                    if (isset($item1['entry']['tenantPackagingLastEntryPrice']) && !isset($item2['entry']['tenantPackagingLastEntryPrice'])) {
                        return -1;
                    }

                    //Now, proceed to sort by prices if both entries are not null
                    if (isset($item1['entry']['tenantPackagingLastEntryPrice']) && isset($item2['entry']['tenantPackagingLastEntryPrice'])) {
                        return $item1['entry']['tenantPackagingLastEntryPrice'] <=> $item2['entry']['tenantPackagingLastEntryPrice']; //usort is using quicksort algorithm to sort the arra
                    }
                }
            });
        }

        $tenderCategory = TenderCategory::findOne(['id'=>$tenderCategoryId]);
        $tender = $tenderCategory->tender;

        //Proceed to get current awarded price and supplier for each item
        for ($i=0;$i<count($result);$i++) {
            $itemId = $result[$i]["itemId"];

            $iph = ItemPriceHistory::find()
                ->alias('iph')
                ->where(['iph.itemId'=>$itemId,'iph.type'=>Yii::$app->params['ITEM']['PRICE_HISTORY']['TYPE']['TENDER_CATEGORY_ENTRY']])
                ->andWhere(['<','iph.priceDate',$tender->createdAt])
                ->innerJoinWith(['tenderCategoryEntry tce'])
                ->innerJoin('tender_category tc','tce.tenderCategoryId = tc.id')
                ->innerJoin('tender t','tc.tenderId = t.id')
                ->orderBy('priceDate desc')
                ->one();

            if ($iph) {
                //Found current award price
                $tenantPackagingCurrentAwardPrice = $iph->unitPrice;
                if ($iph->tenderCategoryEntry->differentPackaging == true) {
                    //Need to calculate the price based on tenant's uom
                    if (!empty($iph->item->uomFormulaId)) { //If there's UOM Formula defined, calculate based on the subuom
                        $tenantPackagingCurrentAwardPrice = CommonClass::convertEntryPrice(
                            $iph->unitPrice,$iph->tenderCategoryEntry->product->uomFormulaId,$iph->item->uomFormulaId,true);
                    } else { //No UOM Formula defined, calculate based on main uom
                        $tenantPackagingCurrentAwardPrice = CommonClass::convertEntryPrice(
                            $iph->unitPrice,$iph->tenderCategoryEntry->product->uomId,$iph->item->uomId,false);
                    }
                }
                $result[$i]["currentAwardDiffPackaging"] = $iph->tenderCategoryEntry->differentPackaging;
                $result[$i]["currentAwardPrice"] = $iph->unitPrice;
                $result[$i]["tenantPackagingCurrentAwardPrice"] = $tenantPackagingCurrentAwardPrice;
                $result[$i]["currentAwardSupplier"] = $iph->supplier->company;
            }
        }

        /*
         * Render excel part starts
         */
        $objPHPExcel = new \PHPExcel();
        $sheet = 0;
        $objPHPExcel->setActiveSheetIndex($sheet);
        $activeSheet = $objPHPExcel->getActiveSheet();

        $tenant = Tenant::findOne($tenantId);

        //Main Summary Info
        $activeSheet->setCellValueByColumnAndRow(0,1,$tenant->name);
        $activeSheet->setCellValueByColumnAndRow(0,2,'Category :');
        $activeSheet->setCellValueByColumnAndRow(2,2,$tenderCategory->category->name);
        $activeSheet->setCellValueByColumnAndRow(4,2,'Different UOM');
        $activeSheet->getStyleByColumnAndRow(4,2)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '5BC0DE') //Bootstrap info color, blue
                )
            )
        );

        $activeSheet->setCellValueByColumnAndRow(0,3,'Reference :');
        $activeSheet->setCellValueByColumnAndRow(2,3,$tenderCategory->tender->reference);
        $activeSheet->setCellValueByColumnAndRow(4,3,'Awarded');
        $activeSheet->getStyleByColumnAndRow(4,3)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => '5CB85C') //Bootstrap success color, green
                )
            )
        );

        $activeSheet->setCellValueByColumnAndRow(0,4,'Contract Period :');
        $contractStart = new \DateTime($tenderCategory->tender->contractStart);
        $contractEnd = new \DateTime($tenderCategory->tender->contractEnd);
        $contractPeriod = $contractStart->format(Yii::$app->params['FORMAT']['DATE']).' to '.$contractEnd->format(Yii::$app->params['FORMAT']['DATE']);
        $activeSheet->setCellValueByColumnAndRow(2,4,$contractPeriod);
        $activeSheet->setCellValueByColumnAndRow(4,4,'Item List');
        $activeSheet->getStyleByColumnAndRow(4,4)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'f5f5f5') //Bootstrap active color, grey
                )
            )
        );

        $headerRow1Index = 6;
        $headers1 = array(
            array("name"=>"No","autoSize"=>false,"width"=>8),
            array("name"=>"Name","autoSize"=>false,"width"=>50),
            array("name"=>"Description","autoSize"=>false,"width"=>50),
            array("name"=>"Code","autoSize"=>true,"width"=>10),
            array("name"=>"Estimate Comsumption","autoSize"=>false,"width"=>13),
            array("name"=>"UOM","autoSize"=>true),
            array("name"=>"Current Price","autoSize"=>false,"width"=>30),
            array("name"=>"Current Supplier","autoSize"=>true),
            array("name"=>"New Price","autoSize"=>true),
            array("name"=>"New Supplier","autoSize"=>true),
            array("name"=>"Remarks","autoSize"=>true),
        );
        //$headers1 = ["No","Name","Description","Code","Estimate Consumption","UOM","Current Price","Current Supplier","New Price","New Supplier","Remarks"];
        $subheaders1 = ["Supplier","","Invited","Name","","UOM","Brand","Country","is Halal","Alternative Item",
            "Price","Latest Price","Remarks","Awarded"];

        $invitedSupplierCount = 0;
        $openSupplierCount = 0;
        foreach ($tenderCategorySuppliers as $tenderCategorySupplier) {
            if ($tenderCategorySupplier->invited) {
                $invitedSupplierCount++;
            } else {
                $openSupplierCount++;
            }
        }

        /*
         * Header Section
         */
        //Draw header1 row
        foreach ($headers1 as $index => $header) {
            $name = $header["name"];
            $autoSize = $header["autoSize"];
            $width = $header["width"] ?? null;
            $activeSheet->setCellValueByColumnAndRow($index,$headerRow1Index,$name);
            $activeSheet->getColumnDimensionByColumn($index)->setAutoSize($autoSize);
            $activeSheet->getColumnDimensionByColumn($index)->setWidth($width);
            $activeSheet->getStyleByColumnAndRow($index,$headerRow1Index)->getAlignment()->setWrapText(true);
            $activeSheet->getStyleByColumnAndRow($index,$headerRow1Index)->getFont()->setBold(true);
        }
        //Set custom grey color for header row
        $activeSheet->getStyleByColumnAndRow(0, $headerRow1Index, 10, $headerRow1Index)->applyFromArray(
            array(
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'a6a6a6') //Custom grey color
                )
            )
        );

        /*
         * Detail Section
         */
        $rowIndex = 7;
        $tenderEntries = [];
        for ($i=0;$i<count($result);$i++) {
            $item = $result[$i];

            //Set grey color for item row
            $activeSheet->getStyleByColumnAndRow(0, $rowIndex, 10, $rowIndex)->applyFromArray(
                array(
                    'fill' => array(
                        'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                        'color' => array('rgb' => 'f5f5f5') //Bootstrap active color, grey
                    )
                )
            );

            //NO
            $activeSheet->setCellValueByColumnAndRow(0,$rowIndex,$i+1);

            //Name
            $activeSheet->setCellValueByColumnAndRow(1,$rowIndex,$item["item"]["name"]);

            //Description
            $activeSheet->setCellValueByColumnAndRow(2,$rowIndex,$item["item"]["description"]);

            //Code
            $activeSheet->setCellValueByColumnAndRow(3,$rowIndex,$item["item"]["code"]);

            //Estimate consumption
            $estimateConsumption = $item["estimateConsumption"];
            $activeSheet->setCellValueByColumnAndRow(4,$rowIndex,$estimateConsumption);

            //UOM
            if ($item["item"]["uomFormulaId"]) {
                $uom = UomFormula::findOne($item["item"]["uomFormulaId"]);
                $uomText = $uom->formulaText;
            } else {
                $uom = Uom::findOne($item["item"]["uomId"]);
                $uomText = $uom->symbol;
            }
            $activeSheet->setCellValueByColumnAndRow(5,$rowIndex,$uomText);

            //Current Awarded Price
            $currentAwardPrice = $item["tenantPackagingCurrentAwardPrice"] ?? null;
            $activeSheet->setCellValueByColumnAndRow(6,$rowIndex,$currentAwardPrice);
            $currentAwardDiffPackaging = $item["currentAwardDiffPackaging"] ?? null;
            if ($currentAwardDiffPackaging) {
                $activeSheet->getCommentByColumnAndRow(6, $rowIndex)->getText()->createTextRun('Supplier Quoted Price:')->getFont()->setBold(true);
                $activeSheet->getCommentByColumnAndRow(6, $rowIndex)->getText()->createTextRun("\r\n");
                $activeSheet->getCommentByColumnAndRow(6, $rowIndex)->getText()->createTextRun($item["currentAwardPrice"]);
            }

            //Current Awarded Supplier
            $currentAwardSupplier = $item["currentAwardSupplier"] ?? null;
            $activeSheet->setCellValueByColumnAndRow(7,$rowIndex,$currentAwardSupplier);

            //New Awarded Price
            $newAwardPrice = $item["newAwardPrice"] ?? null;
            $newAwardTenantPackagingPrice = $item["newAwardTenantPackagingPrice"] ?? null;
            $activeSheet->setCellValueByColumnAndRow(8,$rowIndex,$newAwardTenantPackagingPrice);
            $newAwardDiffPackaging = $item["newAwardDiffPackaging"] ?? null;
            if ($newAwardDiffPackaging) {
                $activeSheet->getCommentByColumnAndRow(8, $rowIndex)->getText()->createTextRun('Supplier Quoted Price:')->getFont()->setBold(true);
                $activeSheet->getCommentByColumnAndRow(8, $rowIndex)->getText()->createTextRun("\r\n");
                $activeSheet->getCommentByColumnAndRow(8, $rowIndex)->getText()->createTextRun($newAwardPrice);
            }

            //New Awarded Supplier
            $newAwardSupplier = $item["newAwardSupplier"] ?? null;
            $activeSheet->setCellValueByColumnAndRow(9,$rowIndex,$newAwardSupplier);

            //Remarks
            $activeSheet->setCellValueByColumnAndRow(10,$rowIndex,$item["remarks"]);

            //Set wrapping text for the item row
            $activeSheet->getStyleByColumnAndRow(0,$rowIndex,10,$rowIndex)->getAlignment()->setWrapText(true);

            //Only print the supplier header for once
            if ($i == 0) {
                //Draw the sub header row for each item (supplier header row)
                $rowIndex += 1;

                foreach ($subheaders1 as $index => $subheader) {
                    $activeSheet->setCellValueByColumnAndRow($index,$rowIndex,$subheader);
                    $activeSheet->getStyleByColumnAndRow($index,$rowIndex)->getAlignment()->setWrapText(true);
                    $activeSheet->getStyleByColumnAndRow($index,$rowIndex)->getFont()->setBold(true)->setItalic(true);
                }
                //Merge the cell for supplier company name
                $activeSheet->mergeCellsByColumnAndRow(0,$rowIndex,1,$rowIndex);

                //Merge the cell for supplier product name
                $activeSheet->mergeCellsByColumnAndRow(3,$rowIndex,4,$rowIndex);

                //Set custom grey color for header row
                $activeSheet->getStyleByColumnAndRow(0, $rowIndex, 13, $rowIndex)->applyFromArray(
                    array(
                        'fill' => array(
                            'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                            'color' => array('rgb' => 'E0E0E0') //Light gray 1
                        )
                    )
                );
            }

            foreach ($result[$i]["suppliers"] as $supplier) {
                if (empty($supplier["entry"])) {
                    continue;
                }
                $rowIndex++;

                //Supplier
                //Merge the cell for supplier company name
                $activeSheet->mergeCellsByColumnAndRow(0,$rowIndex,1,$rowIndex);
                $activeSheet->setCellValueByColumnAndRow(0,$rowIndex,$supplier["supplier"]["company"]);

                //Invited
                if ($supplier["invited"]) {
                    $invited = "Yes";
                } else {
                    $invited = "No";
                }
                $activeSheet->setCellValueByColumnAndRow(2,$rowIndex,$invited);

                //Product Name
                //Merge the cell for supplier product name
                $activeSheet->mergeCellsByColumnAndRow(3,$rowIndex,4,$rowIndex);
                $activeSheet->setCellValueByColumnAndRow(3,$rowIndex,$supplier["entry"]["product"]["name"]);

                //UOM
                if ($supplier["entry"]["product"]["uomFormulaId"]) {
                    $uom = UomFormula::findOne($supplier["entry"]["product"]["uomFormulaId"]);
                    $uomText = $uom->formulaText;
                } else {
                    $uom = Uom::findOne($supplier["entry"]["product"]["uomId"]);
                    $uomText = $uom->symbol;
                }

                //Brand
                $activeSheet->setCellValueByColumnAndRow(6,$rowIndex,$supplier["entry"]["product"]["brand"]);

                //Country
                $activeSheet->setCellValueByColumnAndRow(7,$rowIndex,$supplier["entry"]["product"]["country"]["name"]);

                //Is Halal
                if ($supplier["entry"]["product"]["isHalal"]) {
                    $isHalal = "Yes";
                } else {
                    $isHalal = "No";
                }
                $activeSheet->setCellValueByColumnAndRow(8,$rowIndex,$isHalal);

                //Alternative Item
                if ($supplier["entry"]["isAlternative"]) {
                    $isAlternative = "Yes";
                } else {
                    $isAlternative = "No";
                }
                $activeSheet->setCellValueByColumnAndRow(9,$rowIndex,$isAlternative);

                //Price
                $activeSheet->setCellValueByColumnAndRow(10,$rowIndex,$supplier["entry"]["tenantPackagingEntryPrice"]);
                if ($supplier["entry"]["differentPackaging"]) {
                    $activeSheet->getCommentByColumnAndRow(10, $rowIndex)->getText()->createTextRun('Supplier Quoted Price:')->getFont()->setBold(true);
                    $activeSheet->getCommentByColumnAndRow(10, $rowIndex)->getText()->createTextRun("\r\n");
                    $activeSheet->getCommentByColumnAndRow(10, $rowIndex)->getText()->createTextRun($supplier["entry"]["entryPrice"]);
                }

                //Latest Price
                $activeSheet->setCellValueByColumnAndRow(11,$rowIndex,$supplier["entry"]["tenantPackagingLastEntryPrice"]);
                if ($supplier["entry"]["differentPackaging"]) {
                    $activeSheet->getCommentByColumnAndRow(11, $rowIndex)->getText()->createTextRun('Supplier Quoted Price:')->getFont()->setBold(true);
                    $activeSheet->getCommentByColumnAndRow(11, $rowIndex)->getText()->createTextRun("\r\n");
                    $activeSheet->getCommentByColumnAndRow(11, $rowIndex)->getText()->createTextRun($supplier["entry"]["lastEntryPrice"]);
                }

                //Remarks
                $activeSheet->setCellValueByColumnAndRow(12,$rowIndex,$supplier["entry"]["supplierRemarks"]);

                //Awarded
                $supplierAwarded = $supplier["entry"]["awarded"] ?? null;
                if ($supplierAwarded) {
                    $awarded = "Yes";
                    $activeSheet->getStyleByColumnAndRow(13,$rowIndex)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '5CB85C') //Bootstrap success color, green
                            )
                        )
                    );
                } else {
                    $awarded = "No";
                }
                $activeSheet->setCellValueByColumnAndRow(13,$rowIndex,$awarded);

                //Apply the different packaging coloring later than award
                $activeSheet->setCellValueByColumnAndRow(5,$rowIndex,$uomText);
                if ($supplier["entry"]["differentPackaging"]) {
                    $activeSheet->getStyleByColumnAndRow(5,$rowIndex)->applyFromArray(
                        array(
                            'fill' => array(
                                'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                                'color' => array('rgb' => '5BC0DE') //Bootstrap info color, blue
                            )
                        )
                    );
                }

                //Set wrapping text for the supplier entry row
                $activeSheet->getStyleByColumnAndRow(0,$rowIndex,13,$rowIndex)->getAlignment()->setWrapText(true);
            }
            $rowIndex += 2;
        }

        $today = date("Ymd");
        $fileName = $tenderCategory->tender->reference."_".$tenderCategory->category->name."_TenderDetails_$today.xlsx";

        $returnArray = array("objPHPExcel"=>$objPHPExcel,"fileName"=>$fileName);
        return $returnArray;
    }

}
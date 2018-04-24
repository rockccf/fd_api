<?php

namespace app\components\dbix;

use app\controllers\PdfController;
use app\models\Category;
use app\models\EmailQueue;
use app\models\Rfq;
use app\models\Supplier;
use app\models\Tenant;
use app\models\Tender;
use Yii;
use yii\base\BaseObject;
use yii\db\Expression;
use yii\helpers\Json;
use yii\web\NotFoundHttpException;

class EmailClass extends BaseObject
{
    /*
    * Author : Chong Chee Fei
    * Created At : 2017-11-29
    * Description :
    * To send emails
    */
    public static function sendEmail($sender, array $recipients, $subject, $template, array $parameters, array $remarks = null, array $attachments = null,
                                     array $ccRecipients = null, array $bccRecipients = null, bool $deleteAttachments = true, $emailQueueId = null) {
        $email = Yii::$app->mailer->compose($template,$parameters);
        if (empty($sender)) {
            $sender = Yii::$app->params['GLOBAL']['EMAIL_FROM'];
        }

        $email->setFrom($sender)
            ->setTo($recipients)
            ->setSubject($subject);

        if (!empty($ccRecipients) && count($ccRecipients) > 0) {
            $email->setCc($ccRecipients);
        }

        if (!empty($bccRecipients) && count($bccRecipients) > 0) {
            $email->setBcc($bccRecipients);
        }

        if (!empty($attachments) && count($attachments) > 0) {
            //Loop through and attach the file
            foreach ($attachments as $attachment) {
                $email->attach($attachment);
            }
        }
        
        if (!$email->send())
        {
            //Proceed to insert into EmailQueue if it's not from EmailQueue
            if ($emailQueueId) {
                $eq = EmailQueue::findOne($emailQueueId);
                $eq->lastTriedAt = new Expression('NOW()');
            } else {
                $eq = new EmailQueue();
                $eq->sender = Json::encode($sender);
                $eq->recipients = Json::encode($recipients);
                $eq->ccRecipients = (!empty($ccRecipients) && count($ccRecipients) > 0) ? Json::encode($ccRecipients) : null;
                $eq->bccRecipients = (!empty($bccRecipients) && count($bccRecipients) > 0) ? Json::encode($bccRecipients) : null;
                $eq->subject = $subject;
                $eq->template = $template;
                $eq->parameters = Json::encode($parameters);
                $eq->remarks = (!empty($remarks) && count($remarks) > 0) ? Json::encode($remarks) : null;
                $eq->attachments = (!empty($attachments) && count($attachments) > 0) ? Json::encode($attachments) : null;
            }

            if (!$eq->save()) {
                Yii::error($eq->errors);
            }
            return false;
        } else {
            //Sent successfully, delete the record from email_queue
            if ($emailQueueId) {
                EmailQueue::deleteAll(['id'=>$emailQueueId]);
            }

            //Email sent successfully, proceed to delete the attachments if any
            $message = "Email sent successfully : sender : $sender, recipients : ".implode($recipients,'|').", subject : $subject";
            $message .= ", template : $template";
            Yii::info($message,"email");
            Yii::info($parameters,"email");
            if (!empty($attachments) && count($attachments) > 0 && $deleteAttachments) {
                Yii::info($attachments,"email");
                //Loop through and delete the file
                foreach ($attachments as $attachment) {
                    unlink($attachment);
                }
            }
            return true;
        }
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2017-11-30
    * Description :
    * To send new invitation emails to supplier (tender/rfq)
    */
    public static function sendSupplierNewInvitation($invitationType, $reference, $categoryId, $supplierId, $tenantId) {
        $params["reference"] = $reference;
        $category = Category::findOne($categoryId);
        $tenant = Tenant::findOne($tenantId);
        $supplier = Supplier::find()
            ->where(['id'=>$supplierId])
            ->with([
                'users' => function ($query) {
                    $query->orderBy('username');
                }
            ])
            ->one();
        $recipients = [];
        $supplierUsers = $supplier->users;
        forEach ($supplierUsers as $supplierUser) {
            $recipients[] = $supplierUser->email;
        }
        $params["category"] = $category->name;
        $params["hotelName"] = $tenant->name;
        $params["tenantType"] = $tenant->tenantType;

        if ($invitationType == "R") { //R - RFQ
            $template = "supplier_new_rfq_invitation";
            $subject = "[HorecaBid] New RFQ Invitation - Ref: $reference, $category->name";
        } else { //T - Tender
            $template = "supplier_new_tender_invitation";
            $subject = "[HorecaBid] New Tender Invitation - Ref: $reference, $category->name";
        }

        $hotelAddress = $tenant->address1;
        if (!empty($tenant->address2)) $hotelAddress .= ", $tenant->address2";
        if (!empty($tenant->address3)) $hotelAddress .= ", $tenant->address3";
        $hotelAddress .= " ".$tenant->postcode." ".$tenant->city->name.", ".$tenant->state->name.", ".$tenant->country->name;
        $params["hotelAddress"] = $hotelAddress;

        $tenantConfigurations = $tenant->tenantConfigurations;
        $emailContactInfo = null;
        foreach ($tenantConfigurations as $tenantConfiguration) {
            if ($tenantConfiguration->type == Yii::$app->params["TENANT"]["CONFIGURATION"]["TYPE"]["EMAIL_CONTACT_INFO"]) {
                $emailContactInfo = $tenantConfiguration->valueJson;
                break;
            }
        }
        $emailContactInfoArray = Json::decode($emailContactInfo);
        $contactInfo = null;
        $contactInfo = implode(' or ', array_map(function ($emailContactInfo) {
            return $emailContactInfo['contactPerson']." at ".$emailContactInfo["contactNumber"];
        }, $emailContactInfoArray));
        $params["contactInfo"] = $contactInfo;

        $emailRemarks = array("tenantId"=>$tenantId,"supplierId"=>$supplierId);
        $result = self::sendEmail(null,$recipients,$subject,$template,$params,$emailRemarks);
        return $result;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2017-11-30
    * Description :
    * To send new invitation emails to supplier (tender/rfq)
    */
    public static function sendSupplierNego($invitationType, $reference, $categoryId, $supplierId, $tenantId, $itemArray) {
        $params["reference"] = $reference;
        $category = Category::findOne($categoryId);
        $tenant = Tenant::findOne($tenantId);
        $supplier = Supplier::find()
            ->where(['id'=>$supplierId])
            ->with([
                'users' => function ($query) {
                    $query->orderBy('username');
                }
            ])
            ->one();
        $recipients = [];
        $supplierUsers = $supplier->users;
        forEach ($supplierUsers as $supplierUser) {
            $recipients[] = $supplierUser->email;
        }
        $params["category"] = $category->name;
        $params["hotelName"] = $tenant->name;
        $params["itemArray"] = $itemArray;

        if ($invitationType == "R") { //R - RFQ
            $template = "supplier_rfq_nego";
            $subject = "[HorecaBid] RFQ Negotiation - Ref: $reference, $category->name";
        } else { //T - Tender
            $template = "supplier_tender_nego";
            $subject = "[HorecaBid] Tender Negotiation - Ref: $reference, $category->name";
        }

        $tenantConfigurations = $tenant->tenantConfigurations;
        $emailContactInfo = null;
        foreach ($tenantConfigurations as $tenantConfiguration) {
            if ($tenantConfiguration->type == Yii::$app->params["TENANT"]["CONFIGURATION"]["TYPE"]["EMAIL_CONTACT_INFO"]) {
                $emailContactInfo = $tenantConfiguration->valueJson;
                break;
            }
        }
        $emailContactInfoArray = Json::decode($emailContactInfo);
        $contactInfo = null;
        $contactInfo = implode(' or ', array_map(function ($emailContactInfo) {
            return $emailContactInfo['contactPerson']." at ".$emailContactInfo["contactNumber"];
        }, $emailContactInfoArray));
        $params["contactInfo"] = $contactInfo;

        $emailRemarks = array("tenantId"=>$tenantId,"supplierId"=>$supplierId);
        $result = self::sendEmail(null,$recipients,$subject,$template,$params,$emailRemarks);
        return $result;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2018-03-16
    * Description :
    * To send thank you emails to supplier (tender/rfq)
    */
    public static function sendSupplierThankYou($type, $processId, $tenantId) {
        $result = false;
        $tenant = Tenant::findOne($tenantId);
        $tenantConfigurations = $tenant->tenantConfigurations;
        foreach ($tenantConfigurations as $tenantConfiguration) {
            if ($tenantConfiguration->type == Yii::$app->params["TENANT"]["CONFIGURATION"]["TYPE"]["DO_NOT_SEND_THANK_YOU_EMAIL_TO_SUPPLIER"]) {
                if ($tenantConfiguration->value) {
                    Yii::info("Tenant ".$tenant->name." has configured not to send thank you email to suppliers.");
                    return true;
                }
            }
        }

        $hotelAddress = $tenant->address1;
        if (!empty($tenant->address2)) $hotelAddress .= ", $tenant->address2";
        if (!empty($tenant->address3)) $hotelAddress .= ", $tenant->address3";
        $hotelAddress .= " ".$tenant->postcode." ".$tenant->city->name.", ".$tenant->state->name.", ".$tenant->country->name;
        $params["hotelAddress"] = $hotelAddress;
        $params["hotelName"] = $tenant->name;
        $params["tenantType"] = $tenant->tenantType;

        switch ($type) {
            case 1: //Tender
                $template = "supplier_tender_thank_you";
                $process = Tender::findOne($processId);
                $reference = $process->reference;
                $params["reference"] = $reference;
                foreach ($process->tenderCategories as $tenderCategory) {
                    $subject = "[HorecaBid] Thank You for Tender Participation - Ref: $reference, ".$tenderCategory->category->name;

                    $params["category"] = $tenderCategory->category->name;
                    $tcss = $tenderCategory->tenderCategorySuppliers;
                    foreach ($tcss as $tcs) {
                        $supplier = Supplier::find()
                            ->where(['id'=>$tcs->supplierId])
                            ->with([
                                'users' => function ($query) {
                                    $query->orderBy('username');
                                }
                            ])
                            ->one();

                        //Send to every user under the supplier
                        $recipients = [];
                        $supplierUsers = $supplier->users;
                        forEach ($supplierUsers as $supplierUser) {
                            $recipients[] = $supplierUser->email;
                        }

                        //See if the supplier has any awarded entry
                        $tces = $tenderCategory->getTenderCategoryEntries()
                            ->where(['supplierId'=>$tcs->supplierId,'awarded'=>true])
                            ->all();

                        $attachments = [];
                        if (count($tces) > 0) {
                            $awarded = true;
                            //Proceed to generate the award letter pdf and attach it
                            $attachments[] = TenantPdfClass::getAwardLetterDetails($processId,$tenderCategory->id,$tcs->supplierId,false);
                        } else {
                            $awarded = false;
                        }
                        $params["awarded"] = $awarded;

                        $emailRemarks = array("tenantId"=>$tenantId,"supplierId"=>$supplier->id);
                        $result = self::sendEmail(null,$recipients,$subject,$template,$params,$emailRemarks,$attachments);
                    }
                }

                break;
            case 2: //RFQ
                $template = "supplier_rfq_thank_you";
                $process = Tender::findOne($processId);
                $reference = $process->reference;
                $params["reference"] = $reference;
                foreach ($process->rfqCategories as $rfqCategory) {
                    $subject = "Thank You for RFQ Participation - Ref: $reference, ".$rfqCategory->category->name;

                    $params["category"] = $rfqCategory->category->name;
                }

                break;
        }

        return $result;
    }

    /*
    * Author : Chong Chee Fei
    * Created At : 2018-01-31
    * Description :
    * To send acknowledgment/approval(tender) / approval(rfq) emails to tenant
    */
    public static function sendTenantAcknowledgementApproval($type, $processId) {
        //$type : 1 - Tenant Tender Acknowledgement ; 2 - Tenant Tender Approval ; 3 - Tenant RFQ Approval
        $recipients = [];
        $result = false;

        switch ($type) {
            case 1:
                $template = "tenant_tender_acknowledgement";
                $process = Tender::findOne($processId);
                $reference = $process->reference;
                $categoriesText = $process->categoriesText;
                $subject = "[HorecaBid] Pending Tender Acknowledgement - Ref: $reference ($categoriesText)";
                $pwf = $process->acknowledgeProcessWorkflow;
                break;
            case 2:
                $template = "tenant_tender_approval";
                $process = Tender::findOne($processId);
                $reference = $process->reference;
                $categoriesText = $process->categoriesText;
                $subject = "[HorecaBid] Pending Tender Approval - Ref: $reference ($categoriesText)";
                $pwf = $process->approveProcessWorkflows[0]; //Get the latest one
                break;
            case 3:
                $template = "tenant_rfq_approval";
                $process = Rfq::findOne($processId);
                $reference = $process->reference;
                $categoriesText = $process->categoriesText;
                $subject = "[HorecaBid] Pending RFQ Approval - Ref: $reference ($categoriesText)";
                break;
        }
        if (!empty($pwf)) {
            forEach ($pwf->pwfpCurrentLevelUsers as $user) {
                $recipients[] = $user->email;
            }

            $params["reference"] = $reference;
            $params["categoriesText"] = $categoriesText;

            $result = self::sendEmail(null,$recipients,$subject,$template,$params);
        }

        return $result;
    }
}
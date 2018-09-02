<?php

namespace app\components\ccf;

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
}
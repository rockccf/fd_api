<?php
namespace app\commands;

use app\components\dbix\EmailClass;
use app\models\EmailQueue;
use Yii;
use yii\console\Controller;
use yii\db\Expression;
use yii\helpers\Json;

class EmailController extends Controller
{
    public function actionSendEmailQueue()
    {
        $eqs = EmailQueue::find()->all();
        foreach ($eqs as $eq) {
            try {
                $recipients = Json::decode($eq->recipients);
                $ccRecipients = Json::decode($eq->ccRecipients);
                $bccRecipients = Json::decode($eq->bccRecipients);
                $subject = $eq->subject;
                $template = $eq->template;
                $parameters = Json::decode($eq->parameters);
                $remarks = Json::decode($eq->remarks);
                $attachments = Json::decode($eq->attachments);

                EmailClass::sendEmail(null, $recipients, $subject, $template, $parameters, $remarks, $attachments, $ccRecipients, $bccRecipients, true, $eq->id);
            } catch (\Throwable $e) {
                //Skip this email as unexpected exception encountered
                Yii::error("EmailQueue id : ".$eq->id." cannot be sent.");
                Yii::error($e);
                $eq->lastTriedAt = new Expression('NOW()');
                if (!$eq->save()) {
                    Yii::error($eq->errors);
                }
                continue;
            }
        }
    }
}
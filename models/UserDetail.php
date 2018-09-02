<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "user_detail".
 *
 * @property int $id
 * @property int $version
 * @property int $userId
 * @property int $packageId
 * @property string $creditLimit
 * @property string $creditGranted
 * @property string $balance
 * @property string $outstandingBet
 * @property string $extra4dCommRate
 * @property string $extra6dCommRate
 * @property string $extraGdCommRate
 * @property int $betMethod
 * @property int $autoTransfer
 * @property int $autoTransferMode
 * @property array $autoTransferDays
 * @property int $betGdLotto
 * @property int $bet6d
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property User $user
 * @property Package $package
 */
class UserDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'userId', 'packageId', 'betMethod', 'autoTransfer', 'autoTransferMode', 'betGdLotto', 'bet6d'], 'integer'],
            [['userId', 'packageId', 'creditLimit', 'betMethod'], 'required'],
            [['creditLimit', 'creditGranted', 'extra4dCommRate', 'extra6dCommRate', 'extraGdCommRate', 'balance', 'outstandingBet'], 'number'],
            [['autoTransferDays'], 'safe'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
            [['packageId'], 'exist', 'skipOnError' => true, 'targetClass' => Package::class, 'targetAttribute' => ['packageId' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'userId' => 'User ID',
            'packageId' => 'Package ID',
            'creditLimit' => 'Credit Limit',
            'creditGranted' => 'Credit Granted',
            'balance' => 'Balance',
            'outstandingBet' => 'Outstanding Bet',
            'extra4dCommRate' => 'Extra4d Comm Rate',
            'extra6dCommRate' => 'Extra6d Comm Rate',
            'extraGdCommRate' => 'Extra Gd Comm Rate',
            'betMethod' => 'Bet Method',
            'autoTransfer' => 'Auto Transfer',
            'autoTransferMode' => 'Auto Transfer Mode',
            'autoTransferDays' => 'Auto Transfer Days',
            'betGdLotto' => 'Bet Gd Lotto',
            'bet6d' => 'Bet6d',
            'createdBy' => 'Created By',
            'createdAt' => 'Created At',
            'updatedBy' => 'Updated By',
            'updatedAt' => 'Updated At',
        ];
    }

    public function behaviors()
    {
        return [
            'TimestampBehavior' => [
                'class' => TimestampBehavior::class, //Automatically update the timestamp columns
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt', 'updatedAt'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updatedAt'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
            'BlameableBehavior' => [
                'class' => BlameableBehavior::class, //Automatically update the user id columns
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
                'defaultValue' => 1
            ],
        ];
    }

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        $fields['creditLimit'] = function ($model) {
            return floatval($model->creditLimit); //Cast string to float/double type
        };

        $fields['creditGranted'] = function ($model) {
            return floatval($model->creditGranted); //Cast string to float/double type
        };

        //Credit available for agent to grant to downline
        $fields['creditLimitAvailableToGrant'] = function ($model) {
            $result = 0;
            if ($model->user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                $result = $model->creditLimit - $model->creditGranted;
            }
            return floatval($result); //Cast string to float/double type
        };

        $fields['balance'] = function ($model) {
            return floatval($model->balance); //Cast string to float/double type
        };

        $fields['outstandingBet'] = function ($model) {
            return floatval($model->outstandingBet); //Cast string to float/double type
        };

        $fields['outstandingBetDownline'] = function ($model) {
            $result = 0;
            if ($model->user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                //Get the downline players outstanding
                $players = $model->user->players;
                foreach ($players as $player) {
                    $result += $player->userDetail->outstandingBet;
                }
            }
            return floatval($result); //Cast string to float/double type
        };

        $fields['extra4dCommRate'] = function ($model) {
            return floatval($model->extra4dCommRate); //Cast string to float/double type
        };

        $fields['extra6dCommRate'] = function ($model) {
            return floatval($model->extra6dCommRate); //Cast string to float/double type
        };

        $fields['extraGdCommRate'] = function ($model) {
            return floatval($model->extraGdCommRate); //Cast string to float/double type
        };

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['package'] = function ($model) {
            return $model->package;
        };

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'packageId']);
    }
}

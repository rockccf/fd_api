<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "company_draw".
 *
 * @property int $id
 * @property int $version
 * @property string $drawDate
 * @property int $status
 * @property string $drawTime
 * @property string $stopBetTime
 * @property string $checkResultsDate
 * @property int $companyId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property BetDetail[] $betDetails
 * @property Company $company
 */
class CompanyDraw extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'company_draw';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'status', 'companyId'], 'integer'],
            [['drawDate', 'status', 'companyId'], 'required'],
            [['drawDate', 'drawTime', 'stopBetTime', 'checkResultsDate'], 'safe'],
            [['companyId'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['companyId' => 'id']]
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
            'drawDate' => 'Draw Date',
            'status' => 'Status',
            'drawTime' => 'Draw Time',
            'stopBetTime' => 'Stop Bet Time',
            'checkResultsDate' => 'Check Results Date',
            'companyId' => 'Company ID',
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetails()
    {
        return $this->hasMany(BetDetail::class, ['companyDrawId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::class, ['id' => 'companyId']);
    }
}

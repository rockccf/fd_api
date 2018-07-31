<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "company".
 *
 * @property int $id
 * @property int $version
 * @property string $name
 * @property string $code
 * @property string $drawTime
 * @property string $stopBetTime
 * @property string $bgColor
 * @property string $fontColor
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property CompanyDraw[] $companyDraws
 */
class Company extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'company';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version'], 'integer'],
            [['name', 'code', 'drawTime', 'stopBetTime', 'bgColor', 'fontColor'], 'required'],
            [['drawTime', 'stopBetTime', 'bgColor', 'fontColor'], 'safe'],
            [['name'], 'string', 'max' => 50],
            [['code'], 'string', 'max' => 1],
            [['bgColor', 'fontColor'], 'string', 'max' => 20],
            [['code'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'name' => 'Name',
            'code' => 'Code',
            'drawTime' => 'Draw Time',
            'stopBetTime' => 'Stop Bet Time',
            'bgColor' => 'Bg Color',
            'fontColor' => 'Font Color',
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

    //For expand usage
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['companyDraws'] = function ($model) {
            return $model->companyDraws;
        };

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyDraws()
    {
        return $this->hasMany(CompanyDraw::class, ['companyId' => 'id']);
    }
}

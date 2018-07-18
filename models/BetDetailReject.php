<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bet_detail_reject".
 *
 * @property int $id
 * @property int $version
 * @property string $big
 * @property string $small
 * @property string $4a
 * @property string $4b
 * @property string $4c
 * @property string $4d
 * @property string $4e
 * @property string $4f
 * @property string $3abc
 * @property string $3a
 * @property string $3b
 * @property string $3c
 * @property string $3d
 * @property string $3e
 * @property string $5d
 * @property string $6d
 * @property string $totalReject
 * @property int $betDetailId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property BetDetail $betDetail
 */
class BetDetailReject extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bet_detail_reject';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'betDetailId'], 'integer'],
            [['big', 'small', '4a', '4b', '4c', '4d', '4e', '4f', '3abc', '3a', '3b', '3c', '3d', '3e', '5d', '6d', 'totalReject'], 'number'],
            [['totalReject', 'betDetailId'], 'required'],
            [['betDetailId'], 'exist', 'skipOnError' => true, 'targetClass' => BetDetail::class, 'targetAttribute' => ['betDetailId' => 'id']]
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
            'big' => 'Big',
            'small' => 'Small',
            '4a' => '4a',
            '4b' => '4b',
            '4c' => '4c',
            '4d' => '4d',
            '4e' => '4e',
            '4f' => '4f',
            '3abc' => '3abc',
            '3a' => '3a',
            '3b' => '3b',
            '3c' => '3c',
            '3d' => '3d',
            '3e' => '3e',
            '5d' => '5d',
            '6d' => '6d',
            'totalReject' => 'Total Reject',
            'betDetailId' => 'Bet Detail ID',
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
    public function getBetDetail()
    {
        return $this->hasOne(BetDetail::class, ['id' => 'betDetailId']);
    }
}

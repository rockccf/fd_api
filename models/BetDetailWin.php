<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bet_detail_win".
 *
 * @property int $id
 * @property int $version
 * @property string $betAmount
 * @property int $winPrizeType
 * @property string $winPrizeAmount
 * @property string $totalWin
 * @property string $superiorWinPrizeAmount
 * @property string $superiorBonus
 * @property int $betDetailId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property BetDetail $betDetail
 */
class BetDetailWin extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bet_detail_win';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'winPrizeType', 'betDetailId'], 'integer'],
            [['betAmount', 'winPrizeType', 'winPrizeAmount', 'totalWin', 'betDetailId'], 'required'],
            [['betAmount', 'winPrizeAmount', 'totalWin', 'superiorWinPrizeAmount', 'superiorBonus'], 'number'],
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
            'betAmount' => 'Bet Amount',
            'winPrizeType' => 'Win Prize Type',
            'winPrizeAmount' => 'Win Prize Amount',
            'totalWin' => 'Total Win',
            'superiorWinPrizeAmount' => 'Superior Win Prize Amount',
            'superiorBonus' => 'Superior Bonus',
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

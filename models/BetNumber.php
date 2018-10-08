<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bet_number".
 *
 * @property int $id
 * @property int $version
 * @property int $rowIndex
 * @property string $number
 * @property int $betOption
 * @property int $status
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
 * @property string $soldBig
 * @property string $soldSmall
 * @property string $sold4a
 * @property string $sold4b
 * @property string $sold4c
 * @property string $sold4d
 * @property string $sold4e
 * @property string $sold4f
 * @property string $sold3abc
 * @property string $sold3a
 * @property string $sold3b
 * @property string $sold3c
 * @property string $sold3d
 * @property string $sold3e
 * @property string $sold5d
 * @property string $sold6d
 * @property array $companyCodes
 * @property array $drawDates
 * @property string $totalBet
 * @property string $totalSales
 * @property string $totalReject
 * @property int $betId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property BetDetail[] $betDetails
 * @property Bet $bet
 */
class BetNumber extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bet_number';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'rowIndex', 'betOption', 'status', 'betId'], 'integer'],
            [['rowIndex', 'number', 'betOption', 'status', 'companyCodes', 'drawDates', 'totalBet', 'betId'], 'required'],
            [['number', 'big', 'small', '4a', '4b', '4c', '4d', '4e', '4f', '3abc', '3a', '3b', '3c', '3d', '3e', '5d', '6d', 'soldBig', 'soldSmall', 'sold4a', 'sold4b', 'sold4c', 'sold4d', 'sold4e', 'sold4f', 'sold3abc', 'sold3a', 'sold3b', 'sold3c', 'sold3d', 'sold3e', 'sold5d', 'sold6d', 'totalBet', 'totalSales', 'totalReject'], 'number'],
            [['companyCodes', 'drawDates'], 'safe'],
            [['number'], 'string', 'max' => 6],
            [['betId'], 'exist', 'skipOnError' => true, 'targetClass' => Bet::class, 'targetAttribute' => ['betId' => 'id']],
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
            'rowIndex' => 'Row Index',
            'number' => 'Number',
            'betOption' => 'Bet Option',
            'status' => 'Status',
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
            'soldBig' => 'Sold Big',
            'soldSmall' => 'Sold Small',
            'sold4a' => 'Sold4a',
            'sold4b' => 'Sold4b',
            'sold4c' => 'Sold4c',
            'sold4d' => 'Sold4d',
            'sold4e' => 'Sold4e',
            'sold4f' => 'Sold4f',
            'sold3abc' => 'Sold3abc',
            'sold3a' => 'Sold3a',
            'sold3b' => 'Sold3b',
            'sold3c' => 'Sold3c',
            'sold3d' => 'Sold3d',
            'sold3e' => 'Sold3e',
            'sold5d' => 'Sold5d',
            'sold6d' => 'Sold6d',
            'companyCodes' => 'Company Codes',
            'drawDates' => 'Draw Dates',
            'totalBet' => 'Total Bet',
            'totalSales' => 'Total Sales',
            'totalReject' => 'Total Reject',
            'betId' => 'Bet ID',
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
        return $this->hasMany(BetDetail::class, ['betNumberId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBet()
    {
        return $this->hasOne(Bet::class, ['id' => 'betId']);
    }
}

<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bet_detail".
 *
 * @property int $id
 * @property int $version
 * @property int $number
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
 * @property string $totalSales
 * @property string $totalCommission
 * @property string $totalWin
 * @property string $totalCollect
 * @property string $totalSuperiorCommission
 * @property int $status
 * @property string $voidDate
 * @property string $drawDate
 * @property int $companyDrawId
 * @property int $betId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property CompanyDraw $companyDraw
 * @property Bet $bet
 * @property BetDetailReject[] $betDetailRejects
 * @property BetDetailWin[] $betDetailWins
 */
class BetDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bet_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'number', 'status', 'companyDrawId', 'betId'], 'integer'],
            [['number', 'totalSales', 'totalCommission', 'status', 'drawDate', 'companyDrawId', 'betId'], 'required'],
            [['big', 'small', '4a', '4b', '4c', '4d', '4e', '4f', '3abc', '3a', '3b', '3c', '3d', '3e', '5d', '6d', 'totalSales', 'totalCommission', 'totalWin', 'totalCollect', 'totalSuperiorCommission'], 'number'],
            [['voidDate', 'drawDate'], 'safe'],
            [['companyDrawId'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyDraw::class, 'targetAttribute' => ['companyDrawId' => 'id']],
            [['betId'], 'exist', 'skipOnError' => true, 'targetClass' => Bet::class, 'targetAttribute' => ['betId' => 'id']]
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
            'number' => 'Number',
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
            'totalSales' => 'Total Sales',
            'totalCommission' => 'Total Commission',
            'totalWin' => 'Total Win',
            'totalCollect' => 'Total Collect',
            'totalSuperiorCommission' => 'Total Superior Commission',
            'status' => 'Status',
            'voidDate' => 'Void Date',
            'drawDate' => 'Draw Date',
            'companyDrawId' => 'Company Draw ID',
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
    public function getCompanyDraw()
    {
        return $this->hasOne(CompanyDraw::class, ['id' => 'companyDrawId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBet()
    {
        return $this->hasOne(Bet::class, ['id' => 'betId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetailRejects()
    {
        return $this->hasMany(BetDetailReject::class, ['betDetailId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetailWins()
    {
        return $this->hasMany(BetDetailWin::class, ['betDetailId' => 'id']);
    }
}

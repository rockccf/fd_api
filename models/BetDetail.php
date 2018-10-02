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
 * @property string $number
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
 * @property string $totalReject
 * @property string $ownCommission
 * @property string $extraCommission
 * @property string $totalCommission
 * @property string $totalWin
 * @property string $totalCollect
 * @property string $totalSuperiorCommission
 * @property int $status
 * @property int $won
 * @property string $voidDate
 * @property string $remarks
 * @property string $drawDate
 * @property int $companyDrawId
 * @property int $betNumberId
 * @property int $betId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property CompanyDraw $companyDraw
 * @property BetNumber $betNumber
 * @property Bet $bet
 * @property BetDetailReject $betDetailReject
 * @property BetDetailWin[] $betDetailWins
 * @property User $creator
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
            [['version', 'status', 'won', 'companyDrawId', 'betNumberId', 'betId'], 'integer'],
            [['number', 'totalSales', 'ownCommission', 'totalCommission', 'status', 'drawDate', 'companyDrawId', 'betNumberId', 'betId'], 'required'],
            [['number', 'big', 'small', '4a', '4b', '4c', '4d', '4e', '4f', '3abc', '3a', '3b', '3c', '3d', '3e', '5d', '6d', 'totalSales', 'totalReject', 'ownCommission', 'extraCommission', 'totalCommission', 'totalWin', 'totalCollect', 'totalSuperiorCommission'], 'number'],
            [['voidDate', 'drawDate'], 'safe'],
            [['number'], 'string', 'max' => 6],
            [['remarks'], 'string', 'max' => 255],
            [['companyDrawId'], 'exist', 'skipOnError' => true, 'targetClass' => CompanyDraw::class, 'targetAttribute' => ['companyDrawId' => 'id']],
            [['betNumberId'], 'exist', 'skipOnError' => true, 'targetClass' => BetNumber::class, 'targetAttribute' => ['betNumberId' => 'id']],
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
            'totalReject' => 'Total Reject',
            'ownCommission' => 'Own Commission',
            'extraCommission' => 'Extra Commission',
            'totalCommission' => 'Total Commission',
            'totalWin' => 'Total Win',
            'totalCollect' => 'Total Collect',
            'totalSuperiorCommission' => 'Total Superior Commission',
            'status' => 'Status',
            'won' => 'Won',
            'voidDate' => 'Void Date',
            'remarks' => 'Remarks',
            'drawDate' => 'Draw Date',
            'companyDrawId' => 'Company Draw ID',
            'betNumberId' => 'Bet Number ID',
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

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        $fields['totalSales'] = function ($model) {
            return floatval($model->totalSales); //Cast string to float/double type
        };

        $fields['totalReject'] = function ($model) {
            return floatval($model->totalWin); //Cast string to float/double type
        };

        $fields['totalCommission'] = function ($model) {
            return floatval($model->totalCommission); //Cast string to float/double type
        };

        $fields['totalWin'] = function ($model) {
            return floatval($model->totalWin); //Cast string to float/double type
        };

        $fields['totalCollect'] = function ($model) {
            return floatval($model->totalCollect); //Cast string to float/double type
        };

        $fields['totalSuperiorCommission'] = function ($model) {
            return floatval($model->totalSuperiorCommission); //Cast string to float/double type
        };

        $fields['betDate'] = function ($model) {
            $betDate = new \DateTime($model->createdAt);
            return $betDate->format(Yii::$app->params['FORMAT']['DATETIME']);
        };

        $fields['drawDate'] = function ($model) {
            $drawDate = new \DateTime($model->drawDate);
            return $drawDate->format(Yii::$app->params['FORMAT']['DATE']);
        };

        $fields['voidDate'] = function ($model) {
            if (!empty($model->voidDate)) {
                $voidDate = new \DateTime($model->voidDate);
                return $voidDate->format(Yii::$app->params['FORMAT']['DATETIME']);
            } else {
                return null;
            }
        };

        $fields['voidDateBy'] = function ($model) {
            $createdAt = new \DateTime($model->createdAt);
            $voidDateBy = $createdAt->add(new \DateInterval('PT'.Yii::$app->params['GLOBAL']['BET_VOID_ALLOW_MINUTES'].'M'));
            return $voidDateBy;
        };

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['companyDraw'] = function ($model) {
            return $model->companyDraw;
        };

        $extraFields['betDetailReject'] = function ($model) {
            return $model->betDetailReject;
        };

        $extraFields['betDetailWins'] = function ($model) {
            return $model->betDetailWins;
        };

        return $extraFields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompanyDraw()
    {
        return $this->hasOne(CompanyDraw::class, ['id' => 'companyDrawId']);
    }

    public function getBetNumber()
    {
        return $this->hasOne(BetNumber::class, ['id' => 'betNumberId']);
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
    public function getBetDetailReject()
    {
        return $this->hasOne(BetDetailReject::class, ['betDetailId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetailWins()
    {
        return $this->hasMany(BetDetailWin::class, ['betDetailId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'createdBy']);
    }
}

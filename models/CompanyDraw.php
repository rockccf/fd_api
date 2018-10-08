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
 * @property string $1stPrize
 * @property string $2ndPrize
 * @property string $3rdPrize
 * @property string $special1Prize
 * @property string $special2Prize
 * @property string $special3Prize
 * @property string $special4Prize
 * @property string $special5Prize
 * @property string $special6Prize
 * @property string $special7Prize
 * @property string $special8Prize
 * @property string $special9Prize
 * @property string $special10Prize
 * @property string $consolation1Prize
 * @property string $consolation2Prize
 * @property string $consolation3Prize
 * @property string $consolation4Prize
 * @property string $consolation5Prize
 * @property string $consolation6Prize
 * @property string $consolation7Prize
 * @property string $consolation8Prize
 * @property string $consolation9Prize
 * @property string $consolation10Prize
 * @property string $5d1stPrize
 * @property string $5d2ndPrize
 * @property string $5d3rdPrize
 * @property string $5d4thPrize
 * @property string $5d5thPrize
 * @property string $5d6thPrize
 * @property string $6d1stPrize
 * @property string $6d2nd1Prize
 * @property string $6d2nd2Prize
 * @property string $6d3rd1Prize
 * @property string $6d3rd2Prize
 * @property string $6d4th1Prize
 * @property string $6d4th2Prize
 * @property string $6d5th1Prize
 * @property string $6d5th2Prize
 * @property int $companyId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property BetDetail[] $betDetails
 * @property Company $company
 * @property User $createdBy0
 * @property User $updatedBy0
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
            [['1stPrize', '2ndPrize', '3rdPrize', 'special1Prize', 'special2Prize', 'special3Prize', 'special4Prize', 'special5Prize', 'special6Prize', 'special7Prize', 'special8Prize', 'special9Prize', 'special10Prize', 'consolation1Prize', 'consolation2Prize', 'consolation3Prize', 'consolation4Prize', 'consolation5Prize', 'consolation6Prize', 'consolation7Prize', 'consolation8Prize', 'consolation9Prize', 'consolation10Prize', '5d1stPrize', '5d2ndPrize', '5d3rdPrize', '5d4thPrize', '5d5thPrize', '5d6thPrize', '6d1stPrize', '6d2nd1Prize',  '6d2nd2Prize', '6d3rd1Prize', '6d3rd2Prize', '6d4th1Prize', '6d4th2Prize', '6d5th1Prize', '6d5th2Prize'], 'number'],
            [['drawDate', 'drawTime', 'stopBetTime', 'checkResultsDate'], 'safe'],
            [['1stPrize', '2ndPrize', '3rdPrize', 'special1Prize', 'special2Prize', 'special3Prize', 'special4Prize', 'special5Prize', 'special6Prize', 'special7Prize', 'special8Prize', 'special9Prize', 'special10Prize', 'consolation1Prize', 'consolation2Prize', 'consolation3Prize', 'consolation4Prize', 'consolation5Prize', 'consolation6Prize', 'consolation7Prize', 'consolation8Prize', 'consolation9Prize', 'consolation10Prize'], 'string', 'max' => 4],
            [['5d1stPrize', '5d2ndPrize', '5d3rdPrize', '5d4thPrize', '5d5thPrize', '5d6thPrize'], 'string', 'max' => 5],
            [['6d1stPrize', '6d2nd1Prize', '6d2nd2Prize', '6d3rd1Prize', '6d3rd2Prize', '6d4th1Prize', '6d4th2Prize', '6d5th1Prize', '6d5th2Prize'], 'string', 'max' => 6],
            [['companyId'], 'exist', 'skipOnError' => true, 'targetClass' => Company::class, 'targetAttribute' => ['companyId' => 'id']],
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
            '1stPrize' => '1st Prize',
            '2ndPrize' => '2nd Prize',
            '3rdPrize' => '3rd Prize',
            'special1Prize' => 'Special1 Prize',
            'special2Prize' => 'Special2 Prize',
            'special3Prize' => 'Special3 Prize',
            'special4Prize' => 'Special4 Prize',
            'special5Prize' => 'Special5 Prize',
            'special6Prize' => 'Special6 Prize',
            'special7Prize' => 'Special7 Prize',
            'special8Prize' => 'Special8 Prize',
            'special9Prize' => 'Special9 Prize',
            'special10Prize' => 'Special10 Prize',
            'consolation1Prize' => 'Consolation1 Prize',
            'consolation2Prize' => 'Consolation2 Prize',
            'consolation3Prize' => 'Consolation3 Prize',
            'consolation4Prize' => 'Consolation4 Prize',
            'consolation5Prize' => 'Consolation5 Prize',
            'consolation6Prize' => 'Consolation6 Prize',
            'consolation7Prize' => 'Consolation7 Prize',
            'consolation8Prize' => 'Consolation8 Prize',
            'consolation9Prize' => 'Consolation9 Prize',
            'consolation10Prize' => 'Consolation10 Prize',
            '5d1stPrize' => '5d1st Prize',
            '5d2ndPrize' => '5d2nd Prize',
            '5d3rdPrize' => '5d3rd Prize',
            '5d4thPrize' => '5d4th Prize',
            '5d5thPrize' => '5d5th Prize',
            '5d6thPrize' => '5d6th Prize',
            '6d1stPrize' => '6d1st Prize',
            '6d2nd1Prize' => '6d2nd1 Prize',
            '6d2nd2Prize' => '6d2nd2 Prize',
            '6d3rd1Prize' => '6d3rd1 Prize',
            '6d3rd2Prize' => '6d3rd2 Prize',
            '6d4th1Prize' => '6d4th1 Prize',
            '6d4th2Prize' => '6d4th2 Prize',
            '6d5th1Prize' => '6d5th1 Prize',
            '6d5th2Prize' => '6d5th2 Prize',
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

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        $fields['company'] = function ($model) {
            return $model->company;
        };

        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetails()
    {
        return $this->hasMany(BetDetail::className(), ['companyDrawId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCompany()
    {
        return $this->hasOne(Company::className(), ['id' => 'companyId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreatedBy0()
    {
        return $this->hasOne(User::className(), ['id' => 'createdBy']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUpdatedBy0()
    {
        return $this->hasOne(User::className(), ['id' => 'updatedBy']);
    }
}

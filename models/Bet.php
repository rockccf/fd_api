<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "bet".
 *
 * @property int $id
 * @property int $version
 * @property int $status
 * @property string $betMaxLimitBig
 * @property string $betMaxLimitSmall
 * @property string $betMaxLimit4a
 * @property string $betMaxLimit4b
 * @property string $betMaxLimit4c
 * @property string $betMaxLimit4d
 * @property string $betMaxLimit4e
 * @property string $betMaxLimit4f
 * @property string $betMaxLimit3abc
 * @property string $betMaxLimit3a
 * @property string $betMaxLimit3b
 * @property string $betMaxLimit3c
 * @property string $betMaxLimit3d
 * @property string $betMaxLimit3e
 * @property string $betMaxLimit5d
 * @property string $betMaxLimit6d
 * @property string $4dCommRate
 * @property string $6dCommRate
 * @property string $gdCommRate
 * @property string $extra4dCommRate
 * @property string $extra6dCommRate
 * @property string $extraGdCommRate
 * @property string $totalSales
 * @property string $totalCommission
 * @property string $totalWin
 * @property string $totalCollect
 * @property string $totalSuperiorCommission
 * @property int $packageId
 * @property int $masterId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property Package $package
 * @property Master $master
 * @property BetDetail[] $betDetails
 */
class Bet extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'bet';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'status', 'masterId'], 'integer'],
            [['status', 'betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dCommRate', '6dCommRate', 'gdCommRate', 'masterId'], 'required'],
            [['betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dCommRate', '6dCommRate', 'gdCommRate', 'extra4dCommRate', 'extra6dCommRate', 'extraGdCommRate', 'totalSales', 'totalCommission', 'totalWin', 'totalCollect', 'totalSuperiorCommission'], 'number'],
            [['masterId'], 'exist', 'skipOnError' => true, 'targetClass' => Master::class, 'targetAttribute' => ['masterId' => 'id']],
            [['packageId'], 'exist', 'skipOnError' => true, 'targetClass' => Package::class, 'targetAttribute' => ['packageId' => 'id']]
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
            'status' => 'Status',
            'betMaxLimitBig' => 'Bet Max Limit Big',
            'betMaxLimitSmall' => 'Bet Max Limit Small',
            'betMaxLimit4a' => 'Bet Max Limit4a',
            'betMaxLimit4b' => 'Bet Max Limit4b',
            'betMaxLimit4c' => 'Bet Max Limit4c',
            'betMaxLimit4d' => 'Bet Max Limit4d',
            'betMaxLimit4e' => 'Bet Max Limit4e',
            'betMaxLimit4f' => 'Bet Max Limit4f',
            'betMaxLimit3abc' => 'Bet Max Limit3abc',
            'betMaxLimit3a' => 'Bet Max Limit3a',
            'betMaxLimit3b' => 'Bet Max Limit3b',
            'betMaxLimit3c' => 'Bet Max Limit3c',
            'betMaxLimit3d' => 'Bet Max Limit3d',
            'betMaxLimit3e' => 'Bet Max Limit3e',
            'betMaxLimit5d' => 'Bet Max Limit5d',
            'betMaxLimit6d' => 'Bet Max Limit6d',
            '4dCommRate' => '4d Comm Rate',
            '6dCommRate' => '6d Comm Rate',
            'gdCommRate' => 'Gd Comm Rate',
            'extra4dCommRate' => 'Extra4d Comm Rate',
            'extra6dCommRate' => 'Extra6d Comm Rate',
            'extraGdCommRate' => 'Extra Gd Comm Rate',
            'totalSales' => 'Total Sales',
            'totalCommission' => 'Total Commission',
            'totalWin' => 'Total Win',
            'totalCollect' => 'Total Collect',
            'totalSuperiorCommission' => 'Total Superior Commission',
            'masterId' => 'Master ID',
            'packageId' => 'Package ID',
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
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'packageId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMaster()
    {
        return $this->hasOne(Master::class, ['id' => 'masterId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetails()
    {
        return $this->hasMany(BetDetail::class, ['betId' => 'id']);
    }
}

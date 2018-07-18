<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "master".
 *
 * @property int $id
 * @property int $version
 * @property string $prefix
 * @property string $name
 * @property int $active
 * @property int $locked
 * @property int $voidBetMinutes
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
 * @property string $remarks
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property Bet[] $bets
 * @property Package[] $packages
 * @property User[] $users
 */
class Master extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'master';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'active', 'locked', 'voidBetMinutes'], 'integer'],
            [['prefix', 'name', 'active', 'voidBetMinutes', 'betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d'], 'required'],
            [['betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d'], 'number'],
            [['remarks'], 'string'],
            [['prefix'], 'string', 'max' => 3],
            [['name'], 'string', 'max' => 255],
            [['prefix'], 'unique']
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
            'prefix' => 'Prefix',
            'name' => 'Name',
            'active' => 'Active',
            'locked' => 'Locked',
            'voidBetMinutes' => 'Void Bet Minutes',
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
            'remarks' => 'Remarks',
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

        $fields['betMaxLimitBig'] = function ($model) {
            return floatval($model->betMaxLimitBig); //Cast string to float/double type
        };

        $fields['betMaxLimitSmall'] = function ($model) {
            return floatval($model->betMaxLimitSmall); //Cast string to float/double type
        };

        $fields['betMaxLimit4a'] = function ($model) {
            return floatval($model->betMaxLimit4a); //Cast string to float/double type
        };

        $fields['betMaxLimit4b'] = function ($model) {
            return floatval($model->betMaxLimit4b); //Cast string to float/double type
        };

        $fields['betMaxLimit4c'] = function ($model) {
            return floatval($model->betMaxLimit4c); //Cast string to float/double type
        };

        $fields['betMaxLimit4d'] = function ($model) {
            return floatval($model->betMaxLimit4d); //Cast string to float/double type
        };

        $fields['betMaxLimit4e'] = function ($model) {
            return floatval($model->betMaxLimit4e); //Cast string to float/double type
        };

        $fields['betMaxLimit4f'] = function ($model) {
            return floatval($model->betMaxLimit4f); //Cast string to float/double type
        };

        $fields['betMaxLimit3abc'] = function ($model) {
            return floatval($model->betMaxLimit3abc); //Cast string to float/double type
        };

        $fields['betMaxLimit3a'] = function ($model) {
            return floatval($model->betMaxLimit3a); //Cast string to float/double type
        };

        $fields['betMaxLimit3b'] = function ($model) {
            return floatval($model->betMaxLimit3b); //Cast string to float/double type
        };

        $fields['betMaxLimit3c'] = function ($model) {
            return floatval($model->betMaxLimit3c); //Cast string to float/double type
        };

        $fields['betMaxLimit3d'] = function ($model) {
            return floatval($model->betMaxLimit3d); //Cast string to float/double type
        };

        $fields['betMaxLimit3e'] = function ($model) {
            return floatval($model->betMaxLimit3e); //Cast string to float/double type
        };

        $fields['betMaxLimit5d'] = function ($model) {
            return floatval($model->betMaxLimit5d); //Cast string to float/double type
        };

        $fields['betMaxLimit6d'] = function ($model) {
            return floatval($model->betMaxLimit6d); //Cast string to float/double type
        };

        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBets()
    {
        return $this->hasMany(Bet::class, ['masterId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackages()
    {
        return $this->hasMany(Package::class, ['masterId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['masterId' => 'id']);
    }
}

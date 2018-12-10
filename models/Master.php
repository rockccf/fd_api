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
 * @property string $commissionRate
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
 * @property string $4dBigPrize1
 * @property string $4dBigPrize2
 * @property string $4dBigPrize3
 * @property string $4dBigStarters
 * @property string $4dBigConsolation
 * @property string $4dSmallPrize1
 * @property string $4dSmallPrize2
 * @property string $4dSmallPrize3
 * @property string $4d4aPrize
 * @property string $4d4bPrize
 * @property string $4d4cPrize
 * @property string $4d4dPrize
 * @property string $4d4ePrize
 * @property string $4d4fPrize
 * @property string $3dAbcPrize1
 * @property string $3dAbcPrize2
 * @property string $3dAbcPrize3
 * @property string $3d3aPrize
 * @property string $3d3bPrize
 * @property string $3d3cPrize
 * @property string $3d3dPrize
 * @property string $3d3ePrize
 * @property string $gd4dBigPrize1
 * @property string $gd4dBigPrize2
 * @property string $gd4dBigPrize3
 * @property string $gd4dBigStarters
 * @property string $gd4dBigConsolation
 * @property string $gd4dSmallPrize1
 * @property string $gd4dSmallPrize2
 * @property string $gd4dSmallPrize3
 * @property string $gd4d4aPrize
 * @property string $gd4d4bPrize
 * @property string $gd4d4cPrize
 * @property string $gd4d4dPrize
 * @property string $gd4d4ePrize
 * @property string $gd4d4fPrize
 * @property string $gd3dAbcPrize1
 * @property string $gd3dAbcPrize2
 * @property string $gd3dAbcPrize3
 * @property string $gd3d3aPrize
 * @property string $gd3d3bPrize
 * @property string $gd3d3cPrize
 * @property string $gd3d3dPrize
 * @property string $gd3d3ePrize
 * @property string $5dPrize1
 * @property string $5dPrize2
 * @property string $5dPrize3
 * @property string $5dPrize4
 * @property string $5dPrize5
 * @property string $5dPrize6
 * @property string $6dPrize1
 * @property string $6dPrize2
 * @property string $6dPrize3
 * @property string $6dPrize4
 * @property string $6dPrize5
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
            [['prefix', 'commissionRate', 'name', 'active', 'voidBetMinutes', 'betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '4d4bPrize', '4d4cPrize', '4d4dPrize', '4d4ePrize', '4d4fPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize', '3d3bPrize', '3d3cPrize', '3d3dPrize', '3d3ePrize', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd4d4bPrize', 'gd4d4cPrize', 'gd4d4dPrize', 'gd4d4ePrize', 'gd4d4fPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize', 'gd3d3bPrize', 'gd3d3cPrize', 'gd3d3dPrize', 'gd3d3ePrize', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5'], 'required'],
            [['commissionRate', 'betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '4d4bPrize', '4d4cPrize', '4d4dPrize', '4d4ePrize', '4d4fPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize', '3d3bPrize', '3d3cPrize', '3d3dPrize', '3d3ePrize', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd4d4bPrize', 'gd4d4cPrize', 'gd4d4dPrize', 'gd4d4ePrize', 'gd4d4fPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize', 'gd3d3bPrize', 'gd3d3cPrize', 'gd3d3dPrize', 'gd3d3ePrize', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5'], 'number'],
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
            'commissionRate' => 'Commission Rate',
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
            '4dBigPrize1' => '4d Big Prize1',
            '4dBigPrize2' => '4d Big Prize2',
            '4dBigPrize3' => '4d Big Prize3',
            '4dBigStarters' => '4d Big Starters',
            '4dBigConsolation' => '4d Big Consolation',
            '4dSmallPrize1' => '4d Small Prize1',
            '4dSmallPrize2' => '4d Small Prize2',
            '4dSmallPrize3' => '4d Small Prize3',
            '4d4aPrize' => '4d4a Prize',
            '4d4bPrize' => '4d4b Prize',
            '4d4cPrize' => '4d4c Prize',
            '4d4dPrize' => '4d4d Prize',
            '4d4ePrize' => '4d4e Prize',
            '4d4fPrize' => '4d4f Prize',
            '3dAbcPrize1' => '3d Abc Prize1',
            '3dAbcPrize2' => '3d Abc Prize2',
            '3dAbcPrize3' => '3d Abc Prize3',
            '3d3aPrize' => '3d3a Prize',
            '3d3bPrize' => '3d3b Prize',
            '3d3cPrize' => '3d3c Prize',
            '3d3dPrize' => '3d3d Prize',
            '3d3ePrize' => '3d3e Prize',
            'gd4dBigPrize1' => 'Gd4d Big Prize1',
            'gd4dBigPrize2' => 'Gd4d Big Prize2',
            'gd4dBigPrize3' => 'Gd4d Big Prize3',
            'gd4dBigStarters' => 'Gd4d Big Starters',
            'gd4dBigConsolation' => 'Gd4d Big Consolation',
            'gd4dSmallPrize1' => 'Gd4d Small Prize1',
            'gd4dSmallPrize2' => 'Gd4d Small Prize2',
            'gd4dSmallPrize3' => 'Gd4d Small Prize3',
            'gd4d4aPrize' => 'Gd4d4a Prize',
            'gd4d4bPrize' => 'Gd4d4b Prize',
            'gd4d4cPrize' => 'Gd4d4c Prize',
            'gd4d4dPrize' => 'Gd4d4d Prize',
            'gd4d4ePrize' => 'Gd4d4e Prize',
            'gd4d4fPrize' => 'Gd4d4f Prize',
            'gd3dAbcPrize1' => 'Gd3d Abc Prize1',
            'gd3dAbcPrize2' => 'Gd3d Abc Prize2',
            'gd3dAbcPrize3' => 'Gd3d Abc Prize3',
            'gd3d3aPrize' => 'Gd3d3a Prize',
            'gd3d3bPrize' => 'Gd3d3b Prize',
            'gd3d3cPrize' => 'Gd3d3c Prize',
            'gd3d3dPrize' => 'Gd3d3d Prize',
            'gd3d3ePrize' => 'Gd3d3e Prize',
            '5dPrize1' => '5d Prize1',
            '5dPrize2' => '5d Prize2',
            '5dPrize3' => '5d Prize3',
            '5dPrize4' => '5d Prize4',
            '5dPrize5' => '5d Prize5',
            '5dPrize6' => '5d Prize6',
            '6dPrize1' => '6d Prize1',
            '6dPrize2' => '6d Prize2',
            '6dPrize3' => '6d Prize3',
            '6dPrize4' => '6d Prize4',
            '6dPrize5' => '6d Prize5',
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

        $fields['commissionRate'] = function ($model) {
            return floatval($model->commissionRate); //Cast string to float/double type
        };

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

        $fields['4dBigPrize1'] = function ($model) {
            return floatval($model->{'4dBigPrize1'}); //Cast string to float/double type
        };

        $fields['4dBigPrize2'] = function ($model) {
            return floatval($model->{'4dBigPrize2'}); //Cast string to float/double type
        };

        $fields['4dBigPrize3'] = function ($model) {
            return floatval($model->{'4dBigPrize3'}); //Cast string to float/double type
        };

        $fields['4dBigStarters'] = function ($model) {
            return floatval($model->{'4dBigStarters'}); //Cast string to float/double type
        };

        $fields['4dBigConsolation'] = function ($model) {
            return floatval($model->{'4dBigConsolation'}); //Cast string to float/double type
        };

        $fields['4dSmallPrize1'] = function ($model) {
            return floatval($model->{'4dSmallPrize1'}); //Cast string to float/double type
        };

        $fields['4dSmallPrize2'] = function ($model) {
            return floatval($model->{'4dSmallPrize2'}); //Cast string to float/double type
        };

        $fields['4dSmallPrize3'] = function ($model) {
            return floatval($model->{'4dSmallPrize3'}); //Cast string to float/double type
        };

        $fields['4d4aPrize'] = function ($model) {
            return floatval($model->{'4d4aPrize'}); //Cast string to float/double type
        };

        $fields['4d4bPrize'] = function ($model) {
            return floatval($model->{'4d4bPrize'}); //Cast string to float/double type
        };

        $fields['4d4cPrize'] = function ($model) {
            return floatval($model->{'4d4cPrize'}); //Cast string to float/double type
        };

        $fields['4d4dPrize'] = function ($model) {
            return floatval($model->{'4d4dPrize'}); //Cast string to float/double type
        };

        $fields['4d4ePrize'] = function ($model) {
            return floatval($model->{'4d4ePrize'}); //Cast string to float/double type
        };

        $fields['4d4fPrize'] = function ($model) {
            return floatval($model->{'4d4fPrize'}); //Cast string to float/double type
        };

        $fields['3dAbcPrize1'] = function ($model) {
            return floatval($model->{'3dAbcPrize1'}); //Cast string to float/double type
        };

        $fields['3dAbcPrize2'] = function ($model) {
            return floatval($model->{'3dAbcPrize2'}); //Cast string to float/double type
        };

        $fields['3dAbcPrize3'] = function ($model) {
            return floatval($model->{'3dAbcPrize3'}); //Cast string to float/double type
        };

        $fields['3d3aPrize'] = function ($model) {
            return floatval($model->{'3d3aPrize'}); //Cast string to float/double type
        };

        $fields['3d3bPrize'] = function ($model) {
            return floatval($model->{'3d3bPrize'}); //Cast string to float/double type
        };

        $fields['3d3cPrize'] = function ($model) {
            return floatval($model->{'3d3cPrize'}); //Cast string to float/double type
        };

        $fields['3d3dPrize'] = function ($model) {
            return floatval($model->{'3d3dPrize'}); //Cast string to float/double type
        };

        $fields['3d3ePrize'] = function ($model) {
            return floatval($model->{'3d3ePrize'}); //Cast string to float/double type
        };

        $fields['gd4dBigPrize1'] = function ($model) {
            return floatval($model->{'gd4dBigPrize1'}); //Cast string to float/double type
        };

        $fields['gd4dBigPrize2'] = function ($model) {
            return floatval($model->{'gd4dBigPrize2'}); //Cast string to float/double type
        };

        $fields['gd4dBigPrize3'] = function ($model) {
            return floatval($model->{'gd4dBigPrize3'}); //Cast string to float/double type
        };

        $fields['gd4dBigStarters'] = function ($model) {
            return floatval($model->{'gd4dBigStarters'}); //Cast string to float/double type
        };

        $fields['gd4dBigConsolation'] = function ($model) {
            return floatval($model->{'gd4dBigConsolation'}); //Cast string to float/double type
        };

        $fields['gd4dSmallPrize1'] = function ($model) {
            return floatval($model->{'gd4dSmallPrize1'}); //Cast string to float/double type
        };

        $fields['gd4dSmallPrize2'] = function ($model) {
            return floatval($model->{'gd4dSmallPrize2'}); //Cast string to float/double type
        };

        $fields['gd4dSmallPrize3'] = function ($model) {
            return floatval($model->{'gd4dSmallPrize3'}); //Cast string to float/double type
        };

        $fields['gd4d4aPrize'] = function ($model) {
            return floatval($model->{'gd4d4aPrize'}); //Cast string to float/double type
        };

        $fields['gd4d4bPrize'] = function ($model) {
            return floatval($model->{'gd4d4bPrize'}); //Cast string to float/double type
        };

        $fields['gd4d4cPrize'] = function ($model) {
            return floatval($model->{'gd4d4cPrize'}); //Cast string to float/double type
        };

        $fields['gd4d4dPrize'] = function ($model) {
            return floatval($model->{'gd4d4dPrize'}); //Cast string to float/double type
        };

        $fields['gd4d4ePrize'] = function ($model) {
            return floatval($model->{'gd4d4ePrize'}); //Cast string to float/double type
        };

        $fields['gd4d4fPrize'] = function ($model) {
            return floatval($model->{'gd4d4fPrize'}); //Cast string to float/double type
        };

        $fields['gd3dAbcPrize1'] = function ($model) {
            return floatval($model->{'gd3dAbcPrize1'}); //Cast string to float/double type
        };

        $fields['gd3dAbcPrize2'] = function ($model) {
            return floatval($model->{'gd3dAbcPrize2'}); //Cast string to float/double type
        };

        $fields['gd3dAbcPrize3'] = function ($model) {
            return floatval($model->{'gd3dAbcPrize3'}); //Cast string to float/double type
        };

        $fields['gd3d3aPrize'] = function ($model) {
            return floatval($model->{'gd3d3aPrize'}); //Cast string to float/double type
        };

        $fields['gd3d3bPrize'] = function ($model) {
            return floatval($model->{'gd3d3bPrize'}); //Cast string to float/double type
        };

        $fields['gd3d3cPrize'] = function ($model) {
            return floatval($model->{'gd3d3cPrize'}); //Cast string to float/double type
        };

        $fields['gd3d3dPrize'] = function ($model) {
            return floatval($model->{'gd3d3dPrize'}); //Cast string to float/double type
        };

        $fields['gd3d3ePrize'] = function ($model) {
            return floatval($model->{'gd3d3ePrize'}); //Cast string to float/double type
        };

        $fields['5dPrize1'] = function ($model) {
            return floatval($model->{'5dPrize1'}); //Cast string to float/double type
        };

        $fields['5dPrize2'] = function ($model) {
            return floatval($model->{'5dPrize2'}); //Cast string to float/double type
        };

        $fields['5dPrize3'] = function ($model) {
            return floatval($model->{'5dPrize3'}); //Cast string to float/double type
        };

        $fields['5dPrize4'] = function ($model) {
            return floatval($model->{'5dPrize4'}); //Cast string to float/double type
        };

        $fields['5dPrize5'] = function ($model) {
            return floatval($model->{'5dPrize5'}); //Cast string to float/double type
        };

        $fields['5dPrize6'] = function ($model) {
            return floatval($model->{'5dPrize6'}); //Cast string to float/double type
        };

        $fields['6dPrize1'] = function ($model) {
            return floatval($model->{'6dPrize1'}); //Cast string to float/double type
        };

        $fields['6dPrize2'] = function ($model) {
            return floatval($model->{'6dPrize2'}); //Cast string to float/double type
        };

        $fields['6dPrize3'] = function ($model) {
            return floatval($model->{'6dPrize3'}); //Cast string to float/double type
        };

        $fields['6dPrize4'] = function ($model) {
            return floatval($model->{'6dPrize4'}); //Cast string to float/double type
        };

        $fields['6dPrize5'] = function ($model) {
            return floatval($model->{'6dPrize5'}); //Cast string to float/double type
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

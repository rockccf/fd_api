<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "package".
 *
 * @property int $id
 * @property int $version
 * @property string $name
 * @property string $description
 * @property string $4dAgentCommRate
 * @property string $4dPlayerCommRate
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
 * @property string $gdAgentCommRate
 * @property string $gdPlayerCommRate
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
 * @property string $6dAgentCommRate
 * @property string $6dPlayerCommRate
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
 * @property int $masterId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property Master $master
 * @property UserDetail[] $userDetails
 */
class Package extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'package';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'masterId'], 'integer'],
            [['name', '4dAgentCommRate', '4dPlayerCommRate', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '4d4bPrize', '4d4cPrize', '4d4dPrize', '4d4ePrize', '4d4fPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize', '3d3bPrize', '3d3cPrize', '3d3dPrize', '3d3ePrize', 'gdAgentCommRate', 'gdPlayerCommRate', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd4d4bPrize', 'gd4d4cPrize', 'gd4d4dPrize', 'gd4d4ePrize', 'gd4d4fPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize', 'gd3d3bPrize', 'gd3d3cPrize', 'gd3d3dPrize', 'gd3d3ePrize', '6dAgentCommRate', '6dPlayerCommRate', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5', 'masterId'], 'required'],
            [['4dAgentCommRate', '4dPlayerCommRate', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '4d4bPrize', '4d4cPrize', '4d4dPrize', '4d4ePrize', '4d4fPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize', '3d3bPrize', '3d3cPrize', '3d3dPrize', '3d3ePrize', 'gdAgentCommRate', 'gdPlayerCommRate', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd4d4bPrize', 'gd4d4cPrize', 'gd4d4dPrize', 'gd4d4ePrize', 'gd4d4fPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize', 'gd3d3bPrize', 'gd3d3cPrize', 'gd3d3dPrize', 'gd3d3ePrize', '6dAgentCommRate', '6dPlayerCommRate', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5'], 'number'],
            [['name'], 'string', 'max' => 50],
            [['description'], 'string', 'max' => 255],
            [['masterId'], 'exist', 'skipOnError' => true, 'targetClass' => Master::className(), 'targetAttribute' => ['masterId' => 'id']]
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
            'name' => 'Name',
            'description' => 'Description',
            '4dAgentCommRate' => '4d Agent Comm Rate',
            '4dPlayerCommRate' => '4d Player Comm Rate',
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
            'gdAgentCommRate' => 'Gd Agent Comm Rate',
            'gdPlayerCommRate' => 'Gd Player Comm Rate',
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
            '6dAgentCommRate' => '6d Agent Comm Rate',
            '6dPlayerCommRate' => '6d Player Comm Rate',
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
            'masterId' => 'Master ID',
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

        $fields['4dAgentCommRate'] = function ($model) {
            return floatval($model->{'4dAgentCommRate'}); //Cast string to float/double type
        };

        $fields['4dPlayerCommRate'] = function ($model) {
            return floatval($model->{'4dPlayerCommRate'}); //Cast string to float/double type
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

        $fields['gdAgentCommRate'] = function ($model) {
            return floatval($model->{'gdAgentCommRate'}); //Cast string to float/double type
        };

        $fields['gdPlayerCommRate'] = function ($model) {
            return floatval($model->{'gdPlayerCommRate'}); //Cast string to float/double type
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

        $fields['6dAgentCommRate'] = function ($model) {
            return floatval($model->{'6dAgentCommRate'}); //Cast string to float/double type
        };

        $fields['6dPlayerCommRate'] = function ($model) {
            return floatval($model->{'6dPlayerCommRate'}); //Cast string to float/double type
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
    public function getMaster()
    {
        return $this->hasOne(Master::class, ['id' => 'masterId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserDetails()
    {
        return $this->hasMany(UserDetail::class, ['packageId' => 'id']);
    }
}

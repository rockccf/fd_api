<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "user_detail".
 *
 * @property int $id
 * @property int $version
 * @property int $userId
 * @property int $packageId
 * @property string $creditLimit
 * @property string $creditAvailable
 * @property string $extra4dCommRate
 * @property string $extra6dCommRate
 * @property string $extraGdCommRate
 * @property int $betMethod
 * @property int $autoTransfer
 * @property int $autoTransferMode
 * @property array $autoTransferDays
 * @property int $betGdLotto
 * @property int $bet6d
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
 * @property string $3d3aPrize3
 * @property string $3d3bPrize3
 * @property string $3d3cPrize3
 * @property string $3d3dPrize3
 * @property string $3d3ePrize3
 * @property string $gd4dBigPrize1
 * @property string $gd4dBigPrize2
 * @property string $gd4dBigPrize3
 * @property string $gd4dBigStarters
 * @property string $gd4dBigConsolation
 * @property string $gd4dSmallPrize1
 * @property string $gd4dSmallPrize2
 * @property string $gd4dSmallPrize3
 * @property string $gd4d4aPrize
 * @property string $gd3dAbcPrize1
 * @property string $gd3dAbcPrize2
 * @property string $gd3dAbcPrize3
 * @property string $gd3d3aPrize3
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
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property User $user
 * @property Package $package
 */
class UserDetail extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'user_detail';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['version', 'userId', 'packageId', 'betMethod', 'autoTransfer', 'autoTransferMode', 'betGdLotto', 'bet6d'], 'integer'],
            [['userId', 'packageId', 'creditLimit', 'creditAvailable', 'betMethod'], 'required'],
            [['creditLimit', 'creditAvailable', 'extra4dCommRate', 'extra6dCommRate', 'extraGdCommRate', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize3', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize3', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5'], 'number'],
            [['autoTransferDays'], 'safe'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'version' => 'Version',
            'userId' => 'User ID',
            'packageId' => 'Package ID',
            'creditLimit' => 'Credit Limit',
            'creditAvailable' => 'Credit Available',
            'extra4dCommRate' => 'Extra4d Comm Rate',
            'extra6dCommRate' => 'Extra6d Comm Rate',
            'extraGdCommRate' => 'Extra Gd Comm Rate',
            'betMethod' => 'Bet Method',
            'autoTransfer' => 'Auto Transfer',
            'autoTransferMode' => 'Auto Transfer Mode',
            'autoTransferDays' => 'Auto Transfer Days',
            'betGdLotto' => 'Bet Gd Lotto',
            'bet6d' => 'Bet6d',
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
            '3d3aPrize3' => '3d3a Prize3',
            '3d3bPrize3' => '3d3b Prize3',
            '3d3cPrize3' => '3d3c Prize3',
            '3d3dPrize3' => '3d3d Prize3',
            '3d3ePrize3' => '3d3e Prize3',
            'gd4dBigPrize1' => 'Gd4d Big Prize1',
            'gd4dBigPrize2' => 'Gd4d Big Prize2',
            'gd4dBigPrize3' => 'Gd4d Big Prize3',
            'gd4dBigStarters' => 'Gd4d Big Starters',
            'gd4dBigConsolation' => 'Gd4d Big Consolation',
            'gd4dSmallPrize1' => 'Gd4d Small Prize1',
            'gd4dSmallPrize2' => 'Gd4d Small Prize2',
            'gd4dSmallPrize3' => 'Gd4d Small Prize3',
            'gd4d4aPrize' => 'Gd4d4a Prize',
            'gd3dAbcPrize1' => 'Gd3d Abc Prize1',
            'gd3dAbcPrize2' => 'Gd3d Abc Prize2',
            'gd3dAbcPrize3' => 'Gd3d Abc Prize3',
            'gd3d3aPrize3' => 'Gd3d3a Prize3',
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
    public function getUser()
    {
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPackage()
    {
        return $this->hasOne(Package::class, ['id' => 'packageId']);
    }
}

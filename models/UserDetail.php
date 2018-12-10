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
 * @property string $creditGranted
 * @property string $balance
 * @property string $outstandingBet
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
            [['userId', 'packageId', 'creditLimit', 'betMethod'], 'required'],
            [['creditLimit', 'creditGranted', 'balance', 'outstandingBet', 'extra4dCommRate', 'extra6dCommRate', 'extraGdCommRate', '4dBigPrize1', '4dBigPrize2', '4dBigPrize3', '4dBigStarters', '4dBigConsolation', '4dSmallPrize1', '4dSmallPrize2', '4dSmallPrize3', '4d4aPrize', '4d4bPrize', '4d4cPrize', '4d4dPrize', '4d4ePrize', '4d4fPrize', '3dAbcPrize1', '3dAbcPrize2', '3dAbcPrize3', '3d3aPrize', '3d3bPrize', '3d3cPrize', '3d3dPrize', '3d3ePrize', 'gd4dBigPrize1', 'gd4dBigPrize2', 'gd4dBigPrize3', 'gd4dBigStarters', 'gd4dBigConsolation', 'gd4dSmallPrize1', 'gd4dSmallPrize2', 'gd4dSmallPrize3', 'gd4d4aPrize', 'gd4d4bPrize', 'gd4d4cPrize', 'gd4d4dPrize', 'gd4d4ePrize', 'gd4d4fPrize', 'gd3dAbcPrize1', 'gd3dAbcPrize2', 'gd3dAbcPrize3', 'gd3d3aPrize', 'gd3d3bPrize', 'gd3d3cPrize', 'gd3d3dPrize', 'gd3d3ePrize', '5dPrize1', '5dPrize2', '5dPrize3', '5dPrize4', '5dPrize5', '5dPrize6', '6dPrize1', '6dPrize2', '6dPrize3', '6dPrize4', '6dPrize5'], 'number'],
            [['autoTransferDays'], 'safe'],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
            [['packageId'], 'exist', 'skipOnError' => true, 'targetClass' => Package::class, 'targetAttribute' => ['packageId' => 'id']],
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
            'userId' => 'User ID',
            'packageId' => 'Package ID',
            'creditLimit' => 'Credit Limit',
            'creditGranted' => 'Credit Granted',
            'balance' => 'Balance',
            'outstandingBet' => 'Outstanding Bet',
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

        $fields['creditLimit'] = function ($model) {
            return floatval($model->creditLimit); //Cast string to float/double type
        };

        $fields['creditGranted'] = function ($model) {
            return floatval($model->creditGranted); //Cast string to float/double type
        };

        //Credit available for agent to grant to downline
        $fields['creditLimitAvailableToGrant'] = function ($model) {
            $result = 0;
            if ($model->user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                $result = $model->creditLimit - $model->creditGranted;
            }
            return floatval($result); //Cast string to float/double type
        };

        $fields['balance'] = function ($model) {
            return floatval($model->balance); //Cast string to float/double type
        };

        $fields['outstandingBet'] = function ($model) {
            return floatval($model->outstandingBet); //Cast string to float/double type
        };

        $fields['outstandingBetDownline'] = function ($model) {
            $result = 0;
            if ($model->user->userType == Yii::$app->params['USER']['TYPE']['AGENT']) {
                //Get the downline players outstanding
                $players = $model->user->players;
                foreach ($players as $player) {
                    $result += $player->userDetail->outstandingBet;
                }
            }
            return floatval($result); //Cast string to float/double type
        };

        $fields['extra4dCommRate'] = function ($model) {
            return floatval($model->extra4dCommRate); //Cast string to float/double type
        };

        $fields['extra6dCommRate'] = function ($model) {
            return floatval($model->extra6dCommRate); //Cast string to float/double type
        };

        $fields['extraGdCommRate'] = function ($model) {
            return floatval($model->extraGdCommRate); //Cast string to float/double type
        };

        $fields['4dBigPrize1'] = function ($model) {
            return $model->{'4dBigPrize1'} ? floatval($model->{'4dBigPrize1'}) : null; //Cast string to float/double type
        };

        $fields['4dBigPrize2'] = function ($model) {
            return $model->{'4dBigPrize2'} ? floatval($model->{'4dBigPrize2'}) : null; //Cast string to float/double type
        };

        $fields['4dBigPrize3'] = function ($model) {
            return $model->{'4dBigPrize3'} ? floatval($model->{'4dBigPrize3'}) : null; //Cast string to float/double type
        };

        $fields['4dBigStarters'] = function ($model) {
            return $model->{'4dBigStarters'} ? floatval($model->{'4dBigStarters'}) : null; //Cast string to float/double type
        };

        $fields['4dBigConsolation'] = function ($model) {
            return $model->{'4dBigConsolation'} ? floatval($model->{'4dBigConsolation'}) : null; //Cast string to float/double type
        };

        $fields['4dSmallPrize1'] = function ($model) {
            return $model->{'4dSmallPrize1'} ? floatval($model->{'4dSmallPrize1'}) : null; //Cast string to float/double type
        };

        $fields['4dSmallPrize2'] = function ($model) {
            return $model->{'4dSmallPrize2'} ? floatval($model->{'4dSmallPrize2'}) : null; //Cast string to float/double type
        };

        $fields['4dSmallPrize3'] = function ($model) {
            return $model->{'4dSmallPrize3'} ? floatval($model->{'4dSmallPrize3'}) : null; //Cast string to float/double type
        };

        $fields['4d4aPrize'] = function ($model) {
            return $model->{'4d4aPrize'} ? floatval($model->{'4d4aPrize'}) : null; //Cast string to float/double type
        };

        $fields['4d4bPrize'] = function ($model) {
            return $model->{'4d4bPrize'} ? floatval($model->{'4d4bPrize'}) : null; //Cast string to float/double type
        };

        $fields['4d4cPrize'] = function ($model) {
            return $model->{'4d4cPrize'} ? floatval($model->{'4d4cPrize'}) : null; //Cast string to float/double type
        };

        $fields['4d4dPrize'] = function ($model) {
            return $model->{'4d4dPrize'} ? floatval($model->{'4d4dPrize'}) : null; //Cast string to float/double type
        };

        $fields['4d4ePrize'] = function ($model) {
            return $model->{'4d4ePrize'} ? floatval($model->{'4d4ePrize'}) : null; //Cast string to float/double type
        };

        $fields['4d4fPrize'] = function ($model) {
            return $model->{'4d4fPrize'} ? floatval($model->{'4d4fPrize'}) : null; //Cast string to float/double type
        };

        $fields['3dAbcPrize1'] = function ($model) {
            return $model->{'3dAbcPrize1'} ? floatval($model->{'3dAbcPrize1'}) : null; //Cast string to float/double type
        };

        $fields['3dAbcPrize2'] = function ($model) {
            return $model->{'3dAbcPrize2'} ? floatval($model->{'3dAbcPrize2'}) : null; //Cast string to float/double type
        };

        $fields['3dAbcPrize3'] = function ($model) {
            return $model->{'3dAbcPrize3'} ? floatval($model->{'3dAbcPrize3'}) : null; //Cast string to float/double type
        };

        $fields['3d3aPrize'] = function ($model) {
            return $model->{'3d3aPrize'} ? floatval($model->{'3d3aPrize'}) : null; //Cast string to float/double type
        };

        $fields['3d3bPrize'] = function ($model) {
            return $model->{'3d3bPrize'} ? floatval($model->{'3d3bPrize'}) : null; //Cast string to float/double type
        };

        $fields['3d3cPrize'] = function ($model) {
            return $model->{'3d3cPrize'} ? floatval($model->{'3d3cPrize'}) : null; //Cast string to float/double type
        };

        $fields['3d3dPrize'] = function ($model) {
            return $model->{'3d3dPrize'} ? floatval($model->{'3d3dPrize'}) : null; //Cast string to float/double type
        };

        $fields['3d3ePrize'] = function ($model) {
            return $model->{'3d3ePrize'} ? floatval($model->{'3d3ePrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4dBigPrize1'] = function ($model) {
            return $model->{'gd4dBigPrize1'} ? floatval($model->{'gd4dBigPrize1'}) : null; //Cast string to float/double type
        };

        $fields['gd4dBigPrize2'] = function ($model) {
            return $model->{'gd4dBigPrize2'} ? floatval($model->{'gd4dBigPrize2'}) : null; //Cast string to float/double type
        };

        $fields['gd4dBigPrize3'] = function ($model) {
            return $model->{'gd4dBigPrize3'} ? floatval($model->{'gd4dBigPrize3'}) : null; //Cast string to float/double type
        };

        $fields['gd4dBigStarters'] = function ($model) {
            return $model->{'gd4dBigStarters'} ? floatval($model->{'gd4dBigStarters'}) : null; //Cast string to float/double type
        };

        $fields['gd4dBigConsolation'] = function ($model) {
            return $model->{'gd4dBigConsolation'} ? floatval($model->{'gd4dBigConsolation'}) : null; //Cast string to float/double type
        };

        $fields['gd4dSmallPrize1'] = function ($model) {
            return $model->{'gd4dSmallPrize1'} ? floatval($model->{'gd4dSmallPrize1'}) : null; //Cast string to float/double type
        };

        $fields['gd4dSmallPrize2'] = function ($model) {
            return $model->{'gd4dSmallPrize2'} ? floatval($model->{'gd4dSmallPrize2'}) : null; //Cast string to float/double type
        };

        $fields['gd4dSmallPrize3'] = function ($model) {
            return $model->{'gd4dSmallPrize3'} ? floatval($model->{'gd4dSmallPrize3'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4aPrize'] = function ($model) {
            return $model->{'gd4d4aPrize'} ? floatval($model->{'gd4d4aPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4bPrize'] = function ($model) {
            return $model->{'gd4d4bPrize'} ? floatval($model->{'gd4d4bPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4cPrize'] = function ($model) {
            return $model->{'gd4d4cPrize'} ? floatval($model->{'gd4d4cPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4dPrize'] = function ($model) {
            return $model->{'gd4d4dPrize'} ? floatval($model->{'gd4d4dPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4ePrize'] = function ($model) {
            return $model->{'gd4d4ePrize'} ? floatval($model->{'gd4d4ePrize'}) : null; //Cast string to float/double type
        };

        $fields['gd4d4fPrize'] = function ($model) {
            return $model->{'gd4d4fPrize'} ? floatval($model->{'gd4d4fPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd3dAbcPrize1'] = function ($model) {
            return $model->{'gd3dAbcPrize1'} ? floatval($model->{'gd3dAbcPrize1'}) : null; //Cast string to float/double type
        };

        $fields['gd3dAbcPrize2'] = function ($model) {
            return $model->{'gd3dAbcPrize2'} ? floatval($model->{'gd3dAbcPrize2'}) : null; //Cast string to float/double type
        };

        $fields['gd3dAbcPrize3'] = function ($model) {
            return $model->{'gd3dAbcPrize3'} ? floatval($model->{'gd3dAbcPrize3'}) : null; //Cast string to float/double type
        };

        $fields['gd3d3aPrize'] = function ($model) {
            return $model->{'gd3d3aPrize'} ? floatval($model->{'gd3d3aPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd3d3bPrize'] = function ($model) {
            return $model->{'gd3d3bPrize'} ? floatval($model->{'gd3d3bPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd3d3cPrize'] = function ($model) {
            return $model->{'gd3d3cPrize'} ? floatval($model->{'gd3d3cPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd3d3dPrize'] = function ($model) {
            return $model->{'gd3d3dPrize'} ? floatval($model->{'gd3d3dPrize'}) : null; //Cast string to float/double type
        };

        $fields['gd3d3ePrize'] = function ($model) {
            return $model->{'gd3d3ePrize'} ? floatval($model->{'gd3d3ePrize'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize1'] = function ($model) {
            return $model->{'5dPrize1'} ? floatval($model->{'5dPrize1'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize2'] = function ($model) {
            return $model->{'5dPrize2'} ? floatval($model->{'5dPrize2'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize3'] = function ($model) {
            return $model->{'5dPrize3'} ? floatval($model->{'5dPrize3'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize4'] = function ($model) {
            return $model->{'5dPrize4'} ? floatval($model->{'5dPrize4'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize5'] = function ($model) {
            return $model->{'5dPrize5'} ? floatval($model->{'5dPrize5'}) : null; //Cast string to float/double type
        };

        $fields['5dPrize6'] = function ($model) {
            return $model->{'5dPrize6'} ? floatval($model->{'5dPrize6'}) : null; //Cast string to float/double type
        };

        $fields['6dPrize1'] = function ($model) {
            return $model->{'6dPrize1'} ? floatval($model->{'6dPrize1'}) : null; //Cast string to float/double type
        };

        $fields['6dPrize2'] = function ($model) {
            return $model->{'6dPrize2'} ? floatval($model->{'6dPrize2'}) : null; //Cast string to float/double type
        };

        $fields['6dPrize3'] = function ($model) {
            return $model->{'6dPrize3'} ? floatval($model->{'6dPrize3'}) : null; //Cast string to float/double type
        };

        $fields['6dPrize4'] = function ($model) {
            return $model->{'6dPrize4'} ? floatval($model->{'6dPrize4'}) : null; //Cast string to float/double type
        };

        $fields['6dPrize5'] = function ($model) {
            return $model->{'6dPrize5'} ? floatval($model->{'6dPrize5'}) : null; //Cast string to float/double type
        };

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['package'] = function ($model) {
            return $model->package;
        };

        return $extraFields;
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

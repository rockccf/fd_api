<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "bet".
 *
 * @property int $id
 * @property int $version
 * @property int $status
 * @property int $betMethod
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
 * @property string $superior4dCommRate
 * @property string $superior6dCommRate
 * @property string $superiorGdCommRate
 * @property string $masterCommRate
 * @property string $totalSales
 * @property string $ownCommission
 * @property string $extraCommission
 * @property string $totalCommission
 * @property string $totalWin
 * @property string $totalSuperiorBonus
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
 * @property BetDetail[] $betDetailsSortByNumber
 * @property BetNumber[] $betNumbers
 * @property User $creator
 * @property string $slipText
 */
class Bet extends \yii\db\ActiveRecord
{
    public $volume;

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
            [['version', 'status', 'betMethod', 'masterId'], 'integer'],
            [['status', 'betMethod', 'betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dCommRate', '6dCommRate', 'gdCommRate', 'masterCommRate', 'masterId'], 'required'],
            [['betMaxLimitBig', 'betMaxLimitSmall', 'betMaxLimit4a', 'betMaxLimit4b', 'betMaxLimit4c', 'betMaxLimit4d', 'betMaxLimit4e', 'betMaxLimit4f', 'betMaxLimit3abc', 'betMaxLimit3a', 'betMaxLimit3b', 'betMaxLimit3c', 'betMaxLimit3d', 'betMaxLimit3e', 'betMaxLimit5d', 'betMaxLimit6d', '4dCommRate', '6dCommRate', 'gdCommRate', 'extra4dCommRate', 'extra6dCommRate', 'extraGdCommRate', 'superior4dCommRate', 'superior6dCommRate', 'superiorGdCommRate', 'masterCommRate', 'totalSales', 'ownCommission', 'extraCommission', 'totalCommission', 'totalWin', 'totalSuperiorBonus', 'totalSuperiorCommission'], 'number'],
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
            'betMethod' => 'Bet Method',
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
            'extra4dCommRate' => 'Extra 4d Comm Rate',
            'extra6dCommRate' => 'Extra 6d Comm Rate',
            'extraGdCommRate' => 'Extra Gd Comm Rate',
            'superior4dCommRate' => 'Superior 4d Comm Rate',
            'superior6dCommRate' => 'Superior 6d Comm Rate',
            'superiorGdCommRate' => 'Superior Gd Comm Rate',
            'masterCommRate' => 'Master Comm Rate',
            'totalSales' => 'Total Sales',
            'ownCommission' => 'Own Commission',
            'extraCommission' => 'Extra Commission',
            'totalCommission' => 'Total Commission',
            'totalWin' => 'Total Win',
            'totalSuperiorBonus' => 'Total Superior Bonus',
            'totalSuperiorCommission' => 'Total Superior Commission',
            'packageId' => 'Package ID',
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

        $fields['4dCommRate'] = function ($model) {
            return floatval($model->{'4dCommRate'}); //Cast string to float/double type
        };

        $fields['6dCommRate'] = function ($model) {
            return floatval($model->{'6dCommRate'}); //Cast string to float/double type
        };

        $fields['gdCommRate'] = function ($model) {
            return floatval($model->gdCommRate); //Cast string to float/double type
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

        $fields['superior4dCommRate'] = function ($model) {
            return floatval($model->superior4dCommRate); //Cast string to float/double type
        };

        $fields['superior6dCommRate'] = function ($model) {
            return floatval($model->superior6dCommRate); //Cast string to float/double type
        };

        $fields['superiorGdCommRate'] = function ($model) {
            return floatval($model->superiorGdCommRate); //Cast string to float/double type
        };

        $fields['masterCommRate'] = function ($model) {
            return floatval($model->masterCommRate); //Cast string to float/double type
        };

        $fields['totalSales'] = function ($model) {
            return floatval($model->totalSales); //Cast string to float/double type
        };

        $fields['ownCommission'] = function ($model) {
            return floatval($model->ownCommission); //Cast string to float/double type
        };

        $fields['extraCommission'] = function ($model) {
            return floatval($model->extraCommission); //Cast string to float/double type
        };

        $fields['totalCommission'] = function ($model) {
            return floatval($model->totalCommission); //Cast string to float/double type
        };

        $fields['totalWin'] = function ($model) {
            return floatval($model->totalWin); //Cast string to float/double type
        };

        $fields['totalSuperiorBonus'] = function ($model) {
            return floatval($model->totalSuperiorBonus); //Cast string to float/double type
        };

        $fields['totalSuperiorCommission'] = function ($model) {
            return floatval($model->totalSuperiorCommission); //Cast string to float/double type
        };

        $fields['creator'] = function ($model) {
            return $model->creator;
        };

        return $fields;
    }

    public function extraFields()
    {
        $extraFields = parent::extraFields();

        $extraFields['creator'] = function ($model) {
            return $model->creator;
        };

        $extraFields['slipText'] = function ($model) {
            return $model->slipText;
        };

        $extraFields['betDetails'] = function ($model) {
            return $model->betDetails;
        };

        $extraFields['betDetailsSortByNumber'] = function ($model) {
            return $model->betDetailsSortByNumber;
        };

        return $extraFields;
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

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetDetailsSortByNumber()
    {
        return $this->hasMany(BetDetail::class, ['betId' => 'id'])
            ->orderBy('betNumberId,number');
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCreator()
    {
        return $this->hasOne(User::class, ['id' => 'createdBy']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getBetNumbers()
    {
        return $this->hasMany(BetNumber::class, ['betId' => 'id']);
    }

    /**
     * @return string
     */
    public function getSlipText() {
        $username = $this->creator->username;
        $result = "$username\n";
        $grandTotalBet = 0;
        $grandTotalSales = 0;
        $grandTotalReject = 0;
        foreach ($this->betNumbers as $betNumber) {
            $drawDateString = implode(',', array_map(function ($drawDate) {
                $dateObj = new \DateTime($drawDate);
                return $dateObj->format('md');
            }, $betNumber->drawDates));
            $result .= '{'.$drawDateString.'}'."\n";

            $companyString = implode('',$betNumber->companyCodes);
            $result .= '('.$companyString.')'."\n";

            $betAmountString = "";
            $big = $betNumber->big;
            $small = $betNumber->small;
            $amount4a = $betNumber->{'4a'};
            $amount4b = $betNumber->{'4b'};
            $amount4c = $betNumber->{'4c'};
            $amount4d = $betNumber->{'4d'};
            $amount4e = $betNumber->{'4e'};
            $amount4f = $betNumber->{'4f'};
            $amount3abc = $betNumber->{'3abc'};
            $amount3a = $betNumber->{'3a'};
            $amount3b = $betNumber->{'3b'};
            $amount3c = $betNumber->{'3c'};
            $amount3d = $betNumber->{'3d'};
            $amount3e = $betNumber->{'3e'};
            $amount5d = $betNumber->{'5d'};
            $amount6d = $betNumber->{'6d'};

            if (!empty($big)) {
                $betAmountString .= " B".floatval($big);
            }
            if (!empty($small)) {
                $betAmountString .= " S".floatval($small);
            }
            if (!empty($amount4a)) {
                $betAmountString .= " 4A".floatval($amount4a);
            }
            if (!empty($amount4b)) {
                $betAmountString .= " 4B".floatval($amount4b);
            }
            if (!empty($amount4c)) {
                $betAmountString .= " 4C".floatval($amount4c);
            }
            if (!empty($amount4d)) {
                $betAmountString .= " 4D".floatval($amount4d);
            }
            if (!empty($amount4e)) {
                $betAmountString .= " 4E".floatval($amount4e);
            }
            if (!empty($amount4f)) {
                $betAmountString .= " 4F".floatval($amount4f);
            }
            if (!empty($amount3abc)) {
                $betAmountString .= " 3ABC".floatval($amount3abc);
            }
            if (!empty($amount3a)) {
                $betAmountString .= " 3A".floatval($amount3a);
            }
            if (!empty($amount3b)) {
                $betAmountString .= " 3B".floatval($amount3b);
            }
            if (!empty($amount3c)) {
                $betAmountString .= " 3C".floatval($amount3c);
            }
            if (!empty($amount3d)) {
                $betAmountString .= " 3D".floatval($amount3d);
            }
            if (!empty($amount3e)) {
                $betAmountString .= " 3E".floatval($amount3e);
            }
            if (!empty($amount5d)) {
                $betAmountString .= " 5D".floatval($amount5d);
            }
            if (!empty($amount6d)) {
                $betAmountString .= " 6D".floatval($amount6d);
            }

            if ($betNumber->status == Yii::$app->params['BET']['NUMBER']['STATUS']['ACCEPTED']) {
                $stat = 'A';
            } else if ($betNumber->status == Yii::$app->params['BET']['NUMBER']['STATUS']['LIMITED']) {
                $stat = 'L';
            } else if ($betNumber->status == Yii::$app->params['BET']['NUMBER']['STATUS']['REJECTED']) {
                $stat = 'R';
            }
            if ($betNumber->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['SINGLE']) {
                $result .= $betNumber->number."($stat) =$betAmountString\n";
            } else if ($betNumber->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['RETURN']) {
                $result .= "R*".$betNumber->number."($stat) =$betAmountString\n";
            } else if ($betNumber->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['BOX']) {
                $result .= "B*".$betNumber->number."($stat) =$betAmountString\n";
            } else if ($betNumber->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['IBOX']) {
                $result .= "I*".$betNumber->number."($stat) =$betAmountString\n";
            } else if ($betNumber->betOption == Yii::$app->params['BET']['NUMBER']['OPTION']['PH']) {
                $result .= "P*".$betNumber->number."($stat) =$betAmountString\n";
            }

            if ($betNumber->status != Yii::$app->params['BET']['NUMBER']['STATUS']['ACCEPTED']) {
                //Proceed to get the reject bets and display all of them
                $bds = $betNumber->betDetails;
                foreach ($bds as $bd) {
                    if ($bd->status != Yii::$app->params['BET']['DETAIL']['STATUS']['ACCEPTED']) {
                        $bdr = $bd->betDetailReject;
                        $rejectedBetAmountString = "";
                        $rejectedBig = $bdr->big;
                        $rejectedSmall = $bdr->small;
                        $rejectedAmount4a = $bdr->{'4a'};
                        $rejectedAmount4b = $bdr->{'4b'};
                        $rejectedAmount4c = $bdr->{'4c'};
                        $rejectedAmount4d = $bdr->{'4d'};
                        $rejectedAmount4e = $bdr->{'4e'};
                        $rejectedAmount4f = $bdr->{'4f'};
                        $rejectedAmount3abc = $bdr->{'3abc'};
                        $rejectedAmount3a = $bdr->{'3a'};
                        $rejectedAmount3b = $bdr->{'3b'};
                        $rejectedAmount3c = $bdr->{'3c'};
                        $rejectedAmount3d = $bdr->{'3d'};
                        $rejectedAmount3e = $bdr->{'3e'};
                        $rejectedAmount5d = $bdr->{'5d'};
                        $rejectedAmount6d = $bdr->{'6d'};

                        if (!empty($rejectedBig)) {
                            $rejectedBetAmountString .= " B".floatval($rejectedBig);
                        }
                        if (!empty($rejectedSmall)) {
                            $rejectedBetAmountString .= " S".floatval($rejectedSmall);
                        }
                        if (!empty($rejectedAmount4a)) {
                            $rejectedBetAmountString .= " 4A".floatval($rejectedAmount4a);
                        }
                        if (!empty($rejectedAmount4b)) {
                            $rejectedBetAmountString .= " 4B".floatval($rejectedAmount4b);
                        }
                        if (!empty($rejectedAmount4c)) {
                            $rejectedBetAmountString .= " 4C".floatval($rejectedAmount4c);
                        }
                        if (!empty($rejectedAmount4d)) {
                            $rejectedBetAmountString .= " 4D".floatval($rejectedAmount4d);
                        }
                        if (!empty($rejectedAmount4e)) {
                            $rejectedBetAmountString .= " 4E".floatval($rejectedAmount4e);
                        }
                        if (!empty($rejectedAmount4f)) {
                            $rejectedBetAmountString .= " 4F".floatval($rejectedAmount4f);
                        }
                        if (!empty($rejectedAmount3abc)) {
                            $rejectedBetAmountString .= " 3ABC".floatval($rejectedAmount3abc);
                        }
                        if (!empty($rejectedAmount3a)) {
                            $rejectedBetAmountString .= " 3A".floatval($rejectedAmount3a);
                        }
                        if (!empty($rejectedAmount3b)) {
                            $rejectedBetAmountString .= " 3B".floatval($rejectedAmount3b);
                        }
                        if (!empty($rejectedAmount3c)) {
                            $rejectedBetAmountString .= " 3C".floatval($rejectedAmount3c);
                        }
                        if (!empty($rejectedAmount3d)) {
                            $rejectedBetAmountString .= " 3D".floatval($rejectedAmount3d);
                        }
                        if (!empty($rejectedAmount3e)) {
                            $rejectedBetAmountString .= " 3E".floatval($rejectedAmount3e);
                        }
                        if (!empty($rejectedAmount5d)) {
                            $rejectedBetAmountString .= " 5D".floatval($rejectedAmount5d);
                        }
                        if (!empty($rejectedAmount6d)) {
                            $rejectedBetAmountString .= " 6D".floatval($rejectedAmount6d);
                        }

                        $result .= $bd->companyDraw->company->code.' '.$bd->number."(R) =$rejectedBetAmountString\n";
                    }
                }
            }

            $result .= "\n";
            $grandTotalBet += $betNumber->totalBet;
            $grandTotalSales += $betNumber->totalSales;
            $grandTotalReject += $betNumber->totalReject;
        }

        $grandTotalBet = round($grandTotalBet,2);
        $grandTotalSales = round($grandTotalSales,2);
        $grandTotalReject = round($grandTotalReject,2);
        if ($betNumber->status != Yii::$app->params['BET']['NUMBER']['STATUS']['ACCEPTED']) {
            $result .= "GT:$grandTotalSales(A) $grandTotalBet(B) $grandTotalReject(R)";
        } else {
            $result .= "GT:$grandTotalSales(A)";
        }

        return $result;
    }
}

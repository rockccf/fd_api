<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * This is the model class for table "user_profile".
 *
 * @property integer $id
 * @property integer $version
 * @property string $firstName
 * @property string $lastName
 * @property string $jobTitle
 * @property integer $userId
 * @property integer $departmentId
 * @property integer $createdBy
 * @property string $createdAt
 * @property integer $updatedBy
 * @property string $updatedAt
 *
 * @property User $user
 * @property Department $department
 * @property string $fullName
 * @property Image $profileImage
 */
class UserProfile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_profile';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'userId', 'departmentId'], 'integer'],
            [['firstName', 'lastName', 'jobTitle', 'userId', 'departmentId'], 'required'],
            [['firstName', 'lastName'], 'string', 'max' => 100],
            [['jobTitle'], 'string', 'max' => 255],
            [['userId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['userId' => 'id']],
            [['departmentId'], 'exist', 'skipOnError' => true, 'targetClass' => Department::class, 'targetAttribute' => ['departmentId' => 'id']],
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
            'firstName' => 'First Name',
            'lastName' => 'Last Name',
            'jobTitle' => 'Job Title',
            'userId' => 'User ID',
            'departmentId' => 'Department ID',
            'createdAt' => 'Created At',
            'createdBy' => 'Created By',
            'updatedAt' => 'Updated At',
            'updatedBy' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class, //Automatically update the timestamp columns
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt', 'updatedAt'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updatedAt'],
                ],
                // if you're using datetime instead of UNIX timestamp:
                'value' => new Expression('NOW()'),
            ],
            [
                'class' => BlameableBehavior::class, //Automatically update the user id columns
                'createdByAttribute' => 'createdBy',
                'updatedByAttribute' => 'updatedBy',
                'value' => [$this, 'blameableValue']
            ],
        ];
    }

    public function blameableValue()
    {
        //By returning null value, BlameableBehavior will attempt to get the user id by referring to Yii::$app->user->id
        $userId = null;

        if ($this->isNewRecord) {
            if (Yii::$app->user->isGuest) {
                $userId = 1; //Default to system administrator
            } else {
                $userId = Yii::$app->user->id;
            }
        } else {
            if (Yii::$app->user->isGuest) {
                return $this->updatedBy;
            } else {
                $userId = Yii::$app->user->id;
            }
        }

        return $userId;
    }

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        $fields["fullName"] = function ($model) {
            return $model->fullName;
        };

        $fields["profileImage"] = function ($model) {
            return $model->profileImage;
        };

        return $fields;
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
    public function getDepartment()
    {
        return $this->hasOne(Department::class, ['id' => 'departmentId']);
    }

    /*
     * @param $id
     * @return \yii\db\ActiveRecord
     */
    public function getProfileImage()
    {
        $user = $this->user;
        if ($user->userType == Yii::$app->params['USER']['TYPE']['ADMIN']) {
            $type = Yii::$app->params['IMAGE']['OWNER_TYPE']['ADMIN_USER'];
        } else if ($user->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
            $type = Yii::$app->params['IMAGE']['OWNER_TYPE']['TENANT_USER'];
        }

        return $this->hasOne(Image::class, ['imageOwnerId' => 'userId'])
            ->andOnCondition(['image.imageOwnerType' => $type]);
    }

    /*
     * @return string
     */
    public function getFullName()
    {
        return $this->firstName . ' ' . $this->lastName;
    }
}

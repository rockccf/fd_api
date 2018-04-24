<?php

namespace app\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "auth_item".
 *
 * @property integer $id
 * @property string $name
 * @property integer $type
 * @property string $description
 * @property string $rule_name
 * @property string $data
 * @property string $category
 * @property string $remarks
 * @property integer $created_at
 * @property integer $created_by
 * @property integer $updated_at
 * @property integer $updated_by
 *
 * @property AuthAssignment[] $authAssignments
 * @property AuthRule $ruleName
 * @property AuthItemChild[] $authItemChildren
 * @property AuthItemChild[] $authItemChildren0
 * @property AuthItem[] $children
 * @property AuthItem[] $parents
 */
class AuthItem extends ActiveRecord
{
    const TYPE_ROLE = 1;
    const TYPE_PERMISSION = 2;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'auth_item';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'type'], 'required'],
            [['type', 'created_at', 'updated_at'], 'integer'],
            [['description', 'data', 'category', 'remarks'], 'string'],
            [['name', 'rule_name'], 'string', 'max' => 150],
            [['category'], 'string', 'max' => 50],
            [['remarks'], 'string', 'max' => 255],
            [['description', 'remarks'], 'trim'],
            [['rule_name'], 'exist', 'skipOnError' => true, 'targetClass' => AuthRule::class, 'targetAttribute' => ['rule_name' => 'name']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'type' => 'Type',
            'description' => 'Description',
            'rule_name' => 'Rule Name',
            'data' => 'Data',
            'category' => 'Category',
            'remarks' => 'Remarks',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class, //Automatically update the timestamp columns
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ],
            ],
            [
                'class' => BlameableBehavior::class, //Automatically update the user id columns
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => [$this, 'blameableValue']
            ],
        ];
    }

    public function blameableValue()
    {
        $userId = null;

        if ($this->isNewRecord) {
            if (Yii::$app->user->isGuest) {
                $userId = 1; //Default to system administrator
            } else {
                $userId = Yii::$app->user->id;
            }
        } else {
            if (Yii::$app->user->isGuest) {
                return $this->updated_by;
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

        $fields['permissions'] = function ($model) {
            $permissions = $model->getPermissions($model->name);
            $permissionsArray = array();
            foreach ($permissions as $permission) { //Explicitly convert the object into array
                array_push($permissionsArray, $permission);
            }
            return $permissionsArray;
        };

        return $fields;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['item_name' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getRuleName()
    {
        return $this->hasOne(AuthRule::class, ['name' => 'rule_name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren()
    {
        return $this->hasMany(AuthItemChild::class, ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthItemChildren0()
    {
        return $this->hasMany(AuthItemChild::class, ['child' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getChildren()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'child'])->viaTable('auth_item_child', ['parent' => 'name']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getParents()
    {
        return $this->hasMany(AuthItem::class, ['name' => 'parent'])->viaTable('auth_item_child', ['child' => 'name']);
    }

    /**
     * Function to get the permissions by role name
     * @param $id
     * @return \yii\rbac\Permission[]
     */
    public function getPermissions($name)
    {
        $auth = Yii::$app->authManager;

        if (empty($name)) {
            $name = $this->name;
        }

        return $auth->getPermissionsByRole($name);
    }
}

<?php

namespace app\models;

use Firebase\JWT\JWT;
use Yii;
use yii\base\Model;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\filters\RateLimitInterface;
use yii\helpers\ArrayHelper;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "user".
 *
 * @property int $id
 * @property int $version
 * @property string $username
 * @property string $name
 * @property string $mobileNo
 * @property string $passwordHash
 * @property string $passwordExpiryDate
 * @property int $passwordNeverExpires
 * @property int $active
 * @property int $locked
 * @property int $userType
 * @property string $lastLoginAt
 * @property int $agentId
 * @property int $masterId
 * @property int $createdBy
 * @property string $createdAt
 * @property int $updatedBy
 * @property string $updatedAt
 *
 * @property User $agent
 * @property Master $master
 * @property UserDetail $userDetail
 * @property AuthAssignment[] $authAssignments
 *
 * Non-persistent fields
 * @property string $password
 * @property string $confirmPassword
 */
class User extends ActiveRecord implements IdentityInterface
{
    const SCENARIO_CHANGEPASSWORD = 'changePassword';
    const SCENARIO_RESETPASSWORD = 'resetPassword';

    public $password;
    public $confirmPassword;

    public $jwt;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['version', 'agentId', 'masterId','passwordNeverExpires', 'active', 'locked', 'userType'], 'integer'],
            [['username', 'name', 'passwordHash', 'active', 'userType'], 'required'],
            [['passwordExpiryDate', 'lastLoginAt'], 'safe'],
            [['username'], 'string', 'max' => 50],
            [['name', 'passwordHash'], 'string', 'max' => 255],
            [['mobileNo'], 'string', 'max' => 20],
            [['password', 'confirmPassword'], 'string', 'max' => 100],
            [['username','name'], 'trim'],
            ['password', 'compare', 'compareAttribute' => 'confirmPassword', 'on' => ['changePassword', 'resetPassword']],
            [['username'], 'unique',
                'targetAttribute' => ['username', 'userType'],
                'message' => 'The specified username has already been taken.',
                'when' => function ($model)
                {
                    return $model->userType == Yii::$app->params['USER']['TYPE']['ADMIN'];
                }
            ],
            [['username'], 'unique',
                'targetAttribute' => ['username', 'userType', 'masterId'],
                'message' => 'The specified username has already been taken.',
                'when' => function ($model)
                {
                    return ($model->userType == Yii::$app->params['USER']['TYPE']['MASTER']
                        ||  $model->userType == Yii::$app->params['USER']['TYPE']['AGENT']
                        ||  $model->userType == Yii::$app->params['USER']['TYPE']['PLAYER']);
                }
            ],
            [['agentId'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['agentId' => 'id']],
            [['masterId'], 'exist', 'skipOnError' => true, 'targetClass' => Master::class, 'targetAttribute' => ['masterId' => 'id']],
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
            'username' => 'Username',
            'name' => 'Name',
            'mobileNo' => 'Mobile No',
            'passwordHash' => 'Password Hash',
            'password' => 'Password',
            'confirmPassword' => 'Confirm Password',
            'passwordExpiryDate' => 'Password Expiry Date',
            'passwordNeverExpires' => 'Password Never Expires',
            'active' => 'Active',
            'locked' => 'Locked',
            'userType' => 'User Type',
            'lastLoginAt' => 'Last Login At',
            'agentId' => 'Agent ID',
            'masterId' => 'Master ID',
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
                'defaultValue' => 1
            ],
        ];
    }

    // filter out some fields, best used when you want to inherit the parent implementation
    // and blacklist some sensitive fields.
    public function fields()
    {
        $fields = parent::fields();

        // remove fields that contain sensitive information
        unset($fields['passwordHash']);
        unset($fields['password']);
        unset($fields['confirmPassword']);

        $fields['jwt'] = function ($model) {
            return $model->jwt;
        };

        $fields['roles'] = function ($model) {
            $roles = $model->getRoles($model->id);
            $rolesArray = array();
            foreach ($roles as $role) { //Explicitly convert the object into array
                array_push($rolesArray, $role);
            }
            return $rolesArray;
        };

        $fields['permissions'] = function ($model) {
            $permissions = $model->getPermissions($model->id);
            $permissionsArray = array();
            foreach ($permissions as $permission) { //Explicitly convert the object into array
                array_push($permissionsArray, $permission);
            }
            return $permissionsArray;
        };

        $fields['master'] = function($model) {
            if (!empty($model->master)) {
                return $model->master;
            } else {
                return null;
            }
        };

        $fields['userDetail'] = function($model) {
            if (!empty($model->userDetail)) {
                return $model->userDetail;
            } else {
                return null;
            }
        };

        return $fields;
    }

    //For expand usage
    public function extraFields()
    {
        $extraFields = parent::extraFields();

        return $extraFields;
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
    public function getUserDetail()
    {
        return $this->hasOne(UserDetail::class, ['userId' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getAuthAssignments()
    {
        return $this->hasMany(AuthAssignment::class, ['user_id' => 'id']);
    }

    /**
     * Function to get the roles by user id
     * @param $id
     * @return \yii\rbac\Role[]
     */
    public function getRoles($id = null)
    {
        $auth = Yii::$app->authManager;

        if (empty($id)) {
            $id = $this->id;
        }

        return $auth->getRolesByUser($id);
    }

    /**
     * Function to get the permissions by user id
     * @param $id
     * @return \yii\rbac\Permission[]
     */
    public function getPermissions($id)
    {
        $auth = Yii::$app->authManager;

        if (empty($id)) {
            $id = $this->id;
        }

        return $auth->getPermissionsByUser($id);
    }

    /**
     * Finds an identity by the given ID.
     *
     * @param string|integer $id the ID to be looked for
     * @return IdentityInterface|null the identity object that matches the given ID.
     */
    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    /**
     * Finds an identity by the given token.
     *
     * @param string $token the token to be looked for
     * @return IdentityInterface|null the identity object that matches the given token.
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        /*
         * decode the jwt using the key from config
         */
        $secretKey = base64_decode(Yii::$app->params['GLOBAL']['SECRET_KEY']);

        $jwt = JWT::decode($token, $secretKey, array('HS256'));

        return static::findOne($jwt->data->id);
    }

    /**
     * Finds user by username
     *
     * @param string $username , $userType
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    /**
     * Finds user by username, masterId
     *
     * @param string $username , $userType, $tenantId
     * @return static|null
     */
    public static function findByUsernameAndMasterId($username, $masterId)
    {
        return static::findOne(['username' => $username, 'masterId' => $masterId]);
    }

    /**
     * Finds user by rolename
     *
     * @param string $username , $userType, $tenantId
     * @return array|null
     */
    public static function findUsersByRole($rolename, $active = true)
    {
        return static::find()
            ->innerJoinWith('authAssignments aa')
            ->where(['active' => $active, 'aa.item_name' => $rolename])->all();
    }

    /**
     * @return int|string current user ID
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string current user auth key
     */
    public function getAuthKey()
    {
        //return $this->auth_key;
        return null;
    }

    /**
     * @param string $authKey
     * @return boolean if auth key is valid for current user
     */
    public function validateAuthKey($authKey)
    {
        //return $this->getAuthKey() === $authKey;
        return true;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return boolean if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->passwordHash);
    }

    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPasswordHash($password)
    {
        $this->passwordHash = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * Generate and return Json Web Token
     * @return string
     */
    public function generateJWT()
    {
        $tokenId = base64_encode(random_bytes(32));
        $issuedAt = time();
        $notBefore = $issuedAt; //Token is valid immediately
        $expire = $notBefore + Yii::$app->params['GLOBAL']['TOKEN_VALIDITY']; //Token expires after one day (86400 seconds)
        $serverName = Yii::$app->params['GLOBAL']['SERVER_NAME'];

        /*
         * Create the token as an array
         */
        $data = [
            'iat' => $issuedAt,         // Issued at: time when the token was generated
            'jti' => $tokenId,          // Json Token Id: an unique identifier for the token
            'iss' => $serverName,       // Issuer
            'nbf' => $notBefore,        // Not before
            'exp' => $expire,           // Expire
            'data' => [                  // Data related to the signer user
                'id' => $this->id, // id from the user table
                'username' => $this->username, // username
                /*'roles' => [
                    $this->getRoles($this->id),
                ],
                'permissions' => [
                    $this->getPermissions($this->id),
                ],*/
            ]
        ];

        $secretKey = base64_decode(Yii::$app->params['GLOBAL']['SECRET_KEY']);
        //$secretKey = "123456";

        /*
         * Encode the array to a JWT string.
         * Second parameter is the key to encode the token.
         *
         * The output string can be validated at http://jwt.io/
         */
        $this->jwt = JWT::encode(
            $data,      //Data to be encoded in the JWT
            $secretKey, // The signing key
            'HS256'     // Algorithm used to sign the token, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40#section-3
        );

        return $this->jwt;
    }

    /*
     * return JWT string
     */
    public function getJWT()
    {
        return $this->jwt;
    }

    /*
     * Rate Limiting
     * To limit the API usage of each user
     */
    public function getRateLimit($request, $action)
    {
        return [$this->rateLimit, 1]; // $rateLimit requests per second
    }

    public function loadAllowance($request, $action)
    {
        return [$this->allowance, $this->allowance_updated_at];
    }

    public function saveAllowance($request, $action, $allowance, $timestamp)
    {
        $this->allowance = $allowance;
        $this->allowance_updated_at = $timestamp;
        $this->save();
    }
}
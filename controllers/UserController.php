<?php

namespace app\controllers;

use app\models\User;
use app\models\UserProfile;
use Yii;
use app\components\dbix\CommonClass;
use app\components\dbix\EmailClass;
use yii\filters\AccessControl;
use yii\filters\auth\HttpBearerAuth;
use yii\filters\ContentNegotiator;
use yii\filters\Cors;
use yii\filters\VerbFilter;
use yii\helpers\Url;
use yii\helpers\Json;
use yii\rest\ActiveController;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\web\UnauthorizedHttpException;

class UserController extends ActiveController
{
    public $viewAction = 'view';
    public $modelClass = 'app\models\User';
    //Include pagination information directly in the response body
    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function behaviors()
    {
        $behaviors = parent::behaviors();

        //Remove authentication filter
        $auth = $behaviors['authenticator'];
        unset($behaviors['authenticator']);

        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                // restrict access to
                'Origin' => Yii::$app->params['GLOBAL']['ALLOWED_DOMAINS'],
                'Access-Control-Request-Method' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'HEAD', 'OPTIONS'],
                // Allow only POST and PUT methods
                'Access-Control-Request-Headers' => Yii::$app->params['GLOBAL']['ALLOWED_REQUEST_HEADERS'],
                // Allow only headers 'X-Wsse'
                'Access-Control-Allow-Credentials' => true,
                // Allow OPTIONS caching
                'Access-Control-Max-Age' => 3600,
                // Allow the X-Pagination-Current-Page header to be exposed to the browser.
                //'Access-Control-Expose-Headers' => ['X-Pagination-Current-Page'],
            ],
        ];

        //Re-add authentication filter
        $behaviors['authenticator'] = $auth;
        // avoid authentication on CORS-pre-flight requests (HTTP OPTIONS method)
        $behaviors['authenticator'] = [
            'class' => HttpBearerAuth::class,
            'except' => ['options', 'login',                
                'is-username-exists', 'is-email-exists',
                //'request-password-reset','verify-password-reset-request', 
                'request-reset-password', 'verify-reset-password-token', 'reset-password']
        ];

        $behaviors['verb'] = [
            'class' => VerbFilter::class,
            'actions' => [
                'index'  => ['GET','HEAD'],
                'view'   => ['GET','HEAD'],
                'create' => ['POST'],
                'update' => ['PUT','PATCH'],
                'delete' => ['DELETE'],
                'login' => ['POST']
            ],
        ];

        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => \yii\web\Response::FORMAT_JSON,
            ],
        ];

        $behaviors['access'] = [
            'class' => AccessControl::class,
            'only' => ['index','view','create','update','delete','change-password'],
            'rules' => [
                [
                    'allow' => true,
                    'actions' => ['index'],									
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['view'],																	
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['SUPPLIER'].'VIEW_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['create'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['SUPPLIER'].'CREATE_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['update'],					
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['SUPPLIER'].'UPDATE_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['delete'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_USER',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_USER'
                    ]
                ],
                [
                    'allow' => true,
                    'actions' => ['change-password'],
                    'roles' => [
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_USER_PASSWORD',
                        Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_USER_PASSWORD'
                    ]
                ]
            ],
        ];

        return $behaviors;
    }

    //Override the checkAccess in ActiveController for special checking
    public function checkAccess($action, $model = null, $params = [])
    {
        if ($action === 'view' or $action === 'update' or $action === 'delete')
        {
            if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
                //Make sure the tenant can perform actions stated above to the objects under the same tenant.
                if (Yii::$app->user->identity->tenantId != $model->tenantId
                    || $model->userType != Yii::$app->params['USER']['TYPE']['TENANT']) {
                    throw new ForbiddenHttpException('You do not have the permission to ' . $action . ' the object.');
                }
            } else if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['SUPPLIER']) {
                //Make sure the supplier can only perform actions stated above to own or own company objects
                if( Yii::$app -> user -> identity -> supplierId != $model -> supplierId ||
                    ( Yii::$app -> user -> identity -> id != $model -> id &&
                    Yii::$app -> user -> identity -> supplierUserProfile -> isMaster != 1 )
                ){
                        throw new ForbiddenHttpException('You do not have the permission to ' . $action . ' the object.');
                }
            }
        }
    }

    public function actions()
    {
        /*
         * Default routing set by yii\rest\UrlRule
         * 'PUT,PATCH users/<id>' => 'user/update',
         * 'DELETE users/<id>' => 'user/delete',
         * 'GET,HEAD users/<id>' => 'user/view',
         * 'POST users' => 'user/create',
         * 'GET,HEAD users' => 'user/index',
         * 'users/<id>' => 'user/options',
         * 'users' => 'user/options',
        */
        $actions = parent::actions();

        // disable the default "create", "update" and "delete" action
        unset($actions['create'],$actions['update'],$actions['delete']);

        // customize the data provider preparation with the "prepareDataProvider()" method
        $actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

        return $actions;
    }

    //Customize the data provider preparation with the "prepareDataProvider()" method
    public function prepareDataProvider()
    {
        $request = Yii::$app->request;
        $params = $request->get();

        $user = User::find();
        $where = null;

        return CommonClass::prepareActiveQueryDataProvider($params,$user,$where);
    }

    public function actionCreate()
    { //Override the default create action
        $request = Yii::$app->request;
        $params = $request->post();
	 
        if( Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['SUPPLIER'] ){            
            $params['userType'] = Yii::$app->params['USER']['TYPE']['SUPPLIER'];
            $params['passwordExpiryDate'] = new \yii\db\Expression('NOW()');            
            $params['passwordNeverExpires'] = 0;
            $params['active'] = 1;
            //$params['password']	= sha1($params['username']); // this supplier create user should be integrate with password reset
        }
        $user = new User;
        $user->load($params,''); //Massive Assignment
        $user->setPasswordHash($params['password']);

        if (Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT']) {
            $user->tenantId = Yii::$app->user->identity->tenantId;
            $user->passwordNeverExpires = true;
        }

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!$user->save()) {
                Yii::error($user->errors);
                return $user;
            }
            if( Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['SUPPLIER'] ){
                $userProfile = new SupplierUserProfile();
                $loginPassword = $params['password'];
                $params = $params['userProfile'];                
                $params['isMaster'] = 0;
                $params['phone'] = Json::encode([
                    'telephone' => array_key_exists('telephone', $params['phone']) ? $params['phone']['telephone'] : '' ,
                    'mobilePhone' => array_key_exists('mobilePhone', $params['phone']) ? $params['phone']['mobilePhone'] : '' 
                ]);
            }else{
                $userProfile = new UserProfile();						
            }
		
			$userProfile->attributes = $params; //Massive Assignment
            $userProfile->userId = $user->id;
            
            if (!$userProfile->save()) {
                Yii::error($userProfile->errors);
                return $userProfile;
            }
	 
            if( Yii::$app->user->identity->userType == Yii::$app->params['USER']['TYPE']['TENANT'] ){
                $auth = Yii::$app->authManager;
                $role = $auth->getRole($params['role']);
                //It will throw exception if the role has already been assigned to the user
                if (!$auth->assign($role, $user->getId())) {
                    throw new ServerErrorHttpException(("Failed to assign role to the user."));
                }
            }

            // Supplier create user send email.
            if( Yii::$app -> user -> identity -> userType == Yii::$app -> params['USER']['TYPE']['SUPPLIER'] ){
                $supplier = Supplier::find() -> where(['id' => $user -> supplierId]) -> one();
                /** ************************************ **/
                /** SEND EMAIL TO USER **/
                $sender = Yii::$app->params['GLOBAL']['EMAIL_FROM'];
                $recipients = [$user -> email];
                $subject = '[HorecaBid] New supplier user';
                $template = 'supplier_new_user';
                $parameters = [
                    'companyName' => $supplier -> company,
                    'companyRoc' => $supplier -> roc,
                    'companyCode' => $supplier -> companyCode, 
                    'username' => $user -> username,
                    'firstName' => $userProfile -> firstName,
                    'lastName' => $userProfile -> lastName,
                    'loginUrl' => Yii::getAlias('@supplierPortalUrl') . '/login',
                    'portalUrl' => Yii::getAlias('@supplierPortalUrl'),
                    'password' => $loginPassword
                ];
                EmailClass::sendEmail($sender, $recipients, $subject, $template, $parameters);			
                /** ************************************ **/	
            }
            
            $dbTrans->commit();

            $response = Yii::$app->getResponse();
            $response->setStatusCode(201);
            $id = implode(',', array_values($user->getPrimaryKey(true)));
            $response->getHeaders()->set('Location', Url::toRoute([$this->viewAction, 'id' => $id], true));
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $user;
    }

    public function actionUpdate($id)
    {
        $request = Yii::$app->request;
        $params = $request->bodyParams;
		
        $user = User::findOne($id);
        if (!$user) {
            throw new NotFoundHttpException();
        }
        
        $user->load($params,'');

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!$user->save()) {
                Yii::error($user->errors);
                return $user;
            }
            if( isset($params['userType']) && $params['userType'] == 3 ){
                $userProfile = $user->supplierUserProfile;	
                foreach(['emailSettings', 'isMaster', 'smsSettings', 'version'] as $columnName){
                    if( isset($params['userProfile'][$columnName]) )
                        unset($params['userProfile'][$columnName]);
                }
                
                $userProfile->load($params['userProfile'],'');
                
                $tmp = $params['userProfile']['phone'];
                $userProfile -> phone = Json::encode([
                    'telephone' => array_key_exists('telephone', $tmp) ? $tmp['telephone'] : '' ,
                    'mobilePhone' => array_key_exists('mobilePhone', $tmp) ? $tmp['mobilePhone'] : '' 
                ]);
                unset($tmp);
                
                // delete user profile image if array key presents but null
                if( array_key_exists('userImageFile', $params) && $params['userImageFile'] == null ){							
                    $flag = Image::deleteAll(['imageOwnerType' => Yii::$app->params['IMAGE']['OWNER_TYPE']['SUPPLIER_USER'], 'imageOwnerId' => $id ]);							
                }
                
            }else{					
                $userProfile = $user->userProfile;
                $userProfile->load($params,'');
            }

            if( !$userProfile->save() ) {
                Yii::error($userProfile->errors);
                return $userProfile;
            }

            // mjchen: to update frontend user (session)data instantly, until better solution is found
            if( isset($params['userType']) && $params['userType'] == Yii::$app->params['USER']['TYPE']['SUPPLIER'] ){
                $user->generateJWT();
            }else{
                //Revoke all the existing roles and permissions for the user
                $auth = Yii::$app->authManager;
                $auth->revokeAll($user->getId());

                //Assign roles and permissions					
                $role = $auth->getRole($params['role']);
                $auth->assign($role, $user->getId());
            }

            /*$permissions = $params['permissions'];
            foreach ($permissions as $permission) {
                $permissionObj = $auth->getPermission($permission);
                $auth->assign($permissionObj, $user->getId());
            }*/
					
            $dbTrans->commit();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return $user;
    }

    public function actionDelete($id)
    {
        $request = Yii::$app->request;
        $params = $request->get();

        $user = User::findOne($id);

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if( $user -> userType ==  Yii::$app->params['USER']['TYPE']['SUPPLIER'] ){
                $user->unlink('supplierUserProfile',$user->supplierUserProfile, true);	 
            }else{
                $user->unlink('userProfile',$user->userProfile, true);	 
            }
            
            if ($user->delete() === false) {
                Yii::error($user->errors);
                return $user;
            } else {
                //mjchen: temp disabled for supplier
                if( Yii::$app -> user -> identity -> userType != Yii::$app->params['USER']['TYPE']['SUPPLIER'] ){
                    //Revoke all the existing roles and permissions for the user
                    $auth = Yii::$app->authManager;
                    if (!$auth->revokeAll($id)) {
                        throw new ServerErrorHttpException('Failed to revoke the role and permissions for unknown reason.');
                    }
                }
            }

            $dbTrans->commit();
            $response = Yii::$app->getResponse();
            $response->setStatusCode(204);
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }
    }

    public function actionLogin()
    {
        $request = Yii::$app->request;
        $params = $request->post();

        if ($params['userType'] == Yii::$app->params['USER']['TYPE']['TENANT']) {
            $tenant = Tenant::findByLoginIdentity($params['identity']);

            if (!$tenant) {
                throw new UnauthorizedHttpException();
            }

            $user = User::findByUsernameAndUserTypeAndTenantId($params['username'], $params['userType'], $tenant->id);
        } elseif( $params['userType'] == Yii::$app->params['USER']['TYPE']['SUPPLIER'] ) {
            $supplier = Supplier::findByCompanyCode($params['companyCode']);

            if( !$supplier )
                throw new UnauthorizedHttpException();

            $user = User::findByUsernameAndUserTypeAndSupplierId($params['username'], $params['userType'], $supplier -> id);
        } else {
            $user = User::findByUsernameAndUserType($params['username'], $params['userType']);
        }

        if (!$user || !$user->validatePassword($params['password'])) {
            throw new UnauthorizedHttpException();
        } else {			
            //Password validated, proceed to generate JWT
            $user->touch('lastLoginAt');
            $user->generateJWT();
        }

        return $user;
    }
	
    public function actionChangePassword() {
        $request = Yii::$app->request;
        $params = $request->bodyParams;

        $user = User::findOne($params['id']);
		
        if( array_key_exists('currentPassword', $params) ){
            if( !$user -> validatePassword($params['currentPassword']) )
                throw new ForbiddenHttpException('Invalid current password.');				 
        }

        if (!$user) {
            throw new NotFoundHttpException();
        }

        $user -> scenario = User::SCENARIO_CHANGEPASSWORD;        
        $user -> setPasswordHash($params['password']);
        $user -> passwordExpiryDate = new \yii\db\Expression('NOW() + INTERVAL 10 YEAR');        
        $user -> save();

        return $user;
    }

/*     public function actionRequestPasswordReset() {

    }

    public function actionVerifyPasswordResetRequest() {

    } */
    public function actionRequestResetPassword(){
		// $identity = \app\models\User::findOne(['username' => 'acuca01']);	
		// Yii::$app -> user -> login($identity);
		// $email = Yii::$app -> user -> identity -> email;
		
		$params = Yii::$app -> request -> post();
		return CommonClass::requestResetPassword($params['userType'], $params['email']);
		//return CommonClass::requestResetPassword(Yii::$app -> params['USER']['TYPE']['SUPPLIER'], $email);
	}
	public function actionVerifyResetPasswordToken(){
		$params = Yii::$app -> request -> get();		
		return CommonClass::verifyResetPasswordToken($params['userId'], $params['token']);
	}
	public function actionResetPassword(){
		$params = Yii::$app -> request -> post();
		return CommonClass::resetPassword($params['userId'], $params['token'], $params['password']);
	}
    private function _isEmailExists($userType=null, $id=null, $idType='id'){
        $params = Yii::$app -> request -> get();
        
        if( !is_array($params) || !array_key_exists('email', $params) )
            return Yii::$app -> getResponse() -> setStatusCode(500);				
        
        $id = array_key_exists('userId', $params) && $params['userId'] != '' ? $params['userId'] : $id ;
        
        $model = User::find();
        $model -> where(['email' => $params['email']]);
        if( !is_null($id) && !is_null($idType) )
            $model -> andWhere(['<>', $idType, $id]);
        
        if( !is_null($userType) )
            $model -> andWhere(['userType' => $userType]);
            
        $count = $model -> count();
        //return $model -> createCommand() -> getRawSql();         
        return ($count > 0 ? 1 : 0 ); 			
    }
    // mjchen: this is for supplier registration page
    public function actionIsEmailExists(){
        return $this -> _isEmailExists(Yii::$app->params['USER']['TYPE']['SUPPLIER']);         
    }
    // mjchen: this is for supplier profile edit page
    public function actionIsEmailExistsAgain(){		
        return $this -> _isEmailExists(Yii::$app->params['USER']['TYPE']['SUPPLIER'], Yii::$app->user->identity->id);
    }
    // mjchen: this is for supplier user create/edit page
    public function actionIsUserEmailExists(){		
        return $this -> _isEmailExists(Yii::$app->params['USER']['TYPE']['SUPPLIER']);
    }
    // mjchen: shared function
    private function _isUsernameExists(){		
        $params = Yii::$app -> request -> get();

        if( !is_array($params) || !array_key_exists('supplierId', $params) || !array_key_exists('username', $params) || !array_key_exists('userType', $params) )
            return Yii::$app -> getResponse() -> setStatusCode(500);				

        $count = User::find() ->where(['supplierId' => $params['supplierId'], 'username' => $params['username'], 'userType' => $params['userType']]) ->count();			
        return ($count > 0 ? 1 : 0 ); 			 
    }
    // mjchen: this one for create/update user
    public function actionIsUsernameExistsAgain(){
        return $this -> _isUsernameExists();
    }
	// mjchen: this was for registration and not needed now.
    public function actionIsUsernameExists(){
	    return false; //return $this -> _isUsernameExists();		
    }
    public function actionToggleActive(){
        $params = Yii::$app -> request -> bodyParams;
        $userId = $params['id'];

        $user = User::findOne($userId);
        if( !$user )
            throw new ServerErrorHttpException('User is not found.');

        $user -> active = $user -> active == 1 ? 0 : 1 ;

        if ($user->save() === false)
            Yii::error($user->errors);

        return $user;
    }
}
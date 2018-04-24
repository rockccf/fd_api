<?php
namespace app\commands;

use app\models\Country;
use app\models\User;
use app\models\UserProfile;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{

    /*
     * itemTable: the table for storing authorization items. Defaults to "auth_item".
     * itemChildTable: the table for storing authorization item hierarchy. Defaults to "auth_item_child".
     * assignmentTable: the table for storing authorization item assignments. Defaults to "auth_assignment".
     * ruleTable: the table for storing rules. Defaults to "auth_rule".
     */

    //Bootstrap class to initialize create the necessary permissions and admin user sysadmin
    public function actionInit() {
        $auth = Yii::$app->authManager;

        /*
         * Admin Portal Permissions
         * PERMISSION_ADMIN_CREATE_USER
         *
         * Tenant Portal Permissions
         * PERMISSION_TENANT_CREATE_USER
         *
         * Supplier Portal Permissions
         * PERMISSION_SUPPLIER_CREATE_USER
         */

        // add "createUser" permission
        $createUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_USER');
        $createUser->description = 'Create User';
        $auth->add($createUser);

        // add "updateUser" permission
        $updateUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_USER');
        $updateUser->description = 'Update User';
        $auth->add($updateUser);

        // add "deleteUser" permission
        $deleteUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_USER');
        $deleteUser->description = 'Delete User';
        $auth->add($deleteUser);

        // add "viewUser" permission
        $viewUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_USER');
        $viewUser->description = 'View User';
        $auth->add($viewUser);

        // add "viewUser" permission
        $updateUserPassword = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_USER_PASSWORD');
        $updateUserPassword->description = 'Update User Password';
        $auth->add($updateUserPassword);

        // add "createRole" permission
        $createRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_ROLE');
        $createRole->description = 'Create Role';
        $auth->add($createRole);

        // add "updateRole" permission
        $updateRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_ROLE');
        $updateRole->description = 'Update Role';
        $auth->add($updateRole);

        // add "deleteRole" permission
        $deleteRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_ROLE');
        $deleteRole->description = 'Delete Role';
        $auth->add($deleteRole);

        // add "viewRole" permission
        $viewRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_ROLE');
        $viewRole->description = 'View Role';
        $auth->add($viewRole);

        // add "createDepartment" permission
        $createDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_DEPARTMENT');
        $createDepartment->description = 'Create Department';
        $auth->add($createDepartment);

        // add "updateDepartment" permission
        $updateDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_DEPARTMENT');
        $updateDepartment->description = 'Update Department';
        $auth->add($updateDepartment);

        // add "deleteDepartment" permission
        $deleteDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_DEPARTMENT');
        $deleteDepartment->description = 'Delete Department';
        $auth->add($deleteDepartment);

        // add "viewDepartment" permission
        $viewDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_DEPARTMENT');
        $viewDepartment->description = 'View Department';
        $auth->add($viewDepartment);

        // add "createUomFormula" permission
        $createUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_UOM_FORMULA');
        $createUomFormula->description = 'Create UOM Formula';
        $auth->add($createUomFormula);

        // add "updateUomFormula" permission
        $updateUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_UOM_FORMULA');
        $updateUomFormula->description = 'Update UOM Formula';
        $auth->add($updateUomFormula);

        // add "deleteUomFormula" permission
        $deleteUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_UOM_FORMULA');
        $deleteUomFormula->description = 'Delete UOM Formula';
        $auth->add($deleteUomFormula);

        // add "viewUomFormula" permission
        $viewUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_UOM_FORMULA');
        $viewUomFormula->description = 'View UOM Formula';
        $auth->add($viewUomFormula);

        // add "createTenant" permission
        $createTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_TENANT');
        $createTenant->description = 'Create Tenant';
        $auth->add($createTenant);

        // add "updateTenant" permission
        $updateTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_TENANT');
        $updateTenant->description = 'Update Tenant';
        $auth->add($updateTenant);

        // add "deleteTenant" permission
        $deleteTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_TENANT');
        $deleteTenant->description = 'Delete Tenant';
        $auth->add($deleteTenant);

        // add "viewTenant" permission
        $viewTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_TENANT');
        $viewTenant->description = 'View Tenant';
        $auth->add($viewTenant);

        // add "createTenantItem" permission
        $createTenantItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_TENANT_ITEM');
        $createTenantItem->description = 'Create Tenant Item';
        $auth->add($createTenantItem);

        // add "updateTenantItem" permission
        $updateTenantItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_TENANT_ITEM');
        $updateTenantItem->description = 'Update Tenant Item';
        $auth->add($updateTenantItem);

        // add "deleteTenantItem" permission
        $deleteTenantItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_TENANT_ITEM');
        $deleteTenantItem->description = 'Delete Tenant Item';
        $auth->add($deleteTenantItem);

        // add "viewTenantItem" permission
        $viewTenantItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_TENANT_ITEM');
        $viewTenantItem->description = 'View Tenant Item';
        $auth->add($viewTenantItem);

        // add "createWorkflow" permission
        $createWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_WORKFLOW');
        $createWorkflow->description = 'Create Workflow';
        $auth->add($createWorkflow);

        // add "updateWorkflow" permission
        $updateWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_WORKFLOW');
        $updateWorkflow->description = 'Update Workflow';
        $auth->add($updateWorkflow);

        // add "deleteWorkflow" permission
        $deleteWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_WORKFLOW');
        $deleteWorkflow->description = 'Delete Workflow';
        $auth->add($deleteWorkflow);

        // add "viewWorkflow" permission
        $viewWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_WORKFLOW');
        $viewWorkflow->description = 'View Workflow';
        $auth->add($viewWorkflow);

        // add "createTender" permission
        $createTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_TENDER');
        $createTender->description = 'Create Tender';
        $auth->add($createTender);

        // add "updateTender" permission
        $updateTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_TENDER');
        $updateTender->description = 'Update Tender';
        $auth->add($updateTender);

        // add "deleteTender" permission
        $deleteTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_TENDER');
        $deleteTender->description = 'Delete Tender';
        $auth->add($deleteTender);

        // add "viewTender" permission
        $viewTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_TENDER');
        $viewTender->description = 'View Tender';
        $auth->add($viewTender);
        
        // add "createPurchaseRequest" permission
        $createPurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_PR');
        $createPurchaseRequest->description = 'Create Purchase Request';
        $auth->add($createPurchaseRequest);

        // add "updatePurchaseRequest" permission
        $updatePurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_PR');
        $updatePurchaseRequest->description = 'Update Purchase Request';
        $auth->add($updatePurchaseRequest);

        // add "deletePurchaseRequest" permission
        $deletePurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_PR');
        $deletePurchaseRequest->description = 'Delete Purchase Request';
        $auth->add($deletePurchaseRequest);

        // add "viewPurchaseRequest" permission
        $viewPurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_PR');
        $viewPurchaseRequest->description = 'View Purchase Request';
        $auth->add($viewPurchaseRequest);

        // add "createPurchaseOrder" permission
        $createPurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'CREATE_PO');
        $createPurchaseOrder->description = 'Create Purchase Order';
        $auth->add($createPurchaseOrder);

        // add "updatePurchaseOrder" permission
        $updatePurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'UPDATE_PO');
        $updatePurchaseOrder->description = 'Update Purchase Order';
        $auth->add($updatePurchaseOrder);

        // add "deletePurchaseOrder" permission
        $deletePurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'DELETE_PO');
        $deletePurchaseOrder->description = 'Delete Purchase Order';
        $auth->add($deletePurchaseOrder);

        // add "viewPurchaseOrder" permission
        $viewPurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['ADMIN'].'VIEW_PO');
        $viewPurchaseOrder->description = 'View Purchase Order';
        $auth->add($viewPurchaseOrder);

        // add "HRCBSupport" role and give this role the "createPost" permission
        $support = $auth->createRole('ROLE_ADMIN_HRCB_SUPPORT');
        $support->description = 'HRCB Support';
        $auth->add($support);
        $auth->addChild($support, $createUser);
        $auth->addChild($support, $viewUser);
        $auth->addChild($support, $createRole);
        $auth->addChild($support, $viewRole);

        // add "sysadmin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $admin = $auth->createRole('ROLE_ADMIN_HRCB_ADMINISTRATOR');
        $admin->description = 'HRCB Administrator';
        $auth->add($admin);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        $auth->addChild($admin, $viewUser);
        $auth->addChild($admin, $updateUserPassword);
        $auth->addChild($admin, $createRole);
        $auth->addChild($admin, $updateRole);
        $auth->addChild($admin, $deleteRole);
        $auth->addChild($admin, $viewRole);
        $auth->addChild($admin, $createDepartment);
        $auth->addChild($admin, $updateDepartment);
        $auth->addChild($admin, $deleteDepartment);
        $auth->addChild($admin, $viewDepartment);
        $auth->addChild($admin, $createUomFormula);
        $auth->addChild($admin, $updateUomFormula);
        $auth->addChild($admin, $deleteUomFormula);
        $auth->addChild($admin, $viewUomFormula);
        $auth->addChild($admin, $createTenant);
        $auth->addChild($admin, $updateTenant);
        $auth->addChild($admin, $deleteTenant);
        $auth->addChild($admin, $viewTenant);
        $auth->addChild($admin, $createTenantItem);
        $auth->addChild($admin, $updateTenantItem);
        $auth->addChild($admin, $deleteTenantItem);
        $auth->addChild($admin, $viewTenantItem);
        $auth->addChild($admin, $createWorkflow);
        $auth->addChild($admin, $updateWorkflow);
        $auth->addChild($admin, $deleteWorkflow);
        $auth->addChild($admin, $viewWorkflow);
        $auth->addChild($admin, $createTender);
        $auth->addChild($admin, $updateTender);
        $auth->addChild($admin, $deleteTender);
        $auth->addChild($admin, $viewTender);
        $auth->addChild($admin, $createPurchaseRequest);
        $auth->addChild($admin, $updatePurchaseRequest);
        $auth->addChild($admin, $deletePurchaseRequest);
        $auth->addChild($admin, $viewPurchaseRequest);
        $auth->addChild($admin, $createPurchaseOrder);
        $auth->addChild($admin, $updatePurchaseOrder);
        $auth->addChild($admin, $deletePurchaseOrder);
        $auth->addChild($admin, $viewPurchaseOrder);

        // Assign roles to users. 1 and 2 are IDs returned by IdentityInterface::getId()
        // usually implemented in your User model.
        $user = new User();
        $user->username = 'hrcbadmin';
        $user->setPasswordHash("hrcb2230admin");
        $user->passwordNeverExpires = true;
        $user->email = 'admin@dbix.com.my';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['ADMIN'];
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }

        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Administrator';
        $userProfile->jobTitle = 'HRCB Administrator';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 1;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }

        $auth->assign($admin, $user->getId());

        $user = new User();
        $user->username = 'hrcbsupport';
        $user->setPasswordHash("hrcba312support");
        $user->passwordNeverExpires = true;
        $user->email = 'support@horecabid';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['ADMIN'];
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }

        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Support';
        $userProfile->jobTitle = 'HRCB Support';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 1;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }

        $auth->assign($support, $user->getId());
    }

    //Bootstrap class to initialize and create the necessary permissions for tenant dbix
    public function actionInitTenant()
    {
        $auth = Yii::$app->authManager;

        /*
         * Admin Portal Permissions
         * PERMISSION_ADMIN_CREATE_USER
         *
         * Tenant Portal Permissions
         * PERMISSION_TENANT_CREATE_USER
         *
         * Supplier Portal Permissions
         * PERMISSION_SUPPLIER_CREATE_USER
         */

        // add "createUser" permission
        $createUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_USER');
        $createUser->description = 'Create User';
        $auth->add($createUser);

        // add "updateUser" permission
        $updateUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_USER');
        $updateUser->description = 'Update User';
        $auth->add($updateUser);

        // add "deleteUser" permission
        $deleteUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_USER');
        $deleteUser->description = 'Delete User';
        $auth->add($deleteUser);

        // add "viewUser" permission
        $viewUser = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_USER');
        $viewUser->description = 'View User';
        $auth->add($viewUser);

        // add "viewUser" permission
        $updateUserPassword = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_USER_PASSWORD');
        $updateUserPassword->description = 'Update User Password';
        $auth->add($updateUserPassword);

        // add "createRole" permission
        $createRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_ROLE');
        $createRole->description = 'Create Role';
        $auth->add($createRole);

        // add "updateRole" permission
        $updateRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_ROLE');
        $updateRole->description = 'Update Role';
        $auth->add($updateRole);

        // add "deleteRole" permission
        $deleteRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_ROLE');
        $deleteRole->description = 'Delete Role';
        $auth->add($deleteRole);

        // add "viewRole" permission
        $viewRole = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_ROLE');
        $viewRole->description = 'View Role';
        $auth->add($viewRole);

        // add "createDepartment" permission
        $createDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_DEPARTMENT');
        $createDepartment->description = 'Create Department';
        $auth->add($createDepartment);

        // add "updateDepartment" permission
        $updateDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_DEPARTMENT');
        $updateDepartment->description = 'Update Department';
        $auth->add($updateDepartment);

        // add "deleteDepartment" permission
        $deleteDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_DEPARTMENT');
        $deleteDepartment->description = 'Delete Department';
        $auth->add($deleteDepartment);

        // add "viewDepartment" permission
        $viewDepartment = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_DEPARTMENT');
        $viewDepartment->description = 'View Department';
        $auth->add($viewDepartment);

        // add "createUomFormula" permission
        $createUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_UOM_FORMULA');
        $createUomFormula->description = 'Create UOM Formula';
        $auth->add($createUomFormula);

        // add "updateUomFormula" permission
        $updateUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_UOM_FORMULA');
        $updateUomFormula->description = 'Update UOM Formula';
        $auth->add($updateUomFormula);

        // add "deleteUomFormula" permission
        $deleteUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_UOM_FORMULA');
        $deleteUomFormula->description = 'Delete UOM Formula';
        $auth->add($deleteUomFormula);

        // add "viewUomFormula" permission
        $viewUomFormula = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_UOM_FORMULA');
        $viewUomFormula->description = 'View UOM Formula';
        $auth->add($viewUomFormula);

        // add "createItem" permission
        $createItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_ITEM');
        $createItem->description = 'Create Item';
        $auth->add($createItem);

        // add "updateItem" permission
        $updateItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_ITEM');
        $updateItem->description = 'Update Item';
        $auth->add($updateItem);

        // add "deleteItem" permission
        $deleteItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_ITEM');
        $deleteItem->description = 'Delete Item';
        $auth->add($deleteItem);

        // add "viewItem" permission
        $viewItem = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_ITEM');
        $viewItem->description = 'View Item';
        $auth->add($viewItem);

        // add "updateTenant" permission
        $updateTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_TENANT');
        $updateTenant->description = 'Update Company Profile';
        $auth->add($updateTenant);

        // add "viewTenant" permission
        $viewTenant = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_TENANT');
        $viewTenant->description = 'View Company Profile';
        $auth->add($viewTenant);

        $createWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_WORKFLOW');
        $createWorkflow->description = 'Create Workflow';
        $auth->add($createWorkflow);

        // add "updateWorkflow" permission
        $updateWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_WORKFLOW');
        $updateWorkflow->description = 'Update Workflow';
        $auth->add($updateWorkflow);

        // add "deleteWorkflow" permission
        $deleteWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_WORKFLOW');
        $deleteWorkflow->description = 'Delete Workflow';
        $auth->add($deleteWorkflow);

        // add "viewWorkflow" permission
        $viewWorkflow = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_WORKFLOW');
        $viewWorkflow->description = 'View Workflow';
        $auth->add($viewWorkflow);

        // add "createTender" permission
        $createTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_TENDER');
        $createTender->description = 'Create Tender';
        $auth->add($createTender);

        // add "updateTender" permission
        $updateTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_TENDER');
        $updateTender->description = 'Update Tender';
        $auth->add($updateTender);

        // add "deleteTender" permission
        $deleteTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_TENDER');
        $deleteTender->description = 'Delete Tender';
        $auth->add($deleteTender);

        // add "viewTender" permission
        $viewTender = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_TENDER');
        $viewTender->description = 'View Tender';
        $auth->add($viewTender);

        // add "createPurchaseRequest" permission
        $createPurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_PR');
        $createPurchaseRequest->description = 'Create Purchase Request';
        $auth->add($createPurchaseRequest);

        // add "updatePurchaseRequest" permission
        $updatePurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_PR');
        $updatePurchaseRequest->description = 'Update Purchase Request';
        $auth->add($updatePurchaseRequest);

        // add "deletePurchaseRequest" permission
        $deletePurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_PR');
        $deletePurchaseRequest->description = 'Delete Purchase Request';
        $auth->add($deletePurchaseRequest);

        // add "viewPurchaseRequest" permission
        $viewPurchaseRequest = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_PR');
        $viewPurchaseRequest->description = 'View Purchase Request';
        $auth->add($viewPurchaseRequest);

        // add "createPurchaseOrder" permission
        $createPurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'CREATE_PO');
        $createPurchaseOrder->description = 'Create Purchase Order';
        $auth->add($createPurchaseOrder);

        // add "updatePurchaseOrder" permission
        $updatePurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_PO');
        $updatePurchaseOrder->description = 'Update Purchase Order';
        $auth->add($updatePurchaseOrder);

        // add "deletePurchaseOrder" permission
        $deletePurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'DELETE_PO');
        $deletePurchaseOrder->description = 'Delete Purchase Order';
        $auth->add($deletePurchaseOrder);

        // add "viewPurchaseOrder" permission
        $viewPurchaseOrder = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'VIEW_PO');
        $viewPurchaseOrder->description = 'View Purchase Order';
        $auth->add($viewPurchaseOrder);

        // add "HRCBSupport" role and give this role the "createPost" permission
        $support = $auth->createRole('ROLE_TENANT_1_HRCB_SUPPORT');
        $support->description = 'HRCB Support';
        $auth->add($support);
        $auth->addChild($support, $createUser);
        $auth->addChild($support, $viewUser);
        $auth->addChild($support, $createRole);
        $auth->addChild($support, $viewRole);

        // add "sysadmin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $admin = $auth->createRole('ROLE_TENANT_1_HRCB_ADMINISTRATOR');
        $admin->description = 'HRCB Administrator';
        $auth->add($admin);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        $auth->addChild($admin, $viewUser);
        $auth->addChild($admin, $updateUserPassword);
        $auth->addChild($admin, $createRole);
        $auth->addChild($admin, $updateRole);
        $auth->addChild($admin, $deleteRole);
        $auth->addChild($admin, $viewRole);
        $auth->addChild($admin, $createDepartment);
        $auth->addChild($admin, $updateDepartment);
        $auth->addChild($admin, $deleteDepartment);
        $auth->addChild($admin, $viewDepartment);
        $auth->addChild($admin, $createUomFormula);
        $auth->addChild($admin, $updateUomFormula);
        $auth->addChild($admin, $deleteUomFormula);
        $auth->addChild($admin, $viewUomFormula);
        $auth->addChild($admin, $createItem);
        $auth->addChild($admin, $updateItem);
        $auth->addChild($admin, $deleteItem);
        $auth->addChild($admin, $viewItem);
        $auth->addChild($admin, $updateTenant);
        $auth->addChild($admin, $viewTenant);
        $auth->addChild($admin, $createWorkflow);
        $auth->addChild($admin, $updateWorkflow);
        $auth->addChild($admin, $deleteWorkflow);
        $auth->addChild($admin, $viewWorkflow);
        $auth->addChild($admin, $createTender);
        $auth->addChild($admin, $updateTender);
        $auth->addChild($admin, $deleteTender);
        $auth->addChild($admin, $viewTender);
        $auth->addChild($admin, $createPurchaseRequest);
        $auth->addChild($admin, $updatePurchaseRequest);
        $auth->addChild($admin, $deletePurchaseRequest);
        $auth->addChild($admin, $viewPurchaseRequest);
        $auth->addChild($admin, $createPurchaseOrder);
        $auth->addChild($admin, $updatePurchaseOrder);
        $auth->addChild($admin, $deletePurchaseOrder);
        $auth->addChild($admin, $viewPurchaseOrder);

        // add "sysadmin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        /*$financeHod = $auth->createRole('ROLE_TENANT_1_FINANCE_HOD');
        $financeHod->description = 'Finance HOD';
        $auth->add($financeHod);
        $auth->addChild($financeHod, $createUser);
        $auth->addChild($financeHod, $updateUser);
        $auth->addChild($financeHod, $deleteUser);
        $auth->addChild($financeHod, $viewUser);
        $auth->addChild($financeHod, $updateUserPassword);
        $auth->addChild($financeHod, $createRole);
        $auth->addChild($financeHod, $updateRole);
        $auth->addChild($financeHod, $deleteRole);
        $auth->addChild($financeHod, $viewRole);
        $auth->addChild($financeHod, $createDepartment);
        $auth->addChild($financeHod, $updateDepartment);
        $auth->addChild($financeHod, $deleteDepartment);
        $auth->addChild($financeHod, $viewDepartment);
        $auth->addChild($financeHod, $createUomFormula);
        $auth->addChild($financeHod, $updateUomFormula);
        $auth->addChild($financeHod, $deleteUomFormula);
        $auth->addChild($financeHod, $viewUomFormula);
        $auth->addChild($financeHod, $createItem);
        $auth->addChild($financeHod, $updateItem);
        $auth->addChild($financeHod, $deleteItem);
        $auth->addChild($financeHod, $viewItem);
        $auth->addChild($financeHod, $updateTenant);
        $auth->addChild($financeHod, $viewTenant);
        $auth->addChild($financeHod, $createWorkflow);
        $auth->addChild($financeHod, $updateWorkflow);
        $auth->addChild($financeHod, $deleteWorkflow);
        $auth->addChild($financeHod, $viewWorkflow);
        $auth->addChild($financeHod, $createTender);
        $auth->addChild($financeHod, $updateTender);
        $auth->addChild($financeHod, $deleteTender);
        $auth->addChild($financeHod, $viewTender);
        $auth->addChild($financeHod, $createPurchaseRequest);
        $auth->addChild($financeHod, $updatePurchaseRequest);
        $auth->addChild($financeHod, $deletePurchaseRequest);
        $auth->addChild($financeHod, $viewPurchaseRequest);
        $auth->addChild($financeHod, $createPurchaseOrder);
        $auth->addChild($financeHod, $updatePurchaseOrder);
        $auth->addChild($financeHod, $deletePurchaseOrder);
        $auth->addChild($financeHod, $viewPurchaseOrder);

        // add "sysadmin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $accountHod = $auth->createRole('ROLE_TENANT_1_ACCOUNTING_HOD');
        $accountHod->description = 'Accounting HOD';
        $auth->add($accountHod);
        $auth->addChild($accountHod, $createUser);
        $auth->addChild($accountHod, $updateUser);
        $auth->addChild($accountHod, $deleteUser);
        $auth->addChild($accountHod, $viewUser);
        $auth->addChild($accountHod, $updateUserPassword);
        $auth->addChild($accountHod, $createRole);
        $auth->addChild($accountHod, $updateRole);
        $auth->addChild($accountHod, $deleteRole);
        $auth->addChild($accountHod, $viewRole);
        $auth->addChild($accountHod, $createDepartment);
        $auth->addChild($accountHod, $updateDepartment);
        $auth->addChild($accountHod, $deleteDepartment);
        $auth->addChild($accountHod, $viewDepartment);
        $auth->addChild($accountHod, $createUomFormula);
        $auth->addChild($accountHod, $updateUomFormula);
        $auth->addChild($accountHod, $deleteUomFormula);
        $auth->addChild($accountHod, $viewUomFormula);
        $auth->addChild($accountHod, $createItem);
        $auth->addChild($accountHod, $updateItem);
        $auth->addChild($accountHod, $deleteItem);
        $auth->addChild($accountHod, $viewItem);
        $auth->addChild($accountHod, $updateTenant);
        $auth->addChild($accountHod, $viewTenant);
        $auth->addChild($accountHod, $createWorkflow);
        $auth->addChild($accountHod, $updateWorkflow);
        $auth->addChild($accountHod, $deleteWorkflow);
        $auth->addChild($accountHod, $viewWorkflow);
        $auth->addChild($accountHod, $createTender);
        $auth->addChild($accountHod, $updateTender);
        $auth->addChild($accountHod, $deleteTender);
        $auth->addChild($accountHod, $viewTender);
        $auth->addChild($accountHod, $createPurchaseRequest);
        $auth->addChild($accountHod, $updatePurchaseRequest);
        $auth->addChild($accountHod, $deletePurchaseRequest);
        $auth->addChild($accountHod, $viewPurchaseRequest);
        $auth->addChild($accountHod, $createPurchaseOrder);
        $auth->addChild($accountHod, $updatePurchaseOrder);
        $auth->addChild($accountHod, $deletePurchaseOrder);
        $auth->addChild($accountHod, $viewPurchaseOrder);*/

        // Assign roles to users. 1 and 2 are IDs returned by IdentityInterface::getId()
        // usually implemented in your User model.
        $user = new User();
        $user->username = 'hrcbadmin';
        $user->setPasswordHash("hrcb2230admin");
        $user->passwordNeverExpires = true;
        $user->email = 'admin@dbix.com.my';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 1;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Administrator';
        $userProfile->jobTitle = 'HRCB Administrator';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 2;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        $auth->assign($admin, $user->getId());

        $user = new User();
        $user->username = 'hrcbsupport';
        $user->setPasswordHash("hrcba312support");
        $user->passwordNeverExpires = true;
        $user->email = 'support@horecabid.com';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 1;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Support';
        $userProfile->jobTitle = 'HRCB Support';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 2;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        $auth->assign($support, $user->getId());

        /*$user = new User();
        $user->username = 'financehod';
        $user->setPasswordHash("123456");
        $user->passwordNeverExpires = true;
        $user->email = 'financehod@dbix.com.my';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 1;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'Finance';
        $userProfile->lastName = 'HOD';
        $userProfile->jobTitle = 'Finance HOD';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 3;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        $auth->assign($financeHod, $user->getId());

        $user = new User();
        $user->username = 'accounthod';
        $user->setPasswordHash("123456");
        $user->passwordNeverExpires = true;
        $user->email = 'accounthod@dbix.com.my';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 1;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'Account';
        $userProfile->lastName = 'HOD';
        $userProfile->jobTitle = 'Account HOD';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 4;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        $auth->assign($accountHod, $user->getId());*/
    }

    public function actionCustom() {
        $auth = Yii::$app->authManager;

        $manualPOPrice = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'MANUAL_ENTER_PO_PRICE');
        $manualPOPrice->description = 'Manual Enter PO Price';
        $auth->add($manualPOPrice);

        $updateApprovedPOQuantity = $auth->createPermission(Yii::$app->params['AUTH_ITEM']['PERMISSION_PREFIX']['TENANT'].'UPDATE_APPROVED_PO_QUANTITY');
        $updateApprovedPOQuantity->description = 'Update Approved PO Quantity';
        $auth->add($updateApprovedPOQuantity);
    }

    public function actionCreateUser() {
        $auth = Yii::$app->authManager;

        $user = new User();
        $user->username = 'hrcbadmin';
        $user->setPasswordHash("123456");
        $user->passwordNeverExpires = true;
        $user->email = 'admin@dbix.com.my';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 2;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Administrator';
        $userProfile->jobTitle = 'HRCB Administrator';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 20;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        //$auth->assign($admin, $user->getId());

        $user = new User();
        $user->username = 'hrcbsupport';
        $user->setPasswordHash("123456");
        $user->passwordNeverExpires = true;
        $user->email = 'support@horecabid.com';
        $user->active = true;
        $user->userType = Yii::$app->params['USER']['TYPE']['TENANT'];
        $user->tenantId = 2;
        $user->createdBy = 1;
        $user->updatedBy = 1;
        if (!$user->save()) {
            print_r($user->errors);
        }
        $userProfile = new UserProfile();
        $userProfile->firstName = 'HRCB';
        $userProfile->lastName = 'Support';
        $userProfile->jobTitle = 'HRCB Support';
        $userProfile->userId = $user->id;
        $userProfile->departmentId = 20;
        $userProfile->createdBy = 1;
        $userProfile->updatedBy = 1;
        if (!$userProfile->save()) {
            print_r($userProfile->errors);
        }
        //$auth->assign($support, $user->getId());
    }
}
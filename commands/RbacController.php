<?php
namespace app\commands;

use app\models\Country;
use app\models\Master;
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

        // add "createUser" permission
        $createUser = $auth->createPermission('CREATE_USER');
        $createUser->description = 'Create User';
        $auth->add($createUser);

        // add "updateUser" permission
        $updateUser = $auth->createPermission('UPDATE_USER');
        $updateUser->description = 'Update User';
        $auth->add($updateUser);

        // add "deleteUser" permission
        $deleteUser = $auth->createPermission('DELETE_USER');
        $deleteUser->description = 'Delete User';
        $auth->add($deleteUser);

        // add "viewUser" permission
        $viewUser = $auth->createPermission('VIEW_USER');
        $viewUser->description = 'View User';
        $auth->add($viewUser);

        // add "createRole" permission
        /*$createRole = $auth->createPermission('CREATE_ROLE');
        $createRole->description = 'Create Role';
        $auth->add($createRole);

        // add "updateRole" permission
        $updateRole = $auth->createPermission('UPDATE_ROLE');
        $updateRole->description = 'Update Role';
        $auth->add($updateRole);

        // add "deleteRole" permission
        $deleteRole = $auth->createPermission('DELETE_ROLE');
        $deleteRole->description = 'Delete Role';
        $auth->add($deleteRole);

        // add "viewRole" permission
        $viewRole = $auth->createPermission('VIEW_ROLE');
        $viewRole->description = 'View Role';
        $auth->add($viewRole);*/

        $createMaster = $auth->createPermission('CREATE_MASTER');
        $createMaster->description = 'Create Master';
        $auth->add($createMaster);

        $updateMaster = $auth->createPermission('UPDATE_MASTER');
        $updateMaster->description = 'Update Master';
        $auth->add($updateMaster);

        $deleteMaster = $auth->createPermission('DELETE_MASTER');
        $deleteMaster->description = 'Delete Master';
        $auth->add($deleteMaster);

        $viewMaster = $auth->createPermission('VIEW_MASTER');
        $viewMaster->description = 'View Master';
        $auth->add($viewMaster);

        $createPackage = $auth->createPermission('CREATE_PACKAGE');
        $createPackage->description = 'Create Package';
        $auth->add($createPackage);

        $updatePackage = $auth->createPermission('UPDATE_PACKAGE');
        $updatePackage->description = 'Update Package';
        $auth->add($updatePackage);

        $deletePackage = $auth->createPermission('DELETE_PACKAGE');
        $deletePackage->description = 'Delete Package';
        $auth->add($deletePackage);

        $viewPackage = $auth->createPermission('VIEW_PACKAGE');
        $viewPackage->description = 'View Package';
        $auth->add($viewPackage);

        $createPackage = $auth->createPermission('CREATE_COMPANY_DRAW');
        $createPackage->description = 'Create Company Draw';
        $auth->add($createPackage);

        $deletePackage = $auth->createPermission('DELETE_COMPANY_DRAW');
        $deletePackage->description = 'Delete Company Draw';
        $auth->add($deletePackage);

        $bet = $auth->createPermission('BET');
        $bet->description = 'Bet';
        $auth->add($bet);

        // add "sysadmin" role and give this role the "updatePost" permission
        // as well as the permissions of the "author" role
        $admin = $auth->createRole(Yii::$app->params['AUTH_ITEM']['ROLE']['ADMIN']);
        $admin->description = 'Administrator Role';
        $auth->add($admin);
        $auth->addChild($admin, $createUser);
        $auth->addChild($admin, $updateUser);
        $auth->addChild($admin, $deleteUser);
        $auth->addChild($admin, $viewUser);
        /*$auth->addChild($admin, $createRole);
        $auth->addChild($admin, $updateRole);
        $auth->addChild($admin, $deleteRole);
        $auth->addChild($admin, $viewRole);*/
        $auth->addChild($admin, $createMaster);
        $auth->addChild($admin, $updateMaster);
        $auth->addChild($admin, $deleteMaster);
        $auth->addChild($admin, $viewMaster);

        // Assign roles to users. 1 and 2 are IDs returned by IdentityInterface::getId()
        // usually implemented in your User model.
        $user = new User();
        $user->username = 'sysadmin';
        $user->name = 'System Administrator';
        $user->setPasswordHash("admin123!");
        $user->passwordNeverExpires = true;
        $user->active = 1;
        $user->userType = Yii::$app->params['USER']['TYPE']['ADMIN'];
        if (!$user->save()) {
            print_r($user->errors);
        }
        $auth->assign($admin, $user->getId());

        $user = new User();
        $user->username = 'sysitadmin';
        $user->name = 'IT Administrator';
        $user->setPasswordHash("admin123!");
        $user->passwordNeverExpires = true;
        $user->active = 1;
        $user->userType = Yii::$app->params['USER']['TYPE']['ADMIN'];
        if (!$user->save()) {
            print_r($user->errors);
        }
        $auth->assign($admin, $user->getId());
    }

    //Bootstrap class to initialize and create the necessary permissions for master tbt
    public function actionInitMaster()
    {
        $auth = Yii::$app->authManager;

        $createUser = $auth->getPermission('CREATE_USER');
        $updateUser = $auth->getPermission('UPDATE_USER');
        $deleteUser = $auth->getPermission('DELETE_USER');
        $viewUser = $auth->getPermission('VIEW_USER');

        $createPackage = $auth->getPermission('CREATE_PACKAGE');
        $updatePackage = $auth->getPermission('UPDATE_PACKAGE');
        $deletePackage = $auth->getPermission('DELETE_PACKAGE');
        $viewPackage = $auth->getPermission('VIEW_PACKAGE');

        $master = $auth->createRole(Yii::$app->params['AUTH_ITEM']['ROLE']['MASTER']);
        $master->description = 'Master Role';
        $auth->add($master);
        $auth->addChild($master, $createUser);
        $auth->addChild($master, $updateUser);
        $auth->addChild($master, $deleteUser);
        $auth->addChild($master, $viewUser);
        $auth->addChild($master, $createPackage);
        $auth->addChild($master, $updatePackage);
        $auth->addChild($master, $deletePackage);
        $auth->addChild($master, $viewPackage);
    }

    public function actionInitAgent()
    {
        $auth = Yii::$app->authManager;

        $createUser = $auth->getPermission('CREATE_USER');
        $updateUser = $auth->getPermission('UPDATE_USER');
        $deleteUser = $auth->getPermission('DELETE_USER');
        $viewUser = $auth->getPermission('VIEW_USER');

        $bet = $auth->getPermission('BET');

        $agent = $auth->createRole(Yii::$app->params['AUTH_ITEM']['ROLE']['AGENT']);
        $agent->description = 'Agent Role';
        $auth->add($agent);
        $auth->addChild($agent, $createUser);
        $auth->addChild($agent, $updateUser);
        $auth->addChild($agent, $deleteUser);
        $auth->addChild($agent, $viewUser);
        $auth->addChild($agent, $bet);
    }

    public function actionInitPlayer()
    {
        $auth = Yii::$app->authManager;

        $updateUser = $auth->getPermission('UPDATE_USER');
        $viewUser = $auth->getPermission('VIEW_USER');

        $bet = $auth->getPermission('BET');

        $player = $auth->createRole(Yii::$app->params['AUTH_ITEM']['ROLE']['PLAYER']);
        $player->description = 'Player Role';
        $auth->add($player);
        $auth->addChild($player, $updateUser);
        $auth->addChild($player, $viewUser);
        $auth->addChild($player, $bet);
    }
}
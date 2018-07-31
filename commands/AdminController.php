<?php
namespace app\commands;

use app\models\User;
use Yii;
use yii\console\Controller;
use yii\console\ExitCode;

class AdminController extends Controller
{
    public function actionResetUserPassword($userType, $userId = null, $password = '123456') {
        if (YII_ENV_PROD) {
            echo 'Please do not execute this in a production enviroment.';
            return ExitCode::OK;
        }

        $dbTrans = User::getDb()->beginTransaction();
        try {
            if (!empty($userId)) {
                $user = User::findOne(['id' => $userId]);
                $user->setPasswordHash($password);
                if (!$user->save()) {
                    print_r($user->errors);
                    return ExitCode::UNSPECIFIED_ERROR;
                } else {
                    echo "User ".$user->id." updated.\n";
                }
            } else {
                $users = User::findAll(['userType' => $userType]);
                foreach ($users as $user) {
                    $user->setPasswordHash($password);
                    if (!$user->save()) {
                        print_r($user->errors);
                        return ExitCode::UNSPECIFIED_ERROR;
                    } else {
                        echo "User ".$user->id." updated.\n";
                    }
                }
            }

            $dbTrans->commit();
        } catch (\Throwable $e) {
            $dbTrans->rollBack();
            throw $e;
        }

        return ExitCode::OK;
    }
}
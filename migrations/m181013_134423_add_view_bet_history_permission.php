<?php

use yii\db\Migration;

/**
 * Class m181013_134423_add_view_history_permission
 */
class m181013_134423_add_view_bet_history_permission extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $auth = Yii::$app->authManager;

        // add "viewBetHistory" permission
        $viewBetHistory = $auth->createPermission('VIEW_BET_HISTORY');
        $viewBetHistory->description = 'View Bet History';
        $auth->add($viewBetHistory);

        $masterRole = $auth->getRole(Yii::$app->params['AUTH_ITEM']['ROLE']['MASTER']);
        $auth->addChild($masterRole, $viewBetHistory);

        $agentRole = $auth->getRole(Yii::$app->params['AUTH_ITEM']['ROLE']['AGENT']);
        $auth->addChild($agentRole, $viewBetHistory);

        $playerRole = $auth->getRole(Yii::$app->params['AUTH_ITEM']['ROLE']['PLAYER']);
        $auth->addChild($playerRole, $viewBetHistory);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181013_134423_add_view_history_permission cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181013_134423_add_view_history_permission cannot be reverted.\n";

        return false;
    }
    */
}

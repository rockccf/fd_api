<?php

use yii\db\Migration;

/**
 * Class m181019_144107_feature_bonus
 */
class m181019_144107_feature_bonus extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $sql = 'alter table master';
        $sql .= ' add column 4dBigPrize1 decimal(10,2) not null after betMaxLimit6d,';
        $sql .= ' add column 4dBigPrize2 decimal(10,2) not null after 4dBigPrize1,';
        $sql .= ' add column 4dBigPrize3 decimal(10,2) not null after 4dBigPrize2,';
        $sql .= ' add column 4dBigStarters decimal(10,2) not null after 4dBigPrize3,';
        $sql .= ' add column 4dBigConsolation decimal(10,2) not null after 4dBigStarters,';
        $sql .= ' add column 4dSmallPrize1 decimal(10,2) not null after 4dBigConsolation,';
        $sql .= ' add column 4dSmallPrize2 decimal(10,2) not null after 4dSmallPrize1,';
        $sql .= ' add column 4dSmallPrize3 decimal(10,2) not null after 4dSmallPrize2,';
        $sql .= ' add column 4d4aPrize decimal(10,2) not null after 4dSmallPrize3,';
        $sql .= ' add column 4d4bPrize decimal(10,2) not null after 4d4aPrize,';
        $sql .= ' add column 4d4cPrize decimal(10,2) not null after 4d4bPrize,';
        $sql .= ' add column 4d4dPrize decimal(10,2) not null after 4d4cPrize,';
        $sql .= ' add column 4d4ePrize decimal(10,2) not null after 4d4dPrize,';
        $sql .= ' add column 4d4fPrize decimal(10,2) not null after 4d4ePrize,';
        $sql .= ' add column 3dAbcPrize1 decimal(10,2) not null after 4d4fPrize,';
        $sql .= ' add column 3dAbcPrize2 decimal(10,2) not null after 3dAbcPrize1,';
        $sql .= ' add column 3dAbcPrize3 decimal(10,2) not null after 3dAbcPrize2,';
        $sql .= ' add column 3d3aPrize decimal(10,2) not null after 3dAbcPrize3,';
        $sql .= ' add column 3d3bPrize decimal(10,2) not null after 3d3aPrize,';
        $sql .= ' add column 3d3cPrize decimal(10,2) not null after 3d3bPrize,';
        $sql .= ' add column 3d3dPrize decimal(10,2) not null after 3d3cPrize,';
        $sql .= ' add column 3d3ePrize decimal(10,2) not null after 3d3dPrize,';
        $sql .= ' add column gd4dBigPrize1 decimal(10,2) not null after 3d3ePrize,';
        $sql .= ' add column gd4dBigPrize2 decimal(10,2) not null after gd4dBigPrize1,';
        $sql .= ' add column gd4dBigPrize3 decimal(10,2) not null after gd4dBigPrize2,';
        $sql .= ' add column gd4dBigStarters decimal(10,2) not null after gd4dBigPrize3,';
        $sql .= ' add column gd4dBigConsolation decimal(10,2) not null after gd4dBigStarters,';
        $sql .= ' add column gd4dSmallPrize1 decimal(10,2) not null after gd4dBigConsolation,';
        $sql .= ' add column gd4dSmallPrize2 decimal(10,2) not null after gd4dSmallPrize1,';
        $sql .= ' add column gd4dSmallPrize3 decimal(10,2) not null after gd4dSmallPrize2,';
        $sql .= ' add column gd4d4aPrize decimal(10,2) not null after gd4dSmallPrize3,';
        $sql .= ' add column gd4d4bPrize decimal(10,2) not null after gd4d4aPrize,';
        $sql .= ' add column gd4d4cPrize decimal(10,2) not null after gd4d4bPrize,';
        $sql .= ' add column gd4d4dPrize decimal(10,2) not null after gd4d4cPrize,';
        $sql .= ' add column gd4d4ePrize decimal(10,2) not null after gd4d4dPrize,';
        $sql .= ' add column gd4d4fPrize decimal(10,2) not null after gd4d4ePrize,';
        $sql .= ' add column gd3dAbcPrize1 decimal(10,2) not null after gd4d4fPrize,';
        $sql .= ' add column gd3dAbcPrize2 decimal(10,2) not null after gd3dAbcPrize1,';
        $sql .= ' add column gd3dAbcPrize3 decimal(10,2) not null after gd3dAbcPrize2,';
        $sql .= ' add column gd3d3aPrize decimal(10,2) not null after gd3dAbcPrize3,';
        $sql .= ' add column gd3d3bPrize decimal(10,2) not null after gd3d3aPrize,';
        $sql .= ' add column gd3d3cPrize decimal(10,2) not null after gd3d3bPrize,';
        $sql .= ' add column gd3d3dPrize decimal(10,2) not null after gd3d3cPrize,';
        $sql .= ' add column gd3d3ePrize decimal(10,2) not null after gd3d3dPrize,';
        $sql .= ' add column 5dPrize1 decimal(10,2) not null after gd3d3ePrize,';
        $sql .= ' add column 5dPrize2 decimal(10,2) not null after 5dPrize1,';
        $sql .= ' add column 5dPrize3 decimal(10,2) not null after 5dPrize2,';
        $sql .= ' add column 5dPrize4 decimal(10,2) not null after 5dPrize3,';
        $sql .= ' add column 5dPrize5 decimal(10,2) not null after 5dPrize4,';
        $sql .= ' add column 5dPrize6 decimal(10,2) not null after 5dPrize5,';
        $sql .= ' add column 6dPrize1 decimal(10,2) not null after 5dPrize6,';
        $sql .= ' add column 6dPrize2 decimal(10,2) not null after 6dPrize1,';
        $sql .= ' add column 6dPrize3 decimal(10,2) not null after 6dPrize2,';
        $sql .= ' add column 6dPrize4 decimal(10,2) not null after 6dPrize3,';
        $sql .= ' add column 6dPrize5 decimal(10,2) not null after 6dPrize4;';
        $this->execute($sql);

        $sql = 'alter table user_detail';
        $sql .= ' add column 4dBigPrize1 decimal(10,2) null after bet6d,';
        $sql .= ' add column 4dBigPrize2 decimal(10,2) null after 4dBigPrize1,';
        $sql .= ' add column 4dBigPrize3 decimal(10,2) null after 4dBigPrize2,';
        $sql .= ' add column 4dBigStarters decimal(10,2) null after 4dBigPrize3,';
        $sql .= ' add column 4dBigConsolation decimal(10,2) null after 4dBigStarters,';
        $sql .= ' add column 4dSmallPrize1 decimal(10,2) null after 4dBigConsolation,';
        $sql .= ' add column 4dSmallPrize2 decimal(10,2) null after 4dSmallPrize1,';
        $sql .= ' add column 4dSmallPrize3 decimal(10,2) null after 4dSmallPrize2,';
        $sql .= ' add column 4d4aPrize decimal(10,2) null after 4dSmallPrize3,';
        $sql .= ' add column 4d4bPrize decimal(10,2) null after 4d4aPrize,';
        $sql .= ' add column 4d4cPrize decimal(10,2) null after 4d4bPrize,';
        $sql .= ' add column 4d4dPrize decimal(10,2) null after 4d4cPrize,';
        $sql .= ' add column 4d4ePrize decimal(10,2) null after 4d4dPrize,';
        $sql .= ' add column 4d4fPrize decimal(10,2) null after 4d4ePrize,';
        $sql .= ' add column 3dAbcPrize1 decimal(10,2) null after 4d4fPrize,';
        $sql .= ' add column 3dAbcPrize2 decimal(10,2) null after 3dAbcPrize1,';
        $sql .= ' add column 3dAbcPrize3 decimal(10,2) null after 3dAbcPrize2,';
        $sql .= ' add column 3d3aPrize decimal(10,2) null after 3dAbcPrize3,';
        $sql .= ' add column 3d3bPrize decimal(10,2) null after 3d3aPrize,';
        $sql .= ' add column 3d3cPrize decimal(10,2) null after 3d3bPrize,';
        $sql .= ' add column 3d3dPrize decimal(10,2) null after 3d3cPrize,';
        $sql .= ' add column 3d3ePrize decimal(10,2) null after 3d3dPrize,';
        $sql .= ' add column gd4dBigPrize1 decimal(10,2) null after 3d3ePrize,';
        $sql .= ' add column gd4dBigPrize2 decimal(10,2) null after gd4dBigPrize1,';
        $sql .= ' add column gd4dBigPrize3 decimal(10,2) null after gd4dBigPrize2,';
        $sql .= ' add column gd4dBigStarters decimal(10,2) null after gd4dBigPrize3,';
        $sql .= ' add column gd4dBigConsolation decimal(10,2) null after gd4dBigStarters,';
        $sql .= ' add column gd4dSmallPrize1 decimal(10,2) null after gd4dBigConsolation,';
        $sql .= ' add column gd4dSmallPrize2 decimal(10,2) null after gd4dSmallPrize1,';
        $sql .= ' add column gd4dSmallPrize3 decimal(10,2) null after gd4dSmallPrize2,';
        $sql .= ' add column gd4d4aPrize decimal(10,2) null after gd4dSmallPrize3,';
        $sql .= ' add column gd4d4bPrize decimal(10,2) null after gd4d4aPrize,';
        $sql .= ' add column gd4d4cPrize decimal(10,2) null after gd4d4bPrize,';
        $sql .= ' add column gd4d4dPrize decimal(10,2) null after gd4d4cPrize,';
        $sql .= ' add column gd4d4ePrize decimal(10,2) null after gd4d4dPrize,';
        $sql .= ' add column gd4d4fPrize decimal(10,2) null after gd4d4ePrize,';
        $sql .= ' add column gd3dAbcPrize1 decimal(10,2) null after gd4d4fPrize,';
        $sql .= ' add column gd3dAbcPrize2 decimal(10,2) null after gd3dAbcPrize1,';
        $sql .= ' add column gd3dAbcPrize3 decimal(10,2) null after gd3dAbcPrize2,';
        $sql .= ' add column gd3d3aPrize decimal(10,2) null after gd3dAbcPrize3,';
        $sql .= ' add column gd3d3bPrize decimal(10,2) null after gd3d3aPrize,';
        $sql .= ' add column gd3d3cPrize decimal(10,2) null after gd3d3bPrize,';
        $sql .= ' add column gd3d3dPrize decimal(10,2) null after gd3d3cPrize,';
        $sql .= ' add column gd3d3ePrize decimal(10,2) null after gd3d3dPrize,';
        $sql .= ' add column 5dPrize1 decimal(10,2) null after gd3d3ePrize,';
        $sql .= ' add column 5dPrize2 decimal(10,2) null after 5dPrize1,';
        $sql .= ' add column 5dPrize3 decimal(10,2) null after 5dPrize2,';
        $sql .= ' add column 5dPrize4 decimal(10,2) null after 5dPrize3,';
        $sql .= ' add column 5dPrize5 decimal(10,2) null after 5dPrize4,';
        $sql .= ' add column 5dPrize6 decimal(10,2) null after 5dPrize5,';
        $sql .= ' add column 6dPrize1 decimal(10,2) null after 5dPrize6,';
        $sql .= ' add column 6dPrize2 decimal(10,2) null after 6dPrize1,';
        $sql .= ' add column 6dPrize3 decimal(10,2) null after 6dPrize2,';
        $sql .= ' add column 6dPrize4 decimal(10,2) null after 6dPrize3,';
        $sql .= ' add column 6dPrize5 decimal(10,2) null after 6dPrize4;';
        $this->execute($sql);

        $sql = 'alter table bet modify extra4dCommRate decimal(10,2) null, modify extra6dCommRate decimal(10,2) null, modify extraGdCommRate decimal(10,2) null;';
        $this->execute($sql);

        $sql = 'alter table user_detail modify extra4dCommRate decimal(10,2) null, modify extra6dCommRate decimal(10,2) null, modify extraGdCommRate decimal(10,2) null;';
        $this->execute($sql);

        $sql = 'alter table bet change totalCollect totalSuperiorBonus decimal(10,3) null;';
        $this->execute($sql);

        $sql = 'alter table bet_detail change totalCollect totalSuperiorBonus decimal(10,3) null;';
        $this->execute($sql);

        $sql = 'alter table bet_detail_win';
        $sql .= ' add column superiorWinPrizeAmount decimal(10,3) null after totalWin,';
        $sql .= ' add column superiorBonus decimal(10,3) null after superiorWinPrizeAmount;';
        $this->execute($sql);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m181019_144107_feature_bonus cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181019_144107_feature_bonus cannot be reverted.\n";

        return false;
    }
    */
}

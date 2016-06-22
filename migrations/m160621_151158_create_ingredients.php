<?php

use yii\db\Migration;

/**
 * Handles the creation for table `ingredients`.
 */
class m160621_151158_create_ingredients extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_general_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%ingredients}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'visible' => $this->boolean()->defaultValue(true)
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%ingredients}}');
    }
}

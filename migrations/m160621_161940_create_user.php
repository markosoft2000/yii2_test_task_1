<?php

use yii\db\Migration;

/**
 * Handles the creation for table `user`.
 */
class m160621_161940_create_user extends Migration
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

        $this->createTable('{{%user}}', [
            'id' => $this->primaryKey()->notNull(),
            'login' => $this->string(40)->notNull(),
            'password' => $this->string(40)->notNull(),
            'email' => $this->string(100)->notNull(),
            'nickname' => $this->string(255)->notNull(),
            'about' => $this->text(),
        ], $tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%user}}');
    }
}

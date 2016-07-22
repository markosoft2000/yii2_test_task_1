<?php

use yii\db\Migration;

/**
 * Handles the creation for table `dishes`.
 */
class m160621_153132_create_dishes extends Migration
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

        $this->createTable('{{%dishes}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string()->notNull()->unique(),
            'visible' => $this->boolean()->defaultValue(true)
        ], $tableOptions);

        //table links dishes and ingredients
        $this->createTable('{{%ingredient_dish}}', [
            'ingredient_id' => $this->integer(),
            'dish_id' => $this->integer()
        ], $tableOptions);

        $this->createIndex('FK_ingredient', '{{%ingredient_dish}}', 'ingredient_id');
        $this->addForeignKey(
            'FK_ingredient_dish', '{{%ingredient_dish}}', 'ingredient_id', '{{%ingredients}}', 'id', 'RESTRICT', 'CASCADE'
        );

        $this->createIndex('FK_dish', '{{%ingredient_dish}}', 'dish_id');
        $this->addForeignKey(
            'FK_dish_ingredient', '{{%ingredient_dish}}', 'dish_id', '{{%dishes}}', 'id', 'RESTRICT', 'CASCADE'
        );
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('{{%ingredient_dish}}');
        $this->dropTable('{{%dishes}}');
    }
}

<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ingredient_dish}}".
 *
 * @property integer $ingredient_id
 * @property integer $dish_id
 *
 * @property Dishes $dish
 * @property Ingredients $ingredient
 */
class IngredientDish extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ingredient_dish}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['ingredient_id', 'dish_id'], 'integer'],
            [['dish_id'], 'exist', 'skipOnError' => true, 'targetClass' => Dishes::className(), 'targetAttribute' => ['dish_id' => 'id']],
            [['ingredient_id'], 'exist', 'skipOnError' => true, 'targetClass' => Ingredients::className(), 'targetAttribute' => ['ingredient_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'ingredient_id' => 'Ingredient ID',
            'dish_id' => 'Dish ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getDish()
    {
        return $this->hasOne(Dishes::className(), ['id' => 'dish_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIngredient()
    {
        return $this->hasOne(Ingredients::className(), ['id' => 'ingredient_id']);
    }
}

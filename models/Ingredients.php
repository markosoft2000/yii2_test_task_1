<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "{{%ingredients}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $visible
 *
 * @property IngredientDish[] $ingredientDishes
 */
class Ingredients extends \yii\db\ActiveRecord
{
    /**
     * Статус блюда: доступное.
     */
    const STATUS_VISIBLE = 1;
    /**
     * Статус блюда: скрытое.
     */
    const STATUS_HIDDEN = 0;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%ingredients}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['visible'], 'integer'],
            [['name'], 'string', 'max' => 255],
            [['name'], 'unique'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Наименование',
            'visible' => 'Видимость',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIngredientDishes()
    {
        return $this->hasMany(IngredientDish::className(), ['ingredient_id' => 'id']);
    }
}

<?php

namespace app\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\helpers\ArrayHelper;
use yii\web\NotFoundHttpException;

/**
 * This is the model class for table "{{%dishes}}".
 *
 * @property integer $id
 * @property string $name
 * @property integer $visible
 *
 * @property IngredientDish[] $ingredientDishes
 */
class Dishes extends \yii\db\ActiveRecord
{
    /**
     * Статус блюда: доступное.
     */
    const STATUS_VISIBLE = 1;
    /**
     * Статус блюда: скрытое.
     */
    const STATUS_HIDDEN = 0;

    const FILTER_PARTIAL = 1;
    const FILTER_ALL = 0;
    const FILTER_FULL_MATCH = 2;

    /**
     * Список ингридиентов, закреплённых за блюдом.
     * @var array
     */
    protected $ingredients = [];

    /**
     * Устанавлиает ингридиенты блюда.
     * @param $ingredientsId
     */
    public function setIngredients($ingredientsId)
    {
        $this->ingredients = (array) $ingredientsId;
    }

    /**
     * Возвращает массив идентификаторов ингридиентов.
     */
    public function getIngredients()
    {
        return ArrayHelper::getColumn(
            $this->getIngredientDishes()->all(), 'ingredient_id'
        );
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%dishes}}';
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
            [['ingredients'], 'safe'],
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
     * Возвращает доступные блюда
     * @return ActiveDataProvider
     */
    public function getVisibleDishes()
    {
        return new ActiveDataProvider([
            'query' => Dishes::find()
                ->where(['visible' => self::STATUS_VISIBLE])
                ->orderBy(['id' => SORT_ASC])
        ]);
    }

    /**
     * Возвращает модель блюда.
     * @param int $id
     * @throws NotFoundHttpException в случае, когда блюдо не найдено или скрыто
     * @return \yii\db\ActiveQuery
     */
    public function getDishes($id)
    {
        if (
            ($model = Dishes::findOne($id)) !== null &&
            $model->isVisible()
        ) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested post does not exist.');
        }
    }

    /**
     * Проверка доступности блюда.
     * @return bool
     */
    protected function isVisible()
    {
        return $this->visible === self::STATUS_VISIBLE;
    }

    /**
     * Возвращает блюда только с ингридиентами,
     * @param $visibleDishes - доступность блюда
     * @param $minIngredientCount - минимальное количество ингридиентов в блюде
     * @param $ingredient - массив ID ингридиентов для фильтрации (пустой массив - фильтрация отключена)
     * @param $filter - используем константы с префиксом FILTER_ из класса Dishes для фильтрации блюд
     * @return ActiveDataProvider
     */
    public function getFullIngredientDishes($visibleDishes = false, $minIngredientCount = 0, $ingredient = [], $filter = self::FILTER_ALL)
    {
        $queryString = "
            SELECT `d`. * , COUNT( `di`.`ingredient_id` ) as `total`
            FROM `tbl_dishes` AS `d`
            LEFT JOIN `tbl_ingredient_dish` AS `di` ON `di`.`dish_id` = `d`.`id`
            LEFT JOIN `tbl_ingredients` AS `i` ON `i`.`id` = `di`.`ingredient_id`
            WHERE `i`.`visible` = 1 {{visibleDishes}} {{partial}}
            AND `d`.`id` NOT IN (
                SELECT `di2`.`dish_id` FROM `tbl_ingredient_dish` `di2` WHERE `di2`.`ingredient_id`IN (
                    SELECT `i2`.`id` FROM `tbl_ingredients` `i2` WHERE `i2`.`visible` = 0))
            GROUP BY `d`.`id`
            HAVING `total` >= $minIngredientCount {{full}}";

        if ($visibleDishes) {
            $queryString = str_replace('{{visibleDishes}}', ' AND `d`.`visible` = 1', $queryString);
        } else {
            $queryString = str_replace('{{visibleDishes}}', '', $queryString);
        }

        if (self::FILTER_PARTIAL == $filter) {
            $queryString = str_replace('{{partial}}', " AND `i`.`id` IN ({{ingredients}})", $queryString);
            $queryString = str_replace('{{full}}', "", $queryString);
        } elseif (self::FILTER_FULL_MATCH == $filter) {
            $queryString = str_replace('{{partial}}', "", $queryString);
            $queryString = str_replace('{{full}}',
                " AND `total` = (
                    SELECT COUNT(`di2`.`ingredient_id`)
                    FROM `tbl_ingredient_dish` AS `di2`
                    WHERE `di2`.`dish_id` = `d`.`id` AND `di2`.`ingredient_id` IN ({{ingredients}})
                    )", $queryString);
        } else {
            $queryString = str_replace('{{partial}}', "", $queryString);
            $queryString = str_replace('{{full}}', "", $queryString);
        }

        if (is_array($ingredient) && count($ingredient)) {
            $ingredient = implode(',', $ingredient);

            $queryString = str_replace('{{ingredients}}', " $ingredient", $queryString);
        } else {
            $queryString = str_replace('{{ingredients}}', '0', $queryString);
        }

        $queryString .= (self::FILTER_PARTIAL == $filter) ? " ORDER BY `total` DESC, `d`.`id` ASC" : '';


        return new ActiveDataProvider([
            'query' => $this->findBySql($queryString)
        ]);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getIngredientDishes()
    {
        return $this->hasMany(IngredientDish::className(), ['dish_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        IngredientDish::deleteAll(['dish_id' => $this->id]);
        $values = [];
        foreach ($this->ingredients as $id) {
            $values[] = [$this->id, $id];
        }
        self::getDb()->createCommand()
            ->batchInsert(IngredientDish::tableName(), ['dish_id', 'ingredient_id'], $values)->execute();

        parent::afterSave($insert, $changedAttributes);
    }
}

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
     * Возвращает доступные блюда только с доступными ингридиентами
     * @return ActiveDataProvider
     */
    public function getVisibleIngredientDishes()
    {
        /*return new ActiveDataProvider([
            'query' => Dishes::find()
        ]);*/
        /*$q = Dishes::find()
            ->where(['visible' => self::STATUS_VISIBLE])
            ->orderBy(['id' => SORT_ASC])->all();
        $q = Ingredients::find()
            ->where(['visible' => Ingredients::STATUS_VISIBLE])
            ->all();*/

        /*
            SELECT `d`.name, COUNT(c.`ingredient_id`) as ing
            FROM `tbl_dishes` as `d`, `tbl_ingredient_dish` as `c`
            WHERE c.`dish_id` = d.`id`
            GROUP BY d.`id`
            HAVING COUNT(c.`ingredient_id`) = (
                SELECT COUNT(`ingredient_id`) FROM `tbl_ingredient_dish` as `c2`
	            WHERE `c2`.`dish_id` = `d`.`id` AND `c2`.`ingredient_id` IN (SELECT `i`.`id` FROM `tbl_ingredients` as `i` WHERE `i`.`visible` = 1)
            )
        */

        $queryString = "
            SELECT `d`.*, COUNT(c.`ingredient_id`) as ing
            FROM `tbl_dishes` as `d`, `tbl_ingredient_dish` as `c`
            WHERE c.`dish_id` = d.`id`
            GROUP BY d.`id`
            HAVING COUNT(c.`ingredient_id`) = (
                SELECT COUNT(`ingredient_id`) FROM `tbl_ingredient_dish` as `c2`
	            WHERE `c2`.`dish_id` = `d`.`id` AND `c2`.`ingredient_id` IN (
	              SELECT `i`.`id` FROM `tbl_ingredients` as `i` WHERE `i`.`visible` = 1)
            )";

        return new ActiveDataProvider([
            'query' => $this->findBySql($queryString)
        ]);


        $subQuery = (new Query)
            ->select(['`c2`.`dish_id` as `sq_dish_id`, COUNT(c2.`ingredient_id`) as `current_cnt`'])
            ->from(IngredientDish::tableName() . ' `c2`')
            ->leftJoin(Ingredients::tableName() . ' `i`', '`i`.`id` = `c2`.`ingredient_id`')
            ->where('`i`.`visible` = 1')
            ->groupBy('`c2`.`dish_id`');
            //->all();

        /*$q = (new Query)
            ->select(['`d`.`name`'])
            ->from(Dishes::tableName() . ' `d`')
            ->leftJoin(IngredientDish::tableName() . ' `c`', '`d`.`id` = `c`.`dish_id`')
            ->groupBy('`d`.`id`')
            ->having(['COUNT(`c`.`ingredient_id`)' => (
                    (new Query)
                        ->select(['COUNT(c2.`ingredient_id`)'])
                        ->from(IngredientDish::tableName() . ' `c2`')
                        ->leftJoin(Ingredients::tableName() . ' `i`', '`i`.`id` = `c2`.`ingredient_id`')
                        //->where('`c2`.`dish_id` = `d`.`id`')
                        ->where('`i`.`visible` = 1')
                        ->groupBy('`c2`.`dish_id`')
                        ->one()
                )
                //4
            ])
            ->all();*/
        $q = (new Query)
            ->select(['`d`.`id`', '`d`.`name`', 'COUNT(c.`ingredient_id`) as `total_cnt`', '`sq_dish_id`', '`sq`.`current_cnt`'])
            ->from([IngredientDish::tableName() . ' `c`'])
            ->leftJoin(Ingredients::tableName() . ' `i`', '`i`.`id` = `c`.`ingredient_id`')
            ->leftJoin(Dishes::tableName() . ' `d`', '`d`.`id` = `c`.`dish_id`')
            ->leftJoin(['sq' => $subQuery], '`sq`.`sq_dish_id` = `c`.`dish_id`')
            ->groupBy('`c`.`dish_id`')
            //->having(['COUNT(`c`.`ingredient_id`)' => (2)])
            //->having(['`total_cnt`' => '`current_cnt`'])
            ->having(['=', '`total_cnt`', ('4')])
            ->all();

        //var_export($subQuery);
        echo "<hr>";
        var_export($q);
        die('here');
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

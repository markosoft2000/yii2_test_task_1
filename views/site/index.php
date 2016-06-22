<?php

use yii\helpers\Html;
use \yii\helpers\ArrayHelper;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model app\models\Dishes */
/* @var $ingredients yii\db\ActiveRecord[] */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $result boolean */
/* @var $message string */
/* @var $ingredientRequested array */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'ingredients')->listBox(//checkboxList
        ArrayHelper::map($ingredients, 'id', 'name'),
        [
            'multiple' => true
        ]
    ) ?>

    <div class="form-group">
        <?= Html::submitButton('Show!', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

    <hr>
    <strong>Выбранные ингридиенты:</strong><br>
    <?php
        if (count($ingredientRequested)) {
            foreach ($ingredientRequested as $k => $v) {
                echo "<i>{$v['name']}</i><br>";
            }
        } else {
            echo 'Выберите ингредиенты из списка';
        }
    ?>

    <hr>
    <strong>Результаты поиска:</strong><br>

    <?php if ($result) { ?>
            <i><?php
                if ($isFullMatch) {
                    echo "<font style='color: #00aa00'>Полное совпадение</font>";
                } else {
                    echo "<font style='color: #FFAA00'>Частичное совпадение</font>";
                }
            ?></i>
            <?= \yii\grid\GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    ['class' => 'yii\grid\SerialColumn'],

                    'name',
                    'visible',

                    ['class' => 'yii\grid\ActionColumn'],
                ],
            ]); ?>
        <?php    } else {?>
         <?= $message ?>
    <?php    }
    ?>

</div>

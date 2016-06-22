<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Dishes */
/* @var $ingredients app\models\Ingredients */

$this->title = 'Update Dishes: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Dishes', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Редактирование';
?>
<div class="dishes-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'ingredients' => $ingredients,
    ]) ?>

    <hr>
    <strong>Ингридиенты:</strong>
    <br>
    <hr>

</div>

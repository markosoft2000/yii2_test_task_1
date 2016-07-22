<?php
/* @var $this yii\web\View */

use \yii\helpers\Url;
?>
<h1>Админка</h1>

<p>
    <ul>
        <li><a href="<?= Url::to(['/ingredients'])?>" target="_blank">Ингридиенты</a></li>
        <li><a href="<?= Url::to(['/dishes'])?>" target="_blank">Блюда</a></li>
        <li><a href="<?= Url::to(['/user'])?>" target="_blank">Пользователи</a></li>
    </ul>
</p>

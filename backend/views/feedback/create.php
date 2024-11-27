<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var common\models\Feedback $model */

$this->title = 'Create Feedback';
$this->params['breadcrumbs'][] = ['label' => 'Feedbacks', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="feedback-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>

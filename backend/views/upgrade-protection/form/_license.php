<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\DateControl;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $model common\models\License */

echo Html::tag('strike', Yii::$app->formatter->asDate($model->upgrade_protection_end_date), ['id' => 'up-date-'. $model->id, 'style' => 'display: none;', 'class' => '']);

echo Html::activeHiddenInput($model, '[' . $model->id . ']up_min_date');

echo Html::activeHiddenInput($model, '[' . $model->id . ']up_max_date');

echo $form->field($model, '[' . $model->id . ']upgrade_protection_end_date')->widget(DateControl::classname(), ['widgetOptions' => ['type' => 1, 'options' => ['disabled' => 1, 'style' => 'width: 130px;']]])->label(false);

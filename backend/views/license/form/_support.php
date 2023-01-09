<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\widgets\CheckboxX;
use common\widgets\DateControl;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $models array of common\models\License */

Card::begin([
	'icon' => Html::ICON_SUPPORT,
	'title' => Yii::t('app', 'Support'),
	'show' => $model->include_support,
]);

echo $form->field($model, 'include_support', ['showLabels' => false])->widget(CheckboxX::classname(),['autoLabel' => true, 'options' => ['class' => 'form-control' . ($model->include_support ? '' : ' collapsed'), 'data-toggle' => 'collapse', 'data-target' => '.include_support']]);

echo Html::beginTag('div', ['class' => 'form-row include_support collapse' . ($model->include_support ? ' show' : '')]);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'support_end_date')->widget(DateControl::classname(), ['widgetOptions' => ['pluginOptions' => ['startDate' => new JsExpression('new Date()')]]]);

	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'max_count_request')->textInput(['maxlength' => true]);

	echo Html::endTag('div');

echo Html::endTag('div');

Card::end();

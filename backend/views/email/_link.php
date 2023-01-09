<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Email */

$attributes = [];

if ($value = $model->getLicenses()->count()) {
	$attributes[] = [
		'label' => ($value > 1) ? Yii::t('app', 'Licenses') : Yii::t('app', 'License'),
		'value' => $value,
	];
}

if ($value = $model->getUpgradeProtections()->count()) {
	$attributes[] = [
		'label' => Yii::t('app', 'AMS'),
		'value' => $value,
	];
}

if ($value = $model->getSupports()->count()) {
	$attributes[] = [
		'label' => ($value > 1) ? Yii::t('app', 'Supports') : Yii::t('app', 'Support'),
		'value' => $value,
	];
}

if ($value = $model->getDeliveries()->count()) {
	$attributes[] = [
		'label' => ($value > 1) ? Yii::t('app', 'Deliveries') : Yii::t('app', 'Delivery'),
		'value' => $value,
	];
}

$attributes[] = [
	'attribute' => 'is_valid',
	'format' => 'boolean',
];

if ($value = $model->created_at) {
	$attributes[] = [
		//'label' => Yii::t('app', 'Added'),
		'attribute' => 'created_at',
		'format' => 'date',
	];
}

if ($user = $model->user) {
	$attributes[] = [
		'attribute' => 'owner_id',
		'value' => $user->name,
	];
}

if (!$model->isValid()) {
	Html::addCssClass($options, $model->getCssClass('invalid'));
}

$content = DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
	'striped' => false,
]);

$options = ArrayHelper::merge([
	'data-pjax' => 0,
	'tabindex' => '0',
	'title' => Html::encode($model->address),
	'role' => 'button',
	'data-toggle' => 'popover',
	'data-content' => $content,
	'data-html' => 'true',
	'data-container' => 'body',
	'data-trigger' => 'hover',
], $options);

echo Html::a(Html::encode($model->address), $model->route, $options);


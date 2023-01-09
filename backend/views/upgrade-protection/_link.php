<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */


$attributes = [];

$attributes[] = [
	'label' => Yii::t('app', 'Owner'),
	'attribute' => 'owner_name',
];

if ($email = $model->email) {
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $email->address,
	];
}

if ($value = $model->company) {
	$attributes[] = [
		'attribute' => 'company',
		'value' => $value,
	];
}
/*
$attributes[] = [
	'label' => Yii::t('app', 'Start'),
	'attribute' => 'buy_date',
	'format' => 'date',
];

$attributes[] = [
	'label' => Yii::t('app', 'End'),
	'attribute' => 'end_date',
	'format' => 'date',
];	
*/
$attributes[] = [
	'attribute' => 'product_info_id',
	'value' => ArrayHelper::getValue($model, 'productInfo.commercialVersion'),
];

if ($user = $model->user) {
	$attributes[] = [
		'attribute' => 'owner_id',
		'value' => $user->name,
	];
}

if ($model->isExpired()) {
	Html::addCssClass($options, $model->getCssClass('expired'));
}

$content = DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
	'striped' => false,
]);

$options = ArrayHelper::merge([
	'data-pjax' => 0,
	'tabindex' => '0',
	'title' => Html::encode($model->name),
	'role' => 'button',
	'data-toggle' => 'popover',
	'data-content' => $content,
	'data-html' => 'true',
	'data-container' => 'body',
	'data-trigger' => 'hover',
], $options);

echo Html::a(Html::encode($model->name), $model->route, $options);


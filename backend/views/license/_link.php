<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\License */

Html::addCssClass($options, $model->getCssClass($model->getLicenseTypeName()));

$attributes = [];

/*
$attributes[] = [
	'attribute' => 'license_type_id',
	'value' => $model->getLicenseTypeName(),
];
*/

if ($value = $model->company) {
	$attributes[] = [
		'attribute' => 'company',
		'value' => $value,
	];
}

$attributes[] = [
	'label' => Yii::t('app', 'Owner'),
	'attribute' => 'owner_name',
];

if ($value = $model->emailAddress) {
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $value,
	];
}
if ($value = $model->dateLimit) {
	$attributes[] = [
		'attribute' => 'dateLimit',
		'value' => $value,
	];
	if($model->isExpired()){
		Html::addCssClass($options, $model->getCssClass('expired'));
	}
}

$attributes[] = [
	'label' => Yii::t('app', 'Version'),
	'value' => ArrayHelper::getValue($model, 'productInfo.commercialVersion'),
];

$attributes[] = [
	'label' => Yii::t('app', 'Issued'),
	'attribute' => 'created_at',
	'format' => 'datetime',
];

if ($user = $model->user) {
	$attributes[] = [
		'attribute' => 'owner_id',
		'value' => $user->name,
	];
}

if ($model->hasLicenseInvalidations()) {
	$attributes[] = [
		'label' => Yii::t('app', 'Invalidated'),
		'value' => ArrayHelper::getValue($model, 'licenseInvalidations.0.created_at'),
		'format' => 'date',
	];
	Html::addCssClass($options, $model->getCssClass('invalidated'));
}

$content = DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
	'striped' => false,
]);

$options = ArrayHelper::merge([
	'data-pjax' => 0,
	'tabindex' => '0',
	'title' => Html::encode($model->getLicenseTypeName()),
	'role' => 'button',
	//'data-placement' => 'top',
	'data-toggle' => 'popover',
	'data-content' => $content,
	'data-html' => 'true',
	'data-container' => 'body',
	'data-trigger' => 'hover',
], $options);

echo Html::a(Html::encode($model->id), $model->route, $options);


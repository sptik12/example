<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$attributes = [];

$attributes[] = [
	'attribute' => 'id',
	'value' => Yii::$app->request->isAjax ? Html::a($model->encodedId, $model->route, ['data-pjax' => 0, 'title' => Yii::t('app', 'Open License Details')]) : $model->encodedId,
	'format' => 'raw',
];

$attributes[] = [
	'attribute' => 'issued',
	'valueColOptions' => ['title' => ArrayHelper::getValue($model, 'initialProductInfo.fullVersion')],
];

$attributes[] = [
	'label' => Yii::t('app', 'AMS'), 
	'value' => $model->getAMSEndDateMessage(),
	'valueColOptions' => ['class' => $model->upgradeProtectionCss, 'title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')],
];

ViewCard::begin($options);

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);

ViewCard::end(); 

$attributes = [];

if($value = $model->company){
	$attributes[] = [
		'attribute' => 'company',
		'value' => Html::encode($value). $model->getSearchLink('company'),
		'format' => 'raw',
	];
}

if($value = $model->owner_name){
	$attributes[] = [
		'attribute' => 'owner_name',
		'value' => Html::encode($value) . $model->getSearchLink('owner_name'),
		'format' => 'raw',
	];
}

if($email = $model->email){
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $email->getLink() . $email->getLicenseCount(['class' => 'badge badge-light ml-1']),
		'format' => 'raw',
	];
}

ViewCard::begin([]);

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);

ViewCard::end(); 

$attributes = [];

if ($model->hasDateLimit()) {
	$attributes[] = [
		'attribute' => 'not_after_date',
		'label' => $model->isExpired() ? Yii::t('app', 'Expired on') : Yii::t('app', 'Valid until'), 
		'format' => 'date',
	];
}

if ($value = $model->getSupportEndDateMessage()){
	$attributes[] = [
		'label' => Yii::t('app', 'Support'), 
		'value' => $value,
		'valueColOptions' => ['class' => $model->getSupportCss()],
	];
}

/*
if ($model->details) {
	$attributes[] = [
		'attribute' => 'details',
	];
}
*/

if($user=$model->user){
	$attributes[] = [
		'attribute' => 'owner_id',
		'value' => $user->name,
	];
}

if ($payment = $model->payment){
	$attributes[] = [
		'attribute' => 'payment_id',
		'value' => Html::a($payment->id, Url::toRoute($payment->route)),
		'format' => 'raw'
	];
}
if ($attributes){

	ViewCard::begin([]);

	echo DetailView::widget([
		'model' => $model,
		'attributes' => $attributes,
	]);

	ViewCard::end(); 
}

<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;

/* @var $this yii\web\View */
/* @var $model common\models\Delivery */

$attributes = [];

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

if ($email = $model->email) {
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $email->address,
	];
}

if ($value = $model->getLicenseTypeName()) {
	'attribute' => 'license_type_id',
	'label' => Yii::t('app', 'Type'),
	'value' => $value,
}

$attributes[] = [
	'label' => Yii::t('app', 'Issued'),
	'attribute' => 'created_at',
	'format' => 'datetime',
];	

ViewCard::begin(['title' => Html::encode($model->id)]);

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
	'striped' => false,
]);

ViewCard::end(); 


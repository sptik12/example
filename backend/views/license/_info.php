<?php

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$attributes = [];

if($value = $model->company){
	$attributes[] = [
		'attribute' => 'company',
	];
}

if($value = $model->owner_name){
	$attributes[] = [
		'attribute' => 'owner_name',
	];
}

if($email = $model->email){
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $email->address,
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

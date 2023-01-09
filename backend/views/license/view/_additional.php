<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$attributes = [];

if ($model->details) {
	$attributes[] = [
		'attribute' => 'details',
	];
}

if ($attachments = $model->attachments){

	$attributes[] = [
			'label' => (count($attachments) > 1) ? Yii::t('app', 'Attachments') : Yii::t('app', 'Attachment'),
			'value' => implode(', ', ArrayHelper::getColumn($attachments, 'link')),
			'format' => 'raw',
		];
}

if ($attributes){
	LightCard::begin([
		'icon' => 'plus-square',
		'title' => Yii::t('app', 'Additional'),
		'show' => true,
	]);
	
	echo DetailView::widget([
		'model' => $model,
		'attributes' => $attributes,
	]);

	LightCard::end(); 
}

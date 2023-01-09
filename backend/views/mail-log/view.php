<?php

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\MailLog */

$this->title = $model->name;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mail Log'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

ViewCard::begin([
	'icon' => Html::ICON_LOG,
]);

$attributes = [];

$attributes[] = [
	'attribute' => 'created_at',
	'format' => 'date',
];

$attributes[] = [
	'attribute' => 'email',
	'format' => 'email',
];

if ($delivery = $model->delivery) {
	$attributes[] = [
		'attribute' => 'delivery_id',
		'value' => $delivery->link,
		'format' => 'raw',
	];
}

if ($model->message){
	$attributes[] = [
		'label' => Yii::t('app', 'Source'),
		'value' => Html::a(Yii::t('app', 'View'), ['source', 'id' => $model->id], ['title' => Yii::t('yii', 'View Source'), 'data-pjax' => 0, 'data-toggle' => 'modal', 'data-target' => '#main-modal']),
		'format' => 'raw',
	];
}

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);

ViewCard::end();
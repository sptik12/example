<?php

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Email */


ViewCard::begin($options);

$attributes = [];

$attributes[] = [
	'attribute' => 'email',
	'value' => Yii::$app->request->isAjax ? Html::a(Html::encode($model->address), $model->route, ['data-pjax' => 0, 'title' => Yii::t('app', 'Open Email Details')]) : $model->address,
	'format' => Yii::$app->request->isAjax ? 'raw' : 'email',
];

$attributes[] = [
	'attribute' => 'is_valid',
	'format' => 'boolean',
];

$attributes[] = [
	'attribute' => 'subscribe_ldap',
	'format' => 'boolean',
];

$attributes[] = [
	'attribute' => 'subscribe_adaxes',
	'format' => 'boolean',
];

if ($value = $model->created_at) {
	$attributes[] = [
		'attribute' => 'created_at',
		'format' => 'datetime',
	];
}

if ($user = $model->user) {
	$attributes[] = [
		'attribute' => 'owner_id',
		'value' => $user->name,
	];
}

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);

ViewCard::end();


echo $this->render('view/_licenses', ['model' => $model]);

echo $this->render('view/_upgrade-protections', ['model' => $model]);

echo $this->render('view/_supports', ['model' => $model]);

echo $this->render('view/_deliveries', ['model' => $model]);

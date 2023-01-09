<?php

use yii\helpers\ArrayHelper;
use yii\helpers\Url;

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

ViewCard::begin($options);

$attributes = [];

if ($value = $model->company) {
	$attributes[] = [
		'attribute' => 'company',
	];
}

if ($value = $model->owner_name) {
	$attributes[] = [
		'attribute' => 'owner_name',
	];
}

if ($email = $model->email) {
	$attributes[] = [
		'attribute' => 'email_id',
		'value' => $email->getLink() . $email->getUpgradeProtectionCount(['class' => 'badge badge-light']),
		'format' => 'raw',
	];
}
/*
$attributes[] = [
	'attribute' => 'license_id',
	'value' => $model->license->getLink(),
	'format' => 'raw',
];
*/
$attributes[] = [
	'attribute' => 'buy_date',
	'format' => 'date',
];

$attributes[] = [
	'attribute' => 'end_date',
	'format' => 'date',
];
/*
$attributes[] = [
	'attribute' => 'product_info_id',
	'value' =>  Html::tag('span', ArrayHelper::getValue($model, 'productInfo.commercial_name'), ['title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')]),
	'format' => 'raw',
];
*/
$attributes[] = [
	'attribute' => 'created_at',
	'format' => 'datetime',
];

if ($user = $model->user) {
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

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);

ViewCard::end();

ViewCard::begin([
		'icon' => Html::ICON_LICENSE,
		'title' => Yii::t('app', 'License'),
		'headerOptions' => ['class' => 'bg-light'],
		'linkOptions' => [],
	]);
	
	echo $this->render('/license/_grid', ['models' => [$model->license->id => $model->license]]);
	
ViewCard::end();
	
echo $this->render('view/_deliveries', ['model' => $model]);


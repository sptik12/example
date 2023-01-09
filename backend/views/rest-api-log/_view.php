<?php

use yii\helpers\ArrayHelper;

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\ViewCard;
use common\models\RestApiLog;

/* @var $this yii\web\View */
/* @var $model common\models\RestApiLog */

$this->title = Yii::t('app', 'Rest Api Log');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Rest Api Log'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'View');

ViewCard::begin([
	'icon' => Html::ICON_LOG,
]);

$attributes = [];

$attributes[] = [
	'attribute' => 'created_at',
	'format' => 'date',
];

$attributes[] = [
	'attribute' => 'product_id',
	'value' => ArrayHelper::getValue($model, 'productName'),
];

/*$attributes[] = [
	'attribute' => 'product_id',
	'label' => Yii::t('app', 'v.'),
	'value' => ArrayHelper::getValue($model, 'productInfo.fullVersion')
];*/

$attributes[] = [
	'attribute' => 'url',
	'value' => $model->url
];

$attributes[] = [
	'attribute' => 'params',
	'value' => Html::tag('pre', $model->params),
	'format' => 'raw',
];

$attributes[] = [
	'attribute' => 'response',
	'value' => Html::tag('pre', $model->response),
	'format' => 'raw',
];

if ($value = $model->referer) {
	$attributes[] = [
		'attribute' => 'referer',
	];
}

$attributes[] = [
	'attribute' => 'status',
	'value' =>  $model->status == RestApiLog::STATUS_SUCCESS ? Html::tag('span', Yii::t('app', 'Success'), ['class' => 'text-bold text-green']) : Html::tag('span', Yii::t('app', 'Fault'), ['class' => 'text-bold text-red']),
	'format' => 'raw',
];

echo DetailView::widget([
	'model' => $model,
	'attributes' => $attributes,
]);


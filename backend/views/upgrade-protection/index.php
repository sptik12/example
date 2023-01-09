<?php

use common\helpers\Html;
use common\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UpgradeProtectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Annual Maintenances and Supports');


$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['/license']];
$this->params['breadcrumbs'][] = ['label' => $searchModel->license->id, 'url' => $searchModel->license->route];
$this->params['breadcrumbs'][] = Yii::t('app', 'Annual Maintenances and Supports');

$toolbar = [];

if (!$searchModel->license->hasLicenseInvalidations()) {
	$toolbar[] = [
		'content' => Html::a(Html::icon(Html::ICON_PLUS, ['class' => 'mr-2']) . Yii::t('app', 'Add Annual Maintenance and Support'), ['create', 'id' => $searchModel->license_id], ['class' => 'btn btn-outline-primary', 'title' => Yii::t('app', 'Add new Annual Maintenance and Support'), 'data-pjax' => 0]),
	];
}
//$toolbar[] = '{toggleData}';

$title = Yii::t('app', 'License: {id}', ['id' => Html::a(Html::encode($searchModel->license_id), $searchModel->license->route, ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);

echo GridView::widget([
	'id' => 'upgrade-protection-grid',
	'dataProvider' => $dataProvider,
	//'filterModel' => $searchModel,
	'export' => false,
	//'showOnEmpty' => false,
	'showHeader'=> $dataProvider->totalCount,
	'itemLabelSingle' => 'Annual Maintenance and Support',
	'itemLabelPlural' => 'Annual Maintenances and Supports',
	'panel' => [
		'heading' => Html::icon(Html::ICON_PROTECTION, ['class' => 'mr-3']) . $title,
	],
	'toolbar' => $toolbar,
	'rowOptions' => function ($model, $index, $widget, $grid) {
		if ($model->isExpired()) {
			return ['class' => $model->getCssClass('expired')];
		}
	},
	'columns' => [
		[
			'class' => 'common\widgets\grid\ExpandRowColumn',
			'value' => function ($model, $key, $index, $column) {
				return GridView::ROW_COLLAPSED;
			},
			'detailUrl' => Url::to(['index-view']),
			'visible' => $dataProvider->totalCount,
		],
		[
			'attribute' => 'company',
		],
		[
			'attribute' => 'owner_name',
			'content' => function ($model) {
				$options = ['data-pjax' => 0, 'title' => Yii::t('yii', 'View details')];
				if ($model->isExpired()) {
					Html::addCssClass($options, $model->getCssClass('expired'));
				}
				return Html::a(Html::encode($model->owner_name), $model->getRoute(), $options);
			},
		],
		[
			'attribute' => 'email_id',
			'value' => function ($model) {
				if ($email = $model->email) {
					return $email->address;
				}
			},
		],
		[
			'attribute' => 'buy_date',
			'format' => 'date',
		],
		[
			'attribute' => 'end_date',
			'format' => 'date',
		],
		[
			'attribute' => 'product_info_id',
			'label' => Yii::t('app', 'Ver.'),
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'productInfo.commercial_name');
			},
			'contentOptions' => function ($model, $key, $index, $column) {
				return ['title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')];
			},
		],
		[
			'attribute' => 'owner_id',
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'user.name');
			},
		],
	],
]); 


<?php

use common\helpers\Html;
use common\widgets\grid\GridView;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UpgradeProtectionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Annual Maintenances and Supports');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Emails'), 'url' => ['/email']];
$this->params['breadcrumbs'][] = ['label' => $searchModel->email->address, 'url' => $searchModel->email->route];
$this->params['breadcrumbs'][] = $this->title;

$title = Yii::t('app', 'Email: {address}', ['address' => Html::a(Html::encode($searchModel->email->address), $searchModel->email->route, ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);

$toolbar = [];

if (Yii::$app->user->can('delivery-create')){
	$toolbar[] = [
		'content' => Html::a(Html::icon(Html::ICON_DELIVERY, ['class' => 'mr-2']) . Yii::t('app', 'Add Deliveries'), ['bulk-delivery', 'id' => $searchModel->email_id], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-dark bulk disabled', 'title' => Yii::t('app', 'Add Delivery')]),
	];
}
//$toolbar[] = '{toggleData}';

echo GridView::widget([
	'id' => 'upgrade-protection-grid',
	'dataProvider' => $dataProvider,
	//'filterModel' => $searchModel,
	'export' => false,
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
			'class' => 'common\widgets\grid\CheckboxColumn',
			'noSelectionMessage' => Yii::t('app', 'No Annual Maintenances and Supports selected'),
			'visible' => $dataProvider->totalCount && Yii::$app->user->can('delivery-create'),
			'pjax' => false,
			'checkboxOptions' => function ($model, $key, $index, $column) {
				if($license=$model->license){
					if ($license->hasLicenseInvalidations()) {
						return ['style' => ['display' => 'none'], 'disabled' => true];
					}
				}
				return ['value' => $key];
		   },
		],
		[
			'class' => 'common\widgets\grid\ExpandRowColumn',
			'value' => function ($model, $key, $index, $column) {
				return GridView::ROW_COLLAPSED;
			},
			'detailUrl' => Url::to(['index-view']),
			'visible' => $dataProvider->totalCount,
		],
		[
			'attribute' => 'owner_name',
			'content' => function ($model) {
				$options = ['data-pjax' => 0, 'title' => Yii::t('yii', 'View details')];
				if ($model->isExpired()) {
					Html::addCssClass($options, $model->getCssClass('expired'));
				}
				return Html::a($model->owner_name, $model->getRoute(), $options);
			},
		],
		[
			'attribute' => 'company',
		],
		[
			'attribute' => 'buy_date',
			'format' => 'date',
			'contentOptions' => ['class' => 'text-nowrap'],
		],
		[
			'attribute' => 'end_date',
			'format' => 'date',
			'contentOptions' => ['class' => 'text-nowrap'],
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
/*		[
			'attribute' => 'license_id',
			'contentOptions' => ['class' => 'text-nowrap'],
		],*/
		[
			'attribute' => 'owner_id',
			'value' => function ($data) {
				return ArrayHelper::getValue($data, 'user.name');
			},
		],
	],
	'rowOptions' => function ($model, $index, $widget, $grid){
		$options = ['title' => $model->license_id];
		if($model->isExpired()){
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		return $options;
	},
]); 


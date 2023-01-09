<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

use kartik\widgets\DatePicker;

use \common\models\RestApiLog;

use common\helpers\Html;
use common\helpers\Config;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\RestApiLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Rest Api Log');
$this->params['breadcrumbs'][] = $this->title;

$this->params['pjax_reload_on_change_product'] = 'rest-api-log-grid-pjax';

//Pjax::begin(['id' => 'rest-api-log-grid-pjax', 'timeout' => 5000]);

$columns = [];

$columns[] = [
	'class' => 'common\widgets\grid\ExpandRowColumn',
	'header' => $this->render('/filter/_button', ['route' => '/rest-api-log']),
	'headerOptions' => ['class' => 'kv-align-bottom kv-align-left'],
	'value' => function ($model, $key, $index, $column) {
		return GridView::ROW_COLLAPSED;
	},
	'detailUrl' => Url::to(['index-view']),
	'visible' => true,
];

$columns[] = [
	'class' => 'common\widgets\grid\CheckboxColumn',
	'noSelectionMessage' => Yii::t('app', 'No Log Records selected'),
	'visible' => $dataProvider->totalCount,
];

$columns[] = [
	'attribute' => 'created_at',
	'format' => 'datetime',
	'filterType' => GridView::FILTER_DATE_RANGE,
	'filterWidgetOptions' => ([
		'convertFormat' => true,
		'options' => ['autocomplete' => 'off'],
		'pluginOptions' => [
			'separator' => $searchModel::RANGE_SEPARATOR,
			'locale' => [
				'format' => 'Y-m-d',
			],
		],
	]),
	'contentOptions' => [
	],
	'filterOptions' => ['style' => 'width:200px;'],
];

$columns[] = [
	'attribute' => 'product_id',
	'value' => function ($model) {
		return $model->getProductName();
	},
	'filter' => false,
	'visible' => !Config::getProfileProductId(),
];

$columns[] = [
	'attribute' => 'url',
];

$columns[] = [
	'attribute' => 'status',
	'filter' =>
		Html::activeDropDownList(
			$searchModel,
			'status',
			[RestApiLog::STATUS_SUCCESS => Yii::t('app', 'Success'), RestApiLog::STATUS_ERROR => Yii::t('app', 'Fail')],
			['class' => 'form-control', 'encode' => false, 'prompt'=>Yii::t('app', 'All Records')]
		),
	'headerOptions' => [
		'class' => 'not-sorted',
	],
	'value' => function ($data) {
		return $data->status == RestApiLog::STATUS_SUCCESS ? Yii::t('app','Success') : Yii::t('app','Fail');
	},
];

$toolbar = [];
if ($searchModel->start_date && $searchModel->end_date && $dataProvider->getTotalCount()) {
	$toolbar[] = [
		'content' => Html::a(Html::icon(Html::ICON_DELETE) . ' ' . Yii::t('app', 'Remove {count} Records for period: {start_date} - {end_date}', ['count' => $dataProvider->getTotalCount(), 'start_date' => $searchModel->start_date, 'end_date' => $searchModel->end_date]), ArrayHelper::merge(['delete-period', 'start_date' => $searchModel->start_date, 'end_date' => $searchModel->end_date], $searchModel->getAttributes()), [
			'data-pjax' => 0,
			'class' => 'btn btn-outline-danger',
			'title' => Yii::t('app', 'Remove Log Records'),
			'data-confirm' => Yii::t('app', 'Are you sure you want to permanently remove {count} Log Record(s)?', ['count' => $dataProvider->getTotalCount()]),
		]),
	];
}

$toolbar[] = ['content' => Html::a(Html::icon(Html::ICON_DELETE) . ' ' . Yii::t('app', 'Delete'), ['delete-bulk'], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-danger bulk disabled', 'title' => Yii::t('app', 'Delete Selected Users'), 'data-confirm' => 'Are you sure you want to permanently delete selected Record(s)?']),];

echo GridView::widget([
	'id' => 'rest-api-log-grid',
	'pjax' => true,
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'itemLabelSingle' => 'record',
	'itemLabelPlural' => 'records',
	'panel' => [
		'heading' => Html::icon(Html::ICON_LOG, ['class' => 'mr-3']) . $this->title,
	],
	'toolbar' => $toolbar,
	'columns' => $columns,
	'rowOptions' =>  function ($model) { return ['class' => $model->status == RestApiLog::STATUS_ERROR ? 'bg-fail' : GridView::TYPE_DEFAULT]; },
]);

//Pjax::end();

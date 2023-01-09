<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use yii\widgets\Pjax;

use kartik\widgets\DatePicker;

use common\helpers\Html;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\MailLogSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Mail Log');

$this->params['breadcrumbs'][] = $this->title;

$this->params['pjax_reload_on_change_product'] = 'mail-log-grid-pjax';

$columns = [];

$columns[] = [
	'class' => 'common\widgets\grid\ExpandRowColumn',
	'header' => $this->render('/filter/_button', ['route' => '/mail-log']),
	'headerOptions' => ['class' => 'kv-align-bottom kv-align-left'],
	'value' => function ($model, $key, $index, $column) {
		return GridView::ROW_COLLAPSED;
	},
	'detailUrl' => Url::to(['index-view']),
	'visible' => true,
	'expandOneOnly' => true,
	'allowBatchToggle' => false,
];

$columns[] = [
	'class' => 'common\widgets\grid\ExpandRowColumn',
	'header' => Yii::t('app','Text'),
	'value' => function ($model, $key, $index, $column) {
		return GridView::ROW_COLLAPSED;
	},
	'detailUrl' => Url::to(['index-view', 'type' => 'plain']),
	'visible' => true,
	'expandOneOnly' => true,
	'allowBatchToggle' => false,
];

$columns[] = [
	'class' => 'common\widgets\grid\ExpandRowColumn',
	'header' => Yii::t('app','Html'),
	'value' => function ($model, $key, $index, $column) {
		return GridView::ROW_COLLAPSED;
	},
	'detailUrl' => Url::to(['index-view', 'type' => 'html']),
	'visible' => true,
	'expandOneOnly' => true,
	'allowBatchToggle' => false,
];

if (Yii::$app->user->can('event-log')) {
	$columns[] = [
		'class' => 'common\widgets\grid\CheckboxColumn',
		'noSelectionMessage' => Yii::t('app', 'No Log Records selected'),
		'visible' => $dataProvider->totalCount,
	];
}

$columns[] = [
	'attribute' => 'created_at',
//	'value' => function ($model, $key, $index, $column) {
//		return Yii::$app->formatter->asDatetime($model->created_at, 'php:Y-m-d H:i:s');
//	},
	'format' => 'datetime',
/*	'filter' => DatePicker::widget([
		'model' => $searchModel,
		'attribute' => 'start_date',
		'attribute2' => 'end_date',
		'separator' => '-',
		'type' => DatePicker::TYPE_RANGE,
		'options' => ['autocomplete' => 'off'],
		'options2' => ['autocomplete' => 'off'],
		'pluginOptions' => [
			'autoclose' => true,
			'format' => 'yyyy-mm-dd',
		]
	]),*/
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
	'attribute' => 'email',
	'value' => function ($data) {
		return $data->email;
	},
	'contentOptions' => [
		'class' => 'text-nowrap',
	],
];

$columns[] = [
	'attribute' => 'mail_template_id',
	'value' => function ($model) {
		return $model->mailTemplateName;
	},
	'filter' => Html::activeDropDownList(
		$searchModel,
		'mail_template_id',
		$searchModel->getMailTemplateItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'Any')]
	),
];

$columns[] = [
	'attribute' => 'letter_id',
	'value' => function ($data) {
		return $data->letterName;
	},
	'filter' => Html::activeDropDownList(
		$searchModel,
		'letter_id',
		$searchModel->getLetterItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'All')]
	),
];

$columns[] = [
	'attribute' => 'license_id',
	'content' => function ($data) {
		if ($data->license) {
			return Html::a(Html::encode($data->license->id), ['/license/view', 'id' => $data->license->id], ['target' => '_blank', 'data-pjax' => 0]);
		}
	},
	'contentOptions' => [
		'class' => 'text-nowrap',
	],
];

$toolbar = [];

if (Yii::$app->user->can('event-log')) {
	if ($searchModel->start_date && $searchModel->end_date && $dataProvider->getTotalCount()) {
		$toolbar[] = [
			'content' => Html::a(Html::icon(Html::ICON_DELETE) . ' ' . Yii::t('app', 'Remove {count} Records for period: {start_date} - {end_date}', ['count' => $dataProvider->getTotalCount(), 'start_date' => $searchModel->start_date, 'end_date' => $searchModel->end_date]), ArrayHelper::merge(['delete-period', 'start_date' => $searchModel->start_date, 'end_date' => $searchModel->end_date], $searchModel->getAttributes()), [
				'data-pjax' => 0,
				'class' => 'btn btn-outline-danger',
				'title' => Yii::t('app', 'Remove Log Records'),
				'data-confirm' => Yii::t('app', 'Are you sure you want to permanently remove {count} Log Records?', ['count' => $dataProvider->getTotalCount()]),
			]),
		];
	}
}

$toolbar[] = [
	'content' => Html::a(Html::icon(Html::ICON_DELETE) . ' ' . Yii::t('app', 'Delete'), ['delete-bulk'], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-danger bulk disabled', 'title' => Yii::t('app', 'Delete Selected Users'), 'data-confirm' => 'Are you sure you want to permanently delete selected Records?']),
];

$title = [];

if($searchModel->delivery_id && ($delivery=$searchModel->delivery)){
	$title[] = Yii::t('app', 'Delivery: {id}', ['id' => Html::a(Html::encode($delivery->name), $delivery->route, ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);
}

echo GridView::widget([
	'id' => 'mail-log-grid',
	'pjax' => true,
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'itemLabelSingle' => 'record',
	'itemLabelPlural' => 'records',
	'panel' => [
		'heading' => Html::icon(Html::ICON_LOG, ['class' => 'mr-3']) . implode(', ', $title),
	],
	'toolbar' => $toolbar,
	'columns' => $columns,
]);

//Pjax::end();

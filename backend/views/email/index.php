<?php

use yii\helpers\Url;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\EmailSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Emails');
$this->params['breadcrumbs'][] = $this->title;

$this->params['pjax_reload_on_change_product'] = 'email-grid-pjax';

$title = [];


echo GridView::widget([
	'id' => 'email-grid',
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'itemLabelSingle' => 'email',
	'itemLabelPlural' => 'emails',
	'panel' => [
		'heading' => Html::icon(Html::ICON_EMAIL),
	],
	'toolbar' => false,
	'columns' => [
		[
			'class' => 'common\widgets\grid\ActionColumn',
			'header' => $this->render('/filter/_button', ['route' => '/email']),
			'headerOptions' => ['class' => 'kv-align-bottom kv-align-left'],
			'template' => '{view}',
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
			'attribute' => 'email',
			'content' => function ($model) {
				$options = ['data-pjax' => 0, 'title' => Yii::t('app', 'View Email Details')];
				if (!$model->isValid()) {
					Html::addCssClass($options, $model->getCssClass('invalid'));
				}
				return Html::a(Html::encode($model->address), $model->getRoute(), $options);
			},
		],
		[
			'attribute' => 'id',
			'label' => 'Added',
			//'format' => 'datetime',
			'value' => function ($model) {
				return Yii::$app->formatter->asDatetime($model->created_at);
			},
			'contentOptions' => ['class' => 'text-nowrap'],
			'filter' => Html::activeDropDownList(
				$searchModel,
				'period',
				$searchModel->getPeriodItems(),
				['class' => 'form-control', 'prompt' => Yii::t('app', 'Any Date')]
			),
		],
		[
			'class' => 'common\widgets\grid\BooleanColumn',
			'attribute' => 'is_valid',
			'trueLabel' => Yii::t('app', 'Yes'),
			'falseLabel' => Yii::t('app', 'No'),
		],
		[
			'header' => Html::icon(Html::ICON_LICENSE, ['title' => Yii::t('app', 'License count')]),
			'content' => function ($model) {
				return $model->getLicenseCount(['data-pjax' => 0]);
			},
			'hAlign' => 'right',
			//'filter' => Html::a(Html::icon(Html::ICON_REDO), ['index', 'clear' => 1], ['class' => 'btn btn-outline-secondary', 'title' => Yii::t('app', 'Reset Filters')]),
		],
		[
			'header' => Html::icon(Html::ICON_PROTECTION, ['title' => Yii::t('app', 'Annual Maintenance and Support count')]),
			'content' => function ($model) {
				return $model->getUpgradeProtectionCount(['data-pjax' => 0]);
			},
			'hAlign' => 'right', 
		],
		[
			'header' => Html::icon(Html::ICON_SUPPORT, ['title' => Yii::t('app', 'Support count')]),
			'content' => function ($model) {
				return $model->getSupportCount(['data-pjax' => 0]);
			},
			'hAlign' => 'right', 
		],
		[
			'header' => Html::icon(Html::ICON_DELIVERY, ['title' => Yii::t('app', 'Delivery count')]),
			'content' => function ($model) {
				return $model->getDeliveryCount(['data-pjax' => 0]);
			},
			'hAlign' => 'right', 
		],
	],
	'rowOptions' => function ($model, $index, $widget, $grid){
		$options = [];
		if (!$model->isValid()) {
			Html::addCssClass($options, $model->getCssClass('invalid'));
		}
		return $options;
	},
]);
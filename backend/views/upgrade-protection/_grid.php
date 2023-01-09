<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use common\widgets\grid\GridView;
use yii\helpers\Url;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $models array of common\models\UpgradeProtection */

$dataProvider = new ArrayDataProvider(['allModels' => $models, 'pagination' => false]);

echo GridView::widget([
	'id' => 'upgrade-protection-grid',
	'dataProvider' => $dataProvider,
	'pjax' => false,
	'export' => false,
	'panel' => false,
	'resizableColumns' => false,
	'layout' => '{items}',
	'columns' => [
		[
			'class' => 'yii\grid\ActionColumn',
			'template' => '{view}',
			'urlCreator' => function ($action, $model, $key, $index) {
				if ($action === 'view') {
					return $model->route;
				}
			},
			'visible' => Yii::$app->user->can('upgrade-protection'),
		],
/*		[
			'class' => 'common\widgets\grid\ExpandRowColumn',
			'value' => function ($model, $key, $index, $column) {
				return GridView::ROW_COLLAPSED;
			},
			'detailUrl' => Url::to(['/upgrade-protection/index-view']),
			'visible' => $dataProvider->totalCount && !Yii::$app->request->isAjax,
		],
		[
			'attribute' => 'company',
		],
		[
			'attribute' => 'owner_name',
			'content' => function ($model) {
				$options = [];
				if ($model->isExpired()) {
					Html::addCssClass($options, $model->getCssClass('expired'));
				}
				if (Yii::$app->user->can('upgrade-protection')){
					$options['data-pjax'] = 0;
					$options['title'] = Yii::t('app', 'Open Annual Maintenance and Support Details');
					return Html::a(Html::encode($model->owner_name), $model->getRoute(), $options);
				}
				else{
					return Html::tag('span', Html::encode($email->owner_name), $options);
				}
			},
		],
		[
			'attribute' => 'email_id',
			'value' => function ($model) {
				if ($email = $model->email) {
					return $email->address;
				}
			},
		],*/
		[
			'attribute' => 'created_at',
			'format' => 'datetime',
		],
		[
			'attribute' => 'buy_date',
			'format' => 'date',
		],
		[
			'attribute' => 'end_date',
			'format' => 'date',
		],
/*		[
			'attribute' => 'product_info_id',
			'label' => Yii::t('app', 'Version'),
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'productInfo.commercial_name');
			},
			'contentOptions' => function ($model, $key, $index, $column) {
				return ['title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')];
			},
		],*/
		[
			'attribute' => 'owner_id',
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'user.name');
			},
		],
	],
	'rowOptions' => function ($model, $index, $widget, $grid){
		$options = ['title' => $model->license_id];
		if($model->isExpired()){
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		if (Yii::$app->request->isAjax && Yii::$app->user->can('upgrade-protection')){
			$options['onclick'] = "location.href = " . Json::encode(Url::to($model->route));
			Html::addCssClass($options, 'cursor-pointer');
		}
		return $options;
	},
]);

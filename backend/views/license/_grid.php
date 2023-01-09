<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\data\ArrayDataProvider;
use common\widgets\grid\GridView;
use yii\helpers\Url;
use yii\helpers\Json;

/* @var $this yii\web\View */
/* @var $model array of common\models\License */

$active = isset($active) ? $active : null;

$buttons = isset($buttons) ? $buttons : 1;

$dataProvider = new ArrayDataProvider(['allModels' => $models, 'pagination' => false]);

echo GridView::widget([
	//'id' => 'license-grid',
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
			'visible' => Yii::$app->request->isAjax && Yii::$app->user->can('license'),
		],
		[
			'class' => 'common\widgets\grid\ExpandRowColumn',
			'value' => function ($model, $key, $index, $column) {
				return GridView::ROW_COLLAPSED;
			},
			'detailUrl' => Url::to(['/license/index-view', 'buttons' => $buttons]),
			'visible' => $dataProvider->totalCount && !Yii::$app->request->isAjax,
		],
		[
			'attribute' => 'company',
			'content' => function ($model) {
				$options = [];
				if($model->isExpired()){
					Html::addCssClass($options, $model->getCssClass('expired'));
				}
				if($model->hasLicenseInvalidations()){
					Html::addCssClass($options, $model->getCssClass('invalidated'));
				}
				if ($model->level){
					$options['style'] = 'margin-left: ' . ($model->level * 5) . 'px;';
				}
				if (Yii::$app->user->can('license')){
					$options['data-pjax'] = 0;
					$options['title'] = Yii::t('app', 'Open License Details');
					return Html::a(Html::encode($model->company), $model->getRoute(), $options);
				}
				else{
					return Html::tag('span', Html::encode($email->company), $options);
				}
			},
		],
		[
			'attribute' => 'owner_name',
			'content' => function ($model) {
				$options = [];
				if($model->isExpired()){
					Html::addCssClass($options, $model->getCssClass('expired'));
				}
				if($model->hasLicenseInvalidations()){
					Html::addCssClass($options, $model->getCssClass('invalidated'));
				}
				if (Yii::$app->user->can('license')){
					$options['data-pjax'] = 0;
					$options['title'] = Yii::t('app', 'Open License Details');
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
		],
		[
			'attribute' => 'created_at',
			'format' => 'date',
			'contentOptions' => ['class' => 'text-nowrap'],
		],
		[
			'attribute' => 'not_after_date',
			'label' => Yii::t('app', 'Valid until'),
			'format' => 'date',
			'contentOptions' => ['class' => 'text-nowrap'],
		],
		[
			'label' => Yii::t('app', 'Type'),
			'value' => function ($model) {
				return $model->getLicenseTypeDisplayName();
			},
		],
		[
			'attribute' => 'upgradeProtectionEndDate',
			'label' => Yii::t('app', 'AMS Date'),
			'contentOptions' => function ($model, $key, $index, $column) {
				return['class' => $model->upgradeProtectionCss, 'style' => 'font-weight: normal;'];
			},
			'format' => 'date',
		],
		[
			'attribute' => 'product_info_id',
			'label' => Yii::t('app', 'Version'),
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'productInfo.commercial_name');
			},
			'contentOptions' => function ($model, $key, $index, $column) {
				return ['title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')];
			},
		],
/*		[
			'attribute' => 'owner_id',
			'value' => function ($model) {
				return ArrayHelper::getValue($model, 'user.name');
			},
		],*/
	],
	'rowOptions' => function ($model, $index, $widget, $grid) use ($active){
		$options = ['title' => $model->id, 'class' => $model->getCssClass($model->getLicenseTypeName())];
		if($model->isExpired()){
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		if($model->hasLicenseInvalidations()){
			Html::addCssClass($options, $model->getCssClass('invalidated'));
		}
		if($model->id == $active){
			Html::addCssClass($options, 'table-active');
			Html::addCssClass($options, 'table-row-active');
		}
		if (Yii::$app->request->isAjax && Yii::$app->user->can('license')){
			$options['onclick'] = "location.href = " . Json::encode(Url::to($model->route));
			Html::addCssClass($options, 'cursor-pointer');
		}
		return $options;
	},
]);

<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;
use yii\data\ArrayDataProvider;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $models array */

if ($models){
	LightCard::begin([
		'icon' => Html::ICON_INVALIDATE,
		'title' => Yii::t('app', 'Invalidation'),
	]);

	$dataProvider = new ArrayDataProvider(['allModels' => $models, 'pagination' => false]);

	echo GridView::widget([
		'id' => 'license-invalidation-grid',
		'dataProvider' => $dataProvider,
		'pjax' => false,
		'export' => false,
		'panel' => false,
		'resizableColumns' => false,
		'layout' => '{items}',
		'columns' => [
			[
				'attribute' => 'created_at',
				'format' => 'date',
			],
			[
				'attribute' => 'reason',
			],
			[
				'attribute' => 'owner_id',
				'value' => function ($model) {
					return ArrayHelper::getValue($model, 'user.name');
				},
			],
		],
	]);

	LightCard::end();
}


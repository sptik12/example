<?php

use common\helpers\Html;
use common\widgets\DetailView;
use common\widgets\LightCard;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\License */

	LightCard::begin([
		'icon' => Html::ICON_TRAITS,
		'title' => (count($models) > 1) ? Yii::t('app', 'Traits') . Html::tag('span', count($models), ['class' => 'badge badge-secondary ml-2']) : Yii::t('app', 'Trait'),
		'show' => count($models) < ArrayHelper::getValue(Yii::$app, 'params.minCountToCollapse', 5),
		'id' => 'trait-values',
	]);

	$attributes = [];

	foreach ($models as $model) {
		$attributes[] = [
			'label' => $model->description,
			'value' => $model->displayValue,
		];
	}

	echo DetailView::widget([
		'model' => $model,
		'attributes' => $attributes,
	]);

	LightCard::end();


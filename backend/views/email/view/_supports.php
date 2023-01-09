<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\Email */

if ($models = $model->getSupports()->with(['email', 'user'])->ordered()->indexBy('id')->all()){

	LightCard::begin([
		'icon' => Html::ICON_SUPPORT,
		'title' => (count($models) > 1) ? Yii::t('app', 'Supports') : Yii::t('app', 'Support'),
		'addOn' => $model->getSupportCount(['class' => 'badge badge-secondary ml-2', 'data-pjax' => 0]),
		'show' => count($models) < ArrayHelper::getValue(Yii::$app->params, 'minCountToCollapse', 5),
	]);
	
	echo $this->render('/support/_grid', ['models' => $models]);
	
	LightCard::end();
}
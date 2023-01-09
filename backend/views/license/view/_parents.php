<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

if ($models = $model->getParentModels()){

	LightCard::begin([
		'icon' => Html::ICON_DOWN,
		'title' => (count($models) > 1) ? Yii::t('app', 'Upgraded Licenses') : Yii::t('app', 'Upgraded License'),
		'addOn' => Html::tag('span', count($models), ['class' => 'badge badge-secondary ml-2']),
		'id' => 'parents',
	]);
	
	echo $this->render('/license/_grid', ['models' => $models]);
	
	LightCard::end();
}
<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

if ($models = $model->childModels){

	LightCard::begin([
		'icon' => Html::ICON_UP,
		'title' => (count($models) > 1) ? Yii::t('app', 'Successor Licenses') : Yii::t('app', 'Successor License'),
		'addOn' => Html::tag('span', count($models), ['class' => 'badge badge-secondary ml-2']),
		'show' => !Yii::$app->request->isAjax,
	]);
	
	echo $this->render('/license/_grid', ['models' => $models]);
	
	LightCard::end();
}
<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

	LightCard::begin([
		'icon' => Html::ICON_SUPPORT,
		'title' => (count($models) > 1) ? Yii::t('app', 'Supports') : Yii::t('app', 'Support'),
		'addOn' => $model->getSupportCount(count($models), ['class' => 'badge badge-secondary ml-2']),
		//'show' => !Yii::$app->request->isAjax,
	]);
	
	echo $this->render('/support/_grid', ['models' => $models]);
	
	LightCard::end();

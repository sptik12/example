<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

	LightCard::begin([
		'icon' => Html::ICON_DELIVERY,
		'title' => (count($models) > 1) ? Yii::t('app', 'Deliveries') : Yii::t('app', 'Delivery'),
		'addOn' => $model->getDeliveryCount(count($models), ['class' => 'badge badge-secondary ml-2']),
	]);
	
	echo $this->render('/delivery/_grid', ['models' => $models]);
	
	LightCard::end();

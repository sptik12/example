<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

if ($models = $model->getDeliveries()->with(['email', 'user', 'downloads' => function (\yii\db\ActiveQuery $query) { $query->ordered();	}])->ordered()->indexBy('id')->all()){

	LightCard::begin([
		'icon' => Html::ICON_DELIVERY,
		'title' => (count($models) > 1) ? Yii::t('app', 'Deliveries') . Html::tag('span', count($models), ['class' => 'badge badge-secondary ml-2']) : Yii::t('app', 'Delivery'),
	]);
	
	echo $this->render('/delivery/_grid', ['models' => $models]);
	
	LightCard::end();
}
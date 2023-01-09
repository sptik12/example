<?php

use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\Email */

if ($models = $model->getDeliveries()->with(['email', 'user', 'downloads' => function (\yii\db\ActiveQuery $query) { $query->ordered();	}])->ordered()->indexBy('id')->all()){

	LightCard::begin([
		'icon' => Html::ICON_DELIVERY,
		'title' => (count($models) > 1) ? Yii::t('app', 'Deliveries') : Yii::t('app', 'Delivery'),
		'addOn' => $model->getDeliveryCount(['class' => 'badge badge-secondary ml-2', 'data-pjax' => 0]),
		'show' => count($models) < ArrayHelper::getValue(Yii::$app->params, 'minCountToCollapse', 5),
	]);
	
	echo $this->render('/delivery/_grid', ['models' => $models]);
	
	LightCard::end();
}
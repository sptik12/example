<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\Email */

if ($models=$model->getUpgradeProtections()->with(['productInfo', 'email', 'user'])->ordered()->indexBy('id')->all()){

	LightCard::begin([
		'icon' => Html::ICON_PROTECTION,
		'title' => (count($models) > 1) ? Yii::t('app', 'Annual Maintenances and Supports') : Yii::t('app', 'Annual Maintenance and Support'),
		'addOn' => $model->getUpgradeProtectionCount(['class' => 'badge badge-secondary ml-2']),
		'show' => count($models) < ArrayHelper::getValue(Yii::$app->params, 'minCountToCollapse', 5),
	]);
	
	echo $this->render('/upgrade-protection/_grid', ['models' => $models]);
	
	LightCard::end();
}

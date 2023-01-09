<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

	LightCard::begin([
		'icon' => Html::ICON_PROTECTION,
		'title' => (count($models) > 1) ? Yii::t('app', 'Maintenance History') : Yii::t('app', 'Maintenance History'),
		'addOn' => $model->getUpgradeProtectionCount(count($models), ['class' => 'badge badge-secondary ml-2']),
	]);
	
	echo $this->render('/upgrade-protection/_grid', ['models' => $models]);
	
	LightCard::end();

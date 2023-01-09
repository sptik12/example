<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

	LightCard::begin([
		'icon' => 'history',
		'title' => Yii::t('app', 'History') . Html::tag('span', count($models), ['class' => 'badge badge-secondary ml-2']),
	]);
	
	echo $this->render('/license-history/_grid', ['models' => $models]);
	
	LightCard::end();

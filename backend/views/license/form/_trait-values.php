<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;

/* @var $this yii\web\View */
/* @var $models array of common\models\TraitValue */

Card::begin([
	'icon' => Html::ICON_TRAITS,
	'title' => (count($models) > 1) ? Yii::t('app', 'Traits') : Yii::t('app', 'Trait'),
	'show' => true,
]);

$index = 1;
foreach ($models as $model) {
	if($index % 2 == 1){
		echo Html::beginTag('div', ['class' => 'form-row align-items-end']);
	}
	echo Html::beginTag('div', ['class' => 'col-md-6']);
	
		echo $this->render('_trait-value', ['form' => $form, 'model' => $model, 'disabled' => $disabled]);
		
	echo Html::endTag('div');
	
	if($index % 2 == 0){
		echo Html::endTag('div');
	}
	$index++;
}
if($index % 2 == 0){
	echo Html::endTag('div');
}

Card::end();
<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\CheckboxX;

/* @var $this yii\web\View */
/* @var $model frontend\models\LicenseSearch */

$form = ActiveForm::begin([
	'id' => 'license-filter-form',
	'action' => ['index', 'clear' => 1],
	'type' => ActiveForm::TYPE_HORIZONTAL,
	'method' => 'get',
	'enableClientValidation' => false,
	'options' => ['data-pjax' => 1],
]);

FormCard::begin([
	'icon' => 'search-plus',
	'title' => Yii::t('app', 'Multi-IDs Search'),
	'footer' => Html::button(Yii::t('app', 'Cancel'), ['class' => 'btn btn-default offset-md-3 mr-2 collapse-advanced']) . 
		Html::submitButton(Yii::t('app', 'Search'), ['class' => 'btn btn-primary  mr-2']),
]);

	echo $form->field($model, 'keyword')->textarea(['rows' => 5]);
	
	//echo $form->field($model, 'invalidated')->widget(CheckboxX::classname());
	
	echo Html::hiddenInput('advanced', 1);

FormCard::end();

ActiveForm::end();
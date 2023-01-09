<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\DateControl;
use common\widgets\CheckboxX;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */
/* @var $form yii\widgets\ActiveForm */

$form = ActiveForm::begin();

FormCard::begin([
	'icon' => Html::ICON_PROTECTION,
	'title' => Yii::t('app', 'Add AMS to License: <i>{id}</i>', ['id' => Html::encode($model->license->id)]),
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
]);

echo Html::tag('li', $this->render('/license/_grid', ['models' => [$model->license->id => $model->license], 'buttons' => 0]), ['class' => 'list-group-item form-grid']);

echo Html::beginTag('li', ['class' => 'list-group-item']);

echo $form->errorSummary(ArrayHelper::merge([$model], array_values($deliveries))); 

echo $form->field($model, 'company')->textInput(['maxlength' => true]);

echo $form->field($model, 'owner_name')->textInput(['maxlength' => true]);

echo $form->field($model, 'email_address')->textInput(['maxlength' => true]);

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'period')->dropDownList($model->getPeriodItems());
		
		echo Html::activeHiddenInput($model, 'buy_date');
		
	echo Html::endTag('div');
	
	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'end_date')->widget(DateControl::classname(), ['pastDates' => false]);
		
		echo Html::beginTag('div', ['class' => 'ignore-ams-date-validation collapse' . ($model->period ? '' : ' show')]);
		
			echo $form->field($model, 'ignore_ams_date_validation', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true]);

		echo Html::endTag('div');

	echo Html::endTag('div');
	
echo Html::endTag('div');

echo Html::endTag('li');

FormCard::end();

$footer = Html::a(Yii::t('app', 'Cancel'), $route, ['class' => 'btn btn-default', 'data-pjax' => 0])
	. Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary ml-2']);


echo $this->render('/license/form/_deliveries', ['form' => $form, 'model' => $model, 'deliveries' => $deliveries, 'footer' => $footer]);

ActiveForm::end();

$this->registerJs( "
$('#" . Html::getInputId($model, 'period') . "').change(function(){
	var selected = $(this).val();
	if (selected){
		var date_arr = selected.split('-');
		$('#" . Html::getInputId($model, 'end_date') . "-disp-kvdate').kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
		$('#" . Html::getInputId($model, 'end_date') . "').val(selected);
		$('.ignore-ams-date-validation').collapse('hide');
	}
	else{
		$('.ignore-ams-date-validation').collapse('show');
	}
});

$('#" . Html::getInputId($model, 'end_date') . "').change(function(){
	var selected = $(this).val();
	var combobox = $('#" . Html::getInputId($model, 'period') . "');
	if (selected){
		combobox.val(selected);		
		if (selected != combobox.val()){
			combobox.val('');
		}
	}
});
");
<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\DateControl;
use common\widgets\Select2;
use yii\web\JsExpression;
use yii\helpers\Url;
use common\widgets\CheckboxX;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */
/* @var $form yii\widgets\ActiveForm */

$form = ActiveForm::begin([
	'id' => 'up-license-form',
	'livePageWarning' => true,
	'validateOnBlur' => false,
	'validateOnChange' => false,
]);

FormCard::begin([
	'icon' => Html::ICON_PROTECTION,
	'title' => (count($licenses) == 1 && ($license = reset($licenses))) ? Yii::t('app', 'Add AMS to License: <i>{id}</i>', ['id' => Html::encode($license->id)]) : null,
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
]);

echo Html::beginTag('li', ['class' => 'list-group-item form-grid']);

echo $form->errorSummary(ArrayHelper::merge([$model], array_values($deliveries), array_values($licenses))); 

echo $this->render('form/_licenses', ['models' => $licenses, 'form' => $form]);

echo Html::endTag('li');

echo Html::beginTag('li', ['class' => 'list-group-item']);

echo $form->field($model, 'company')->textInput(['maxlength' => true]);

echo $form->field($model, 'owner_name')->textInput(['maxlength' => true]);

echo $form->field($model, 'email_address')->textInput(['maxlength' => true]);

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'period')->dropDownList($model->getBulkPeriodItems());
		
		echo Html::activeHiddenInput($model, 'buy_date');

	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => 'col-md-6 period collapse' . ($model->period ? '' : ' show')]);
	
		echo $form->field($model, 'end_date')->widget(DateControl::classname(), ['pastDates' => false]);
		
		echo $form->field($model, 'ignore_ams_date_validation', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true]);

	echo Html::endTag('div');

echo Html::endTag('div');

echo Html::endTag('li');

FormCard::end();

$footer = Html::a(Yii::t('app', 'Cancel'), $route, ['class' => 'btn btn-default', 'data-pjax' => 0])
	. Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary ml-2']);

echo $this->render('/license/form/_deliveries', ['form' => $form, 'model' => $model, 'deliveries' => $deliveries, 'footer' => $footer]);

ActiveForm::end();

$js = "
$('#" . Html::getInputId($model, 'period') . "').change(function(){
	var selected = $(this).val();
	if (selected){ 
		$('.period').collapse('hide');
	";

foreach($licenses as $license){
$js .= "
		var up_min_date = $('#" . Html::getInputId($license, '['.$license->id.']up_min_date') . "').val();
		var up_max_date = $('#" . Html::getInputId($license, '['.$license->id.']up_max_date') . "').val();
		var datecontrol = $('#" . Html::getInputId($license, '['.$license->id.']upgrade_protection_end_date') . "-disp');
		var date_arr = up_min_date.split('-');
		var end_date = (parseInt(date_arr[0]) + parseInt(selected)) + '-' + date_arr[1] + '-' + date_arr[2];
		var input_class = '';
		
		if (end_date > up_max_date){
			end_date = up_max_date;
			input_class = 'text-red';
		}
		else{
			input_class = 'text-green';
		}
		if (end_date > up_min_date){
			date_arr = end_date.split('-');
			datecontrol.removeClass('text-red text-green');
			datecontrol.addClass(input_class);
			$('#" . Html::getInputId($license, '['.$license->id.']upgrade_protection_end_date') . "').val(end_date); 
			datecontrol.kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
			$('#up-date-" . $license->id. "').show();
		}";
}
		
$js .= "
	}
	else{
		$('.period').collapse('show');
	}
});

$('#" . Html::getInputId($model, 'end_date') . ", #" . Html::getInputId($model, 'ignore_ams_date_validation') . "').change(function(){
	var selected = $('#" . Html::getInputId($model, 'end_date') . "').val();
	if (selected){";

foreach($licenses as $license){
$js .= "
		var up_min_date = $('#" . Html::getInputId($license, '['.$license->id.']up_min_date') . "').val();
		var up_max_date = $('#" . Html::getInputId($license, '['.$license->id.']up_max_date') . "').val();
		var datecontrol = $('#" . Html::getInputId($license, '['.$license->id.']upgrade_protection_end_date') . "-disp');
		var end_date = selected;
		var input_class = '';
		if (end_date > up_max_date){
			end_date = up_max_date;
			input_class = 'text-red';
		}
		else{
			input_class = 'text-green';
		}
		if (end_date > up_min_date || $('#" . Html::getInputId($model, 'ignore_ams_date_validation') . "').val() == 1){
			var date_arr = end_date.split('-');
			datecontrol.removeClass('text-red text-green');
			datecontrol.addClass(input_class);
			$('#" . Html::getInputId($license, '['.$license->id.']upgrade_protection_end_date') . "').val(end_date); 
			datecontrol.kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
			$('#up-date-" . $license->id. "').show();
		}";
}
	
$js .= "
	}
});
";
$this->registerJs($js);
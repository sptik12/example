<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\widgets\CheckboxX;
use common\widgets\DateControl;
use yii\web\JsExpression;

/* @var $this yii\web\View */
/* @var $models array of common\models\License */

Card::begin([
	'icon' => 'cogs',
	'title' => Yii::t('app', 'Advanced'),
	'show' => $model->date_limit,
]);

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => 'col-md-6']);
	
		echo $form->field($model, 'subversion')->dropDownList($this->context->getSubversionItems($model->product_id, $model->version));

	echo Html::endTag('div');

echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'date_limit', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true, 'options' => ['class' => 'form-control' . ($model->date_limit ? '' : ' collapsed'), 'data-toggle' => 'collapse', 'data-target' => '.date-limit']]);

	echo Html::endTag('div');
	
echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'form-row date-limit collapse' . ($model->date_limit ? ' show' : '')]);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'date_limit_period')->dropDownList($this->context->getDateLimitPeriodItems());

	echo Html::endTag('div');
	
	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'not_after_date')->widget(DateControl::classname(), ['widgetOptions' => ['pluginOptions' => ['startDate' => new JsExpression('new Date()')]]]);

	echo Html::endTag('div');

echo Html::endTag('div');

Card::end();

$this->registerJs( "
$('#" . Html::getInputId($model, 'date_limit_period') . "').change(function(){
	var selected = $(this).val();
	if (selected){
		var date_arr = selected.split('-');
		$('#" . Html::getInputId($model, 'not_after_date') . "-disp-kvdate').kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
		$('#" . Html::getInputId($model, 'not_after_date') . "').val(selected).trigger('change');
	}
});

$('#" . Html::getInputId($model, 'not_after_date') . "').change(function(){
	var selected = $(this).val();
	var combobox = $('#" . Html::getInputId($model, 'date_limit_period') . "');
	if (selected){
		combobox.val(selected);		
		if (selected != combobox.val()){
			combobox.val('');
		}
		if ($('#" . Html::getInputId($model, 'date_limit') . "').val() == 1){
			var date_arr = selected.split('-');
			$('#" . Html::getInputId($model, 'upgrade_protection_end_date') . "-disp-kvdate').kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
			$('#" . Html::getInputId($model, 'upgrade_protection_end_date') . "').val(selected).trigger('change');
		}
	
	}
});
$('#" . Html::getInputId($model, 'date_limit') . "').change(function(){
	$('#" . Html::getInputId($model, 'not_after_date') . "').trigger('change');
});
");
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
	'icon' => Html::ICON_PROTECTION,
	'title' => Yii::t('app', 'Maintenance'),
	'show' => true,
]);


echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'ams', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true, 'options' => ['class' => 'form-control' . ($model->ams ? '' : ' collapsed'), 'data-toggle' => 'collapse', 'data-target' => '.ams']]);

	echo Html::endTag('div');


echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'form-row ams collapse' . ($model->ams ? ' show' : '')]);

	echo Html::beginTag('div', ['class' => 'col-md-6']);

		echo $form->field($model, 'ams_period')->dropDownList($this->context->getAMSPeriodItems());

	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => 'col-md-6']);
	
		echo $form->field($model, 'upgrade_protection_end_date')->widget(DateControl::classname(), ['widgetOptions' => ['pluginOptions' => ['startDate' => new JsExpression('new Date()')]]]);

	echo Html::endTag('div');

echo Html::endTag('div');

Card::end();

$this->registerJs( "
$('#" . Html::getInputId($model, 'ams_period') . "').change(function(){
	var selected = $(this).val();
	if (selected){
		var date_arr = selected.split('-');
		$('#" . Html::getInputId($model, 'upgrade_protection_end_date') . "-disp-kvdate').kvDatepicker('update',  new Date(date_arr[0], date_arr[1] - 1, date_arr[2]));
		$('#" . Html::getInputId($model, 'upgrade_protection_end_date') . "').val(selected);
	}
});

$('#" . Html::getInputId($model, 'upgrade_protection_end_date') . "').change(function(){
	var selected = $(this).val();
	var combobox = $('#" . Html::getInputId($model, 'ams_period') . "');
	if (selected){
		combobox.val(selected);		
		if (selected != combobox.val()){
			combobox.val('');
		}
	}
});
");
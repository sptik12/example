<?php

use common\helpers\Html;
use common\widgets\CheckboxX;

/* @var $this yii\web\View */
/* @var $model common\models\Delivery */

if ($index > $minCount){
	echo Html::button(Html::tag('span', '&times;', ['aria-hidden' => 'true']), ['class' => 'close float-right', 'style' => 'margin-left:.2rem; margin-right:-.8rem; margin-top: -.5rem;', 'aria-label' => 'Close', 'title' => Yii::t('app', 'Remove'), 'data-target' => '#delivery-' . $index]);
}

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => "col-md-6"]);
	echo $form->field($model, '['  . $index . ']email_address',	 [
		'addon' => [
			'append' => [
				'content' => Html::button(Html::icon(Html::ICON_EMAIL), ['class' => 'btn btn-outline-secondary copy-email', 'data-target' => '#' . Html::getInputId($model, '['	 . $index . ']email_address'), 'title' => Yii::t('app', 'Add Customer Email')]),
				'asButton' => true
			]
		]
	])->textInput(['maxlength' => true, 'disabled' => $disabled, 'autocomplete' => 'off']);
	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => "col-md-6"]);
	
	echo $form->field($model, '['  . $index . ']letter_id')->dropDownList($model->letterItems, ['prompt' => Yii::t('app', 'Select Letter'), 'disabled' => $disabled]);
	echo Html::activeHiddenInput($model, '['  . $index . ']disabled', ['class' => 'delivery-disabled']);

	echo Html::endTag('div');

echo Html::endTag('div');

if (!$model->isSupport()) {

	echo Html::beginTag('div', ['class' => 'form-row']);
	
	echo Html::beginTag('div', ['class' => "col-md-6"]);
	echo $form->field($model, '[' . $index . ']add_to_restricted', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true, 'disabled' => $disabled, 'options' => ['class' => 'delivery-bool']]);
	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => "col-md-3"]);
	echo $form->field($model, '[' . $index . ']use_server', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true, 'disabled' => $disabled, 'options' => ['class' => 'delivery-bool']]);
	echo Html::endTag('div');

	echo Html::beginTag('div', ['class' => "col-md-3"]);
	echo $form->field($model, '[' . $index . ']use_zip', ['showLabels' => false])->widget(CheckboxX::classname(), ['autoLabel' => true, 'readonly' => $model->use_server ? true : false, 'disabled' => $disabled, 'options' => ['class' => 'delivery-bool']]);
	echo Html::endTag('div');

	echo Html::endTag('div');
	
	$this->registerJs("
	$('#" . Html::getInputId($model, '[' . $index . ']use_server') . "').change(function(){
		if ($(this).val() == 1){
			$('#" . Html::getInputId($model, '[' . $index . ']use_zip') . "').val(1).prop('readonly', true).checkboxX('refresh');
		}
		else{
			$('#" . Html::getInputId($model, '[' . $index . ']use_zip') . "').prop('readonly', false).checkboxX('refresh');
		}
	});
	");
}
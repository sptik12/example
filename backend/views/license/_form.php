<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\License */
/* @var $form yii\widgets\ActiveForm */

$form = ActiveForm::begin([
	'id' => 'license-form',
	'livePageWarning' => true,
	'validateOnBlur' => false,
	'validateOnChange' => false,
]);

FormCard::begin([
	'icon' => $model->isNewRecord ? Html::ICON_GENERATE : Html::ICON_EDIT,
	'title' => $model->getProductTitle(),
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
	//'collapse' => false,
]);

if (count($licenses) > 1){
	echo Html::tag('li', $this->render('/license/_grid', ['models' => $licenses, 'buttons' => 0]), ['class' => 'list-group-item form-grid']);
}

echo Html::beginTag('li', ['class' => 'list-group-item']);

echo $form->errorSummary(ArrayHelper::merge([$model], $licenseUpgrades, array_values($deliveries), array_values(ArrayHelper::getValue($trait_values, $model->license_type_name, [])))); 

echo $form->field($model, 'company')->textInput(['maxlength' => true]);

echo $form->field($model, 'owner_name')->textInput(['maxlength' => true]);

echo $form->field($model, 'email_address')->textInput(['maxlength' => true]);

if ($model->isNewRecord) {
	$maxLicenseQuantity = $model->getSetting('maxLicenseQuantity', 30);
	$quantity = $form->field($model, 'quantity')->dropDownList($this->context->getQuantityItems($maxLicenseQuantity), []);
	if ($maxLicenseQuantity <= 1){
		$quantity =  Html::tag('div', $quantity, ['class' => 'd-none']);
	}
	echo $quantity;
}
else{
	$this->registerJs("
	$('#" . Html::getInputId($model, 'license_type_name') . "').change(function(){
		$('.trait-values').collapse('hide').find('input, textarea').prop('disabled', true);
		$('#trait-values-' + $(this).val()).collapse('show').find('input, textarea').prop('disabled', false);
		$('#trait-values-' + $(this).val()).find('.trait-bool').each(function() { $(this).checkboxX('refresh') });
	});
	");			
	echo $form->field($model, 'license_type_name')->dropDownList($model->getLicenseTemplateItems(), ['prompt' => Yii::t('app', 'Select Type')]);
}
		
echo Html::endTag('li');

FormCard::end();

foreach($trait_values as $license_template_id => $traitValues){
	echo Html::beginTag('div', ['id' => 'trait-values-' . $license_template_id, 'class' => 'trait-values collapse' . (($model->license_type_name == $license_template_id) ? ' show' : '')]);
	if (count($traitValues)){
		echo $this->render('form/_trait-values', ['form' => $form, 'models' => $traitValues, 'disabled' => ($model->license_type_name != $license_template_id)]);
	}
	echo Html::endTag('div');
}

if ($model->isNewRecord) {

	if ($model->isSupportEnabled()) {
		echo $this->render('form/_support', ['form' => $form, 'model' => $model]);
	}

	echo $this->render('form/_advanced', ['form' => $form, 'model' => $model]);
	echo $this->render('form/_upgrade-protection', ['form' => $form, 'model' => $model]);
}

echo $this->render('form/_additional', ['form' => $form, 'model' => $model]);

//echo $this->render('form/_attachments', ['form' => $form, 'model' => $model]);

if (count($licenses) < 2){
	//echo $this->render('form/_parents', ['form' => $form, 'model' => $model]);
	
	echo $this->render('form/_license_upgrades', ['form' => $form, 'model' => $model, 'licenseUpgrades' => $licenseUpgrades]);
}

$footer = Html::a($model->isNewRecord ? Yii::t('app', 'Back') : Yii::t('app', 'Cancel'), $route, ['class' => 'btn btn-default', 'data-pjax' => 0])
	. Html::submitButton($model->isNewRecord ? Yii::t('app', 'Generate') : Yii::t('app', 'Save'), ['class' => 'btn btn-primary ml-2']);


echo $this->render('form/_deliveries', ['form' => $form, 'model' => $model, 'deliveries' => $deliveries, 'footer' => $footer]);

ActiveForm::end();

<?php

use common\helpers\Html;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use yii\widgets\Pjax;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\User */

$this->title = Yii::t('app', 'Choose License Type');

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('app', 'Choose License Type');

$this->params['pjax_reload_on_change_product'] = 'add-license-pjax';

Pjax::begin(['id' => 'add-license-pjax', 'timeout' => 5000]);

$form = ActiveForm::begin(['id' => 'add-license-form', 'type' => ActiveForm::TYPE_HORIZONTAL]);

$header = Html::icon('cog', ['class' => 'float-right', 'style' => 'margin-top:.2rem', 'title' => Yii::t('app', 'Open Version'), 'data-toggle' => 'collapse', 'data-target' => '#version-container']);

FormCard::begin([
	'icon' => Html::ICON_GENERATE,
	'title' => Yii::$app->user->identity->getProductTitle(false, false),
	'header' => $header,
	'footer' => Html::a(Yii::t('app', 'Cancel'), ['/license'], ['class' => 'btn btn-default offset-md-3 mr-2', 'data-pjax' => 0]) . 
		Html::submitButton(Yii::t('app', 'Next'), ['class' => 'btn btn-primary']),
]);

echo $form->errorSummary($model); 

if ($items = $model->getLicenseTemplateItems()){
	echo $form->field($model, 'license_template_id')->radioList($items, []);
}

echo Html::tag('div', $form->field($model, 'version')->dropDownList($model::getVersionItems($model->product_id), ['prompt' => Yii::t('app', 'Select Version')]), ['id' => 'version-container', 'class' => 'collapse']);

echo Html::tag('div', $form->field($model, 'product_id')->hiddenInput([])->label(false), ['class' => 'd-none']);

FormCard::end();

ActiveForm::end();

Pjax::end();

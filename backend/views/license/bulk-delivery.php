<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$this->title = (count($licenses) > 1) ? Yii::t('app', 'Add Deliveries to {count} Licenses', ['count' => count($licenses)]) : Yii::t('app', 'Add Deliveries');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
if(count($licenses) == 1 && ($license = reset($licenses))){
	$this->params['breadcrumbs'][] = ['label' => $license->id, 'url' => $license->route];
}
$this->params['breadcrumbs'][] = $this->title;

$form = ActiveForm::begin(['id' => 'license-form' , 'livePageWarning' => true]);

FormCard::begin([
	'icon' => Html::ICON_LICENSE,
	'title' => (($count = count($licenses)) > 1) ? Yii::t('app', 'Delivered {count} {product} Licenses', ['count' => $count, 'product' => $model->getProductTitle()]) : Yii::t('app', 'Delivered {product} License', ['product' => $model->getProductTitle()]),
	'collapse' => false,
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
]);

if (count($licenses)){
	echo Html::tag('li', $this->render('/license/_grid', ['models' => $licenses, 'buttons' => 0]), ['class' => 'list-group-item form-grid']);
}

echo Html::activeHiddenInput($model, 'email_address');

FormCard::end();

$footer = Html::a(Yii::t('app', 'Cancel'), $route, ['class' => 'btn btn-default', 'data-pjax' => 0])
	. Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary ml-2']);

echo $this->render('form/_deliveries', ['form' => $form, 'model' => $model, 'deliveries' => $deliveries, 'footer' => $footer]);

ActiveForm::end();

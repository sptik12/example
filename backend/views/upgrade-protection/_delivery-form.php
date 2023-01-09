<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$form = ActiveForm::begin(['id' => 'license-form' , 'livePageWarning' => true]);

FormCard::begin([
	'icon' => Html::ICON_PROTECTION,
	'title' => (($count = count($upgradeProtections)) > 1) ? Yii::t('app', 'Delivered {count} Annual Maintenances and Supports', ['count' => $count]) : Yii::t('app', 'Delivered Annual Maintenance and Support'),
	'collapse' => false,
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
]);

if (count($upgradeProtections)){
	echo Html::tag('li', $this->render('/upgrade-protection/_grid', ['models' => $upgradeProtections]), ['class' => 'list-group-item form-grid']);
	//echo Html::tag('div', $this->render('_form-upgrade-protections', ['models' => $upgradeProtections]), ['class' => 'mb-3']);
}

//echo $form->errorSummary(ArrayHelper::merge([$model], array_values($deliveries))); 

echo Html::activeHiddenInput($model, 'email_address');

FormCard::end();

$footer = Html::a(Yii::t('app', 'Cancel'), $route, ['class' => 'btn btn-default', 'data-pjax' => 0])
	. Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary ml-2']);

echo $this->render('/license/form/_deliveries', ['form' => $form, 'model' => $model, 'deliveries' => $deliveries, 'footer' => $footer]);

ActiveForm::end();

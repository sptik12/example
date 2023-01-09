<?php

use common\helpers\Html;
use common\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\LicenseSearch */

$form = ActiveForm::begin([
	'id' => 'license-find-form',
	'type' => ActiveForm::TYPE_INLINE,
	'action' => ['index', 'clear' => 1],
	'method' => 'get',
	'enableClientValidation' => false,
	'options' => ['data-pjax' => 1],
]);
	
	echo $form->field($model, 'phrase',	[
		'addon' => [
			'append' => [
				'content' => Html::submitButton(Html::icon('search'), ['class' => 'btn btn-outline-secondary collapse-advanced', 'title' => Yii::t('app', 'Search')]),
				'asButton' => true
			]
		]
	])->textInput(['placeholder' => Yii::t('app', 'Search'), 'style' => 'min-width: 300px;']);

ActiveForm::end();
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
	'icon' => 'plus-square',
	'title' => Yii::t('app', 'Additional'),
	'show' => count($model->attachment_ids) || $model->hasErrors('attachment_ids') || strlen($model->license_id_prefix) || strlen($model->details),
]);

if ($model->isNewRecord) {

	echo $form->field($model, 'license_id_prefix')->textInput(['maxlength' => true]);

	echo $form->field($model, 'details')->textInput(['maxlength' => true]);
}

if ($model->attachment_ids){
	echo $form->field($model, 'attachment_ids')->widget(Select2::classname(), [
		'data' => ArrayHelper::map(Attachment::find()->andWhere(['id' => $model->attachment_ids])->all(), 'id', 'file_name'),
		'options' => ['multiple' => true],
		'pluginOptions' => [
			'allowClear' => false,
		],
	]);
}
echo $form->field($model, 'files[]')->fileInput(['class' => 'form-control-file', 'multiple' => true, 'accept'=> '.' . implode(', .' , Yii::$app->params['attachment.extensions'])])
	->hint('To select more than one file, you may need to use the ctrl or shift keys.');

Card::end();

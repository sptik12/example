<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\widgets\Select2;
use common\models\Attachment;

/* @var $this yii\web\View */
/* @var $models array of common\models\License */

Card::begin([
	'icon' => 'paperclip',
	'title' => Yii::t('app', 'Attachments'),
	'show' => count($model->attachment_ids) || $model->hasErrors('attachment_ids'),
]);

if ($model->attachment_ids){
	echo $form->field($model, 'attachment_ids')->widget(Select2::classname(), [
		'data' => ArrayHelper::map(Attachment::find()->andWhere(['id' => $model->attachment_ids])->all(), 'id', 'file_name'),
		'options' => ['multiple' => true],
		'pluginOptions' => [
			'allowClear' => false,
		],
	]);
}
echo $form->field($model, 'files[]')->fileInput(['multiple' => true, 'accept'=> '.' . implode(', .' , Yii::$app->params['attachment.extensions'])])
	->hint('To select more than one file, you may need to use the ctrl or shift keys.')->label(false);

Card::end();

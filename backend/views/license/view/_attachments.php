<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;
use common\widgets\ViewCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */

if ($attachments = $model->attachments){

	ViewCard::begin([]);

	echo DetailView::widget([
		'model' => $model,
		'attributes' => [
			[
				'label' => (count($attachments) > 1) ? Yii::t('app', 'Attachments') : Yii::t('app', 'Attachment'),
				'value' => implode(', ', ArrayHelper::getColumn($attachments, 'link')),
				'format' => 'raw',
			],
		],
	]);

	ViewCard::end();
}
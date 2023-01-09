<?php

use common\helpers\Html;
use common\widgets\Tabs;

/* @var $this yii\web\View */
/* @var $text string */

echo  Tabs::widget([
	'items' => [
		[
			'label' => Yii::t('app', 'HTML'), 
			'active' => true, 
			'content' => $content,
		],
		[
			'label' => Yii::t('app', 'Plain Text'),
			'content' => $text,
		]
	]
]);

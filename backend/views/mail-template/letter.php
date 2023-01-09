<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;

/* @var $this yii\web\View */

$this->title = ArrayHelper::getValue($params, 'subject');

if ($params) {
	echo $this->render('@common/mail/' . ArrayHelper::getValue($letter, 'view'), $params);

	if ($view = ArrayHelper::getValue($letter, 'text')) {
		$this->params['render']['/mail-template/_letter-menu'] = ['text' => Html::tag('pre',
			$this->render('@common/mail/' . $view, $params), ['style' => 'white-space: pre-wrap;'])];
	}
}
else {
	echo 'Template parameters list is empty, template cannot be displayed';
}
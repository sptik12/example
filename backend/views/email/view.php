<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\Email */

$this->title = $model->address;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Emails'), 'url' => ['/email']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['reload_on_change_product'] = 1;

echo $this->render('_view', ['model' => $model, 'options' => ['icon' => Html::ICON_EMAIL, 'title' => Yii::t('app', 'Email Details')]]);


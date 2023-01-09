<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$this->title = Yii::t('app', 'Add Annual Maintenance and Support');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['/license']];
$this->params['breadcrumbs'][] = ['label' => $model->license->id, 'url' => $model->license->route];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Annual Maintenances and Supports'), 'url' => ['/upgrade-protection', 'id' => $model->license->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Add');

echo $this->render('_form', [
	'model' => $model,
	'deliveries' => $deliveries,
	'route' => $route,
]);
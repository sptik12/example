<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$this->title = Yii::t('app', 'Add Deliveries');

if (Yii::$app->user->can('license')){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['/license']];
	$this->params['breadcrumbs'][] = ['label' => $model->license->id, 'url' => $model->license->route];
}
else{
	$this->params['breadcrumbs'][] = Yii::t('app', 'Licenses');
	$this->params['breadcrumbs'][] = $model->license->id;
}

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Annual Maintenances and Supports'), 'url' => ['/upgrade-protection', 'id' => $model->license->id]];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => $model->route];

$this->params['breadcrumbs'][] = Yii::t('app', 'Add Deliveries');

echo $this->render('_delivery-form', [
	'model' => $model,
	'deliveries' => $deliveries,
	'upgradeProtections' => [$model->id => $model],
	'route' => $route,
]);

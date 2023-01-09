<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$this->title = (count($licenses) > 1) ? Yii::t('app', 'Add AMS to {count} Licenses', ['count' => count($licenses)]) : Yii::t('app', 'Add Annual Maintenance and Support');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['/license']];

if(count($licenses) == 1 && ($license = reset($licenses))){
	$this->params['breadcrumbs'][] = ['label' => $license->id, 'url' => $license->route];
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Annual Maintenances and Supports'), 'url' => ['/upgrade-protection', 'id' => $license->id]];
}
$this->params['breadcrumbs'][] = Yii::t('app', 'Add');

echo $this->render('_bulk-form', [
	'model' => $model,
	'deliveries' => $deliveries,
	'route' => $route,
	'licenses' => $licenses,
]);
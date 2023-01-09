<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$this->title = (count($licenses) > 1) ? Yii::t('app', 'Update {count} Licenses', ['count' => count($licenses)]) : Yii::t('app', 'Update');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
if(count($licenses) == 1 && ($license = reset($licenses))){
	$this->params['breadcrumbs'][] = ['label' => $license->id, 'url' => $license->route];
}
$this->params['breadcrumbs'][] = $this->title;

echo $this->render('_form', [
	'model' => $model,
	'trait_values' => $trait_values,
	'deliveries' => $deliveries,
	'licenseUpgrades' => $licenseUpgrades,
	'route' => $route,
	'licenses' => $licenses,
]);


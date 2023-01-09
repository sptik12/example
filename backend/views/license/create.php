<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$this->title = Yii::t('app', 'Generating {type} Licenses', ['type' => $model->license_type_name]);

//$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Choose License Type'), 'url' => ['add', 'lt' => $model->license_type_name]];
$this->params['breadcrumbs'][] = $model->license_type_name;

echo $this->render('_form', [
	'model' => $model,
	'trait_values' => $trait_values,
	'deliveries' => $deliveries,
	'licenseUpgrades' => $licenseUpgrades,
	'route' => $route,
	'licenses' => [],
]);


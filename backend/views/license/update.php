<?php

use common\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$this->title = Yii::t('app', 'Update License');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Update');

echo $this->render('_form', [
	'model' => $model,
	'trait_values' => $trait_values,
	'deliveries' => $deliveries,
	'licenseUpgrades' => $licenseUpgrades,
	'route' => $route,
	'licenses' => [$model],
]);


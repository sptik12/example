<?php

use common\helpers\Html;
use common\helpers\ArrayHelper;
use common\widgets\ActiveForm;
use common\widgets\FormCard;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$this->title = (count($upgradeProtections) > 1) ? Yii::t('app', 'Add Deliveries to {count} Annual Maintenances and Supports', ['count' => count($upgradeProtections)]) : Yii::t('app', 'Add Deliveries');

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Emails'), 'url' => ['/email']];
$this->params['breadcrumbs'][] = ['label' => $model->email->address, 'url' => $model->email->route];
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Annual Maintenances and Supports'), 'url' => ['/upgrade-protection/email', 'id' => $model->email_id]];

if(count($upgradeProtections) == 1 && ($upgradeProtection = reset($upgradeProtections))){
	$this->params['breadcrumbs'][] = ['label' => $upgradeProtection->name, 'url' => $upgradeProtection->route];
}
$this->params['breadcrumbs'][] = Yii::t('app', 'Add Deliveries');

echo $this->render('_delivery-form', [
	'model' => $model,
	'deliveries' => $deliveries,
	'upgradeProtections' => $upgradeProtections,
	'route' => $route,
]);


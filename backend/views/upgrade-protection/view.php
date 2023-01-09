<?php

use common\helpers\Html;
use common\widgets\btnGroup;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model common\models\UpgradeProtection */

$this->title = $model->name;
if (Yii::$app->user->can('license')){
	$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['/license']];
	$this->params['breadcrumbs'][] = ['label' => $model->license->id, 'url' => $model->license->route];
}
else{
	$this->params['breadcrumbs'][] = Yii::t('app', 'Licenses');
	$this->params['breadcrumbs'][] = $model->license->id;
}
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Annual Maintenances and Supports'), 'url' => ['/upgrade-protection', 'id' => $model->license->id]];
$this->params['breadcrumbs'][] = $model->name;

$buttons = [];
$buttons[] = Html::a(Html::icon(Html::ICON_PROTECTION, ['class' => 'mr-2']) . Yii::t('app', 'Annual Maintenances and Supports'), ['/upgrade-protection', 'id' => $model->license->id], ['class' => 'btn btn-outline-secondary']);
if($count = $model->getEventLogsCount()){
	$buttons[] = Html::a(Html::icon(Html::ICON_EVENT_LOG, ['class' => 'mr-2']) . Yii::t('app', 'Change Log ({count})', ['count' => $count]), $model->getLogRoute(), ['class' => 'btn btn-outline-secondary']);
}
if(!$model->license->hasLicenseInvalidations()){
	if (Yii::$app->user->can('delivery-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_DELIVERY, ['class' => 'mr-2']) . Yii::t('app', 'Add Deliveries'), ['delivery', 'id' => $model->id], ['class' => 'btn btn-outline-dark', 'title' => Yii::t('app', 'Add Deliveries')]);
	}
}

echo btnGroup::widget(['buttons' => $buttons]);

echo $this->render('_view', ['model' => $model, 'options' => [
	'icon' => Html::ICON_PROTECTION,
	'title' => Yii::t('app', 'Annual Maintenance and Support for <i>{id}</i>', ['id' => Html::encode($model->license->id)]),
]]);
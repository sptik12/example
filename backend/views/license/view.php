<?php

use common\helpers\Html;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$this->title = $model->id;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Licenses'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$this->params['reload_on_change_product'] = 1;

$buttons = [];

$buttons[] = Html::a(Html::icon(Html::ICON_LICENSE, ['class' => 'mr-2']) . Yii::t('app', 'Licenses'), ['index'], ['class' => 'btn btn-outline-secondary']);
if($count = $model->getEventLogsCount()){
	$buttons[] =Html::a(Html::icon(Html::ICON_EVENT_LOG, ['class' => 'mr-2']) . Yii::t('app', 'Change Log ({count})', ['count' => $count]), ['/event-log', 'EventLogSearch[keyword]' => $model->id, 'clear' => 1], ['class' => 'btn btn-outline-secondary',]);
}
if(!$model->hasLicenseInvalidations()){
	if (Yii::$app->user->can('license-update')){
		$buttons[] = Html::a(Html::icon(Html::ICON_UPDATE, ['class' => 'mr-2']) . Yii::t('app', 'Update'), ['update', 'id' => $model->id], ['class' => 'btn btn-outline-primary']);
	}
	if ($model->isSupportEnabled() && Yii::$app->user->can('support-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_SUPPORT, ['class' => 'mr-2']) . Yii::t('app', 'Add Support'), ['/support/create', 'id' => $model->id], ['class' => 'btn btn-outline-dark', 'title' => Yii::t('app', 'Add new Support')]);
	}
	if (Yii::$app->user->can('delivery-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_DELIVERY, ['class' => 'mr-2']) . Yii::t('app', 'Add Deliveries'), ['bulk-delivery', 'ids' => [$model->id]], ['class' => 'btn btn-outline-dark', 'title' => Yii::t('app', 'Add new Deliveries')]);
	}
	if (Yii::$app->user->can('upgrade-protection-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_PROTECTION, ['class' => 'mr-2']) . Yii::t('app', 'Add AMS'), ['/upgrade-protection/create', 'id' => $model->id], ['class' => 'btn btn-outline-dark', 'title' => Yii::t('app', 'Add Annual Maintenance and Support')]);
	}
	if (Yii::$app->user->can('license-invalidation')){
		$buttons[] = Html::a(Html::icon(Html::ICON_INVALIDATE, ['class' => 'mr-2']) . Yii::t('app', 'Invalidate'), ['/license-invalidation/create', 'id' => $model->id], ['class' => 'btn btn-outline-danger']);
	}
}

echo btnGroup::widget(['buttons' => $buttons]);

echo $this->render('_view', ['model' => $model, 'options' => ['icon' => Html::ICON_LICENSE, 'title' => Yii::t('app', 'License Details')]]);

if ($model->hasLicenseInvalidations()){
	echo $this->render('view/_license-invalidations', ['models' => $model->licenseInvalidations]);
}

echo $this->render('view/_trait-values', ['model' => $model]);

echo $this->render('view/_upgrade-protections', ['model' => $model]);

echo $this->render('view/_additional', ['model' => $model]);

echo $this->render('view/_related', ['model' => $model]);

echo $this->render('view/_supports', ['model' => $model]);

echo $this->render('view/_deliveries', ['model' => $model]);

echo $this->render('view/_license-histories', ['model' => $model]);



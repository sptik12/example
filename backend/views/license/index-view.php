<?php

use common\helpers\Html;
use common\widgets\btnGroupVertical;

/* @var $this yii\web\View */
/* @var $model common\models\License */

$buttons = [];

$buttons[] = Html::a(Html::icon(Html::ICON_VIEW), $model->route, ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Open License Details')]);

if($count = $model->getEventLogsCount()){
	$buttons[] =Html::a(Html::icon(Html::ICON_EVENT_LOG), ['/event-log', 'EventLogSearch[keyword]' => $model->id, 'clear' => 1], ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Open Change Log ({count})', ['count' => $count])]);
}

if(!$model->hasLicenseInvalidations()){
	if (Yii::$app->user->can('license-update')){
		$buttons[] = Html::a(Html::icon(Html::ICON_UPDATE), ['/license/update', 'id' => $model->id], ['class' => 'btn btn-outline-primary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Update License')]);
	}
	if ($model->isSupportEnabled() && Yii::$app->user->can('support-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_SUPPORT), ['/support/create', 'id' => $model->id], ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Add new Support')]);
	}
	if (Yii::$app->user->can('delivery-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_DELIVERY), ['/license/bulk-delivery', 'ids' => [$model->id]], ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Add new Deliveries')]);
	}
	if (Yii::$app->user->can('upgrade-protection-create')){
		$buttons[] = Html::a(Html::icon(Html::ICON_PROTECTION), ['/upgrade-protection/create', 'id' => $model->id], ['class' => 'btn btn-outline-secondary', 'data-pjax' => 0, 'title' => Yii::t('app', 'Add Annual Maintenance and Support')]);
	}
	if (Yii::$app->user->can('license-invalidation')){
		$buttons[] = Html::a(Html::icon(Html::ICON_INVALIDATE), ['/license-invalidation/create', 'id' => $model->id], ['class' => 'btn btn-outline-danger', 'data-pjax' => 0, 'title' => Yii::t('app', 'Invalidate License')]);
	}
}
echo Html::beginTag('div', ['class' => 'row no-gutters']);

echo Html::beginTag('div', ['class' => 'col-sm-11']);

echo $this->render('_index-view', ['model' => $model, 'options' => []]);

echo Html::endTag('div');

echo Html::beginTag('div', ['class' => 'col-sm-1 pl-2']);

echo btnGroupVertical::widget(['buttons' => $buttons]);

echo Html::endTag('div');

echo Html::endTag('div');

//echo $this->render('view/_trait-values', ['model' => $model]);

//echo $this->render('view/_upgrade-protections', ['model' => $model]);

//echo $this->render('view/_parents', ['model' => $model]);

//echo $this->render('view/_children', ['model' => $model]);

//if ($model->hasLicenseInvalidations()){
//	echo $this->render('view/_license-invalidations', ['models' => $model->licenseInvalidations]);
//}

//echo $this->render('view/_supports', ['model' => $model]);

//echo $this->render('view/_deliveries', ['model' => $model]);

//echo $this->render('view/_attachments', ['model' => $model]);

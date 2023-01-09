<?php

use common\helpers\Html;
use common\widgets\grid\GridView;
use common\models\Delivery;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\LicenseSearch */
/* @var $modelProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Licenses');

$this->params['breadcrumbs'][] = $this->title;

$this->params['pjax_reload_on_change_product'] = 'license-grid-pjax';

echo Html::tag('div', $this->render('_multi_ids_search', ['model' => $searchModel]), ['id' => 'search-form-container', 'class' => 'collapse' . ($advanced ? ' show' : '')]);

$columns = [];

$template = [];

if (Yii::$app->user->can('license-update')) {
	$template[] = '{update}';
}

if ($searchModel->isSupportEnabled() && Yii::$app->user->can('support-create')) {
	$template[] = '{support}';
}

if (Yii::$app->user->can('license-invalidation')) {
	$template[] = '{invalidate}';
}

if (Yii::$app->user->can('upgrade-protection-create')) {
	$template[] = '{upgrade-protection}';
}

//if ($template) {
	$columns[] = [
		'class' => 'common\widgets\grid\ActionColumn',
		'header' => $this->render('/filter/_button', ['route' => '/license']),
		'headerOptions' => ['class' => 'kv-align-bottom kv-align-left'],
		'template' => implode(' ', $template),
		'buttons' => [
			'invalidate' => function ($url, $model) {
				return Html::a(Html::icon(Html::ICON_INVALIDATE, ['class' => 'ml-1']), ['/license-invalidation/create', 'id' => $model->id], ['class' => 'text-red', 'title' => Yii::t('app', 'Invalidate'), 'data-pjax' => '0']);
			},
			'upgrade-protection' => function ($url, $model) {
				return Html::a(Html::icon(Html::ICON_PROTECTION, ['class' => 'ml-1']), ['/upgrade-protection/create', 'id' => $model->id], ['class' => $model->upgradeProtectionCss, 'title' => Yii::t('app', 'Add Annual Maintenance and Support'), 'data-pjax' => '0']);
			},
			'support' => function ($url, $model) {
				return Html::a(Html::icon(Html::ICON_SUPPORT, ['class' => 'ml-1']), ['/support/create', 'id' => $model->id], ['class' => $model->supportCss, 'title' => Yii::t('app', 'Add Support'), 'data-pjax' => '0']);
			},
		],
		'visibleButtons' => [
			'invalidate' => function ($model) {
				return !$model->hasLicenseInvalidations();
			},
			'update' => function ($model) {
				return !$model->hasLicenseInvalidations();
			},
			'upgrade-protection' => function ($model) {
				return !$model->hasLicenseInvalidations();
			},
			'support' => function ($model) {
				return $model->isSupportEnabled() && !$model->hasLicenseInvalidations();
			},
		],
	];
//}

if (Yii::$app->user->can('license-update') || Yii::$app->user->can('upgrade-protection-create') || Yii::$app->user->can('support-create') || Yii::$app->user->can('delivery-create')) {
	$columns[] = [
		'class' => 'common\widgets\grid\CheckboxColumn',
		'noSelectionMessage' => Yii::t('app', 'No Records selected'),
		'visible' => $dataProvider->totalCount && $searchModel->product_id,
		'pjax' => false,
		'checkboxOptions' => function ($model, $key, $index, $column) {
			if ($model->hasLicenseInvalidations()) {
				return ['style' => ['display' => 'none'], 'disabled' => true];
			}
			return ['value' => $key];
		},
	];
}

$columns[] = [
	'class' => 'common\widgets\grid\ExpandRowColumn',
	'header' => '',
	'value' => function ($model, $key, $index, $column) {
		return GridView::ROW_COLLAPSED;
	},
	'detailUrl' => Url::to(['index-view']),
	//'enableRowClick' => false,
	'visible' => $dataProvider->totalCount,
];

$columns[] = [
	'attribute' => 'company',
	'content' => function ($model) {
		$options = ['data-pjax' => 0, 'title' => Yii::t('app', 'Open License Details'), 'class' => 'open-details'];
		if ($model->isExpired()) {
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		if ($model->hasLicenseInvalidations()) {
			Html::addCssClass($options, $model->getCssClass('invalidated'));
		}
		return Html::a(Html::encode($model->company), $model->getRoute(), $options);
	},
];

$columns[] = [
	'attribute' => 'owner_name',
	'content' => function ($model) {
		$value = '';
		if ($model->hasLicenseUpgrades()) {
			$value .= Html::icon(Html::ICON_DOWN, ['class' => 'pr-1', 'title' => (count($model->licenseUpgrades) > 1) ? Yii::t('app', 'Has Upgraded Licenses') : Yii::t('app', 'Has Upgraded License')]);
		}
		$options = ['data-pjax' => 0, 'title' => Yii::t('app', 'Open License Details'), 'class' => 'open-details'];
		if ($model->isExpired()) {
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		if ($model->hasLicenseInvalidations()) {
			Html::addCssClass($options, $model->getCssClass('invalidated'));
		}
		$value .= Html::a(Html::encode($model->owner_name), $model->getRoute(), $options);
		if ($model->hasParentLicenseUpgrades()) {
			$value .= Html::icon(Html::ICON_UP, ['class' => 'pl-1', 'title' => Yii::t('app', 'Was Upgraded')]);
		}
		return $value;
	},
];

$columns[] = [
	'attribute' => 'email_address',
	'content' => function ($model) {
		if ($email = $model->email) {
			$options = ['data-pjax' => 0, 'title' => Yii::t('app', 'Open License Details'), 'class' => 'open-details'];
			if ($model->isExpired()) {
				Html::addCssClass($options, $model->getCssClass('expired'));
			}
			if ($model->hasLicenseInvalidations()) {
				Html::addCssClass($options, $model->getCssClass('invalidated'));
			}
			return Html::a(Html::encode($email->address), $model->getRoute(), $options) . $email->getLicenseCount(['class' => 'badge badge-light ml-1']);
		}
	},
	'contentOptions' => ['class' => 'text-nowrap'],
];

$columns[] = [
	'attribute' => 'created_at',
	'format' => 'datetime',
	'contentOptions' => ['class' => 'text-nowrap'],
	'filter' => Html::activeDropDownList(
		$searchModel,
		'period',
		$searchModel->getPeriodItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'Any')]
	),
];
/*
$columns[] = [
	'attribute' => 'not_after_date',
	'label' => Yii::t('app', 'Valid until'),
	'format' => 'date',
	'filter' => Html::activeDropDownList(
		$searchModel,
		'valid_period',
		$searchModel->getPeriodItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'Any')]
	),
];
*/
$columns[] = [
	'attribute' => 'license_type_id',
	'label' => Yii::t('app', 'Type'),
	'value' => function ($model) {
		return $model->getLicenseTypeDisplayName();
	},
	'filter' => Html::activeDropDownList(
		$searchModel,
		'license_type_id',
		$searchModel->getLicenseTypeItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'Any')]
	),
];

$columns[] = [
	'attribute' => 'upgrade_protection_end_date',
	'label' => Yii::t('app', 'AMS Date'),
	'value' => function ($model) {
		return $model->getUpgradeProtectionEndDate();
	},
	'contentOptions' => function ($model, $key, $index, $column) {
		return['class' => $model->upgradeProtectionCss, 'style' => 'font-weight: normal;'];
	},
	'format' => 'date',
	'filter' => Html::activeDropDownList(
		$searchModel,
		'up_period',
		$searchModel->getPeriodItems(),
		['class' => 'form-control', 'prompt' => Yii::t('app', 'Any')]
	),
];

$columns[] = [
	'attribute' => 'product_info_id',
	'label' => $searchModel->product_id ? Yii::t('app', 'Version') : Yii::t('app', 'Product'),
	'value' => function ($model) use ($searchModel) {
		return $searchModel->product_id ? ArrayHelper::getValue($model, 'productInfo.commercial_name') : ArrayHelper::getValue($model, 'productInfo.shortName');
	},
	'contentOptions' => function ($model, $key, $index, $column) {
		return ['title' => ArrayHelper::getValue($model, 'productInfo.fullVersion')];
	},
	'filter' => Html::activeDropDownList(
		$searchModel,
		'product_info_id',
		$searchModel->getProductInfoItems($options),
		['class' => 'form-control select-group', 'encode'=>false, 'prompt' => Yii::t('app', 'Any'), 'options' => $options]
	),
];

$toolbar = [];

$toolbar[] = [
	'content' => $this->render('_search', ['model' => $searchModel]),
];

$toolbar[] = [
	'content' => Html::button(Html::icon('search-plus', ['class' => 'mr-2']) . Yii::t('app', 'Multi-IDs Search') , ['class' => 'btn btn-outline-secondary', 'data-toggle' => 'collapse', 'data-target' => '#search-form-container', 'title' => Yii::t('app', 'Multi-IDs Search')]),
];

if (false && Yii::$app->user->can('license-create')) {
	$toolbar[] = [
		'content' => Html::a(Html::icon(Html::ICON_GENERATE, ['class' => 'mr-2']) . Yii::t('app', 'Generate'), ['add'], ['class' => 'btn btn-primary', 'title' => Yii::t('app', 'Generate new Licenses'), 'data-pjax' => 0]),
	];
}

if ($searchModel->product_id) {
	$bulk = [];
	if (Yii::$app->user->can('license-update')) {
		$bulk[] = Html::a(Html::icon(Html::ICON_UPDATE, ['class' => 'mr-2']) . Yii::t('app', 'Update'), ['bulk-update'], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-primary bulk disabled', 'title' => Yii::t('app', 'Update Licenses')]);
	}
	if ($searchModel->isSupportEnabled() && Yii::$app->user->can('support-create')) {
		$bulk[] = Html::a(Html::icon(Html::ICON_SUPPORT, ['class' => 'mr-2']) . Yii::t('app', 'Add Support'), ['/support/bulk-create', 'pid' => $searchModel->product_id, 'ver' => $searchModel->version], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-dark bulk disabled', 'title' => Yii::t('app', 'Add Support')]);
	}
	if (Yii::$app->user->can('delivery-create')) {
		$bulk[] = Html::a(Html::icon(Html::ICON_DELIVERY, ['class' => 'mr-2']) . Yii::t('app', 'Add Deliveries'), ['bulk-delivery', 'pid' => $searchModel->product_id, 'ver' => $searchModel->version], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-dark bulk disabled', 'title' => Yii::t('app', 'Add Deliveries')]);
	}
	if (Yii::$app->user->can('upgrade-protection-create')) {
		$bulk[] = Html::a(Html::icon(Html::ICON_PROTECTION, ['class' => 'mr-2']) . Yii::t('app', 'Add AMS'), ['/upgrade-protection/bulk-create', 'pid' => $searchModel->product_id, 'ver' => $searchModel->version], ['data-pjax' => 0, 'disabled' => 1, 'class' => 'btn btn-outline-dark bulk disabled', 'title' => Yii::t('app', 'Add Annual Maintenance and Support')]);
	}

	if ($bulk) {
		$toolbar[] = [
			'content' => implode(' ', $bulk),
		];
	}
}

//$toolbar[] = '{toggleData}';

$title = [];

if ($searchModel->support_id) {
	$title[] = Yii::t('app', 'Support: {id}', ['id' => Html::a(Html::encode($searchModel->support_id), ['/support/view', 'id' => $searchModel->support_id], ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);
}

if ($searchModel->delivery_id && ($delivery = Delivery::findOne($searchModel->delivery_id))) {
	$title[] = Yii::t('app', 'Delivery: {id}', ['id' => Html::a(Html::encode($delivery->name), $delivery->route, ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);
}

if ($searchModel->owner_id && ($user = $searchModel->user)) {
	$title[] = Yii::t('app', 'Initiator: {name}', ['name' => Html::a(Html::encode($user->name), $user->route, ['data-pjax' => 0, 'class' => 'text-white', 'title' => Yii::t('app', 'View Details')])]);
}

if (empty($title) && $searchModel->product_id) {
	$title[] = Html::encode($searchModel->getProductTitle());
}

echo GridView::widget([
	'id' => 'license-grid',
	'dataProvider' => $dataProvider,
	'filterModel' => $searchModel,
	'itemLabelSingle' => 'license',
	'itemLabelPlural' => 'licenses',
	'panel' => [
		'heading' => Html::icon(Html::ICON_LICENSE, ['class' => 'mr-3']) . implode(', ', $title),
	],
	'toolbar' => $toolbar,
	'columns' => $columns,
	'rowOptions' => function ($model, $index, $widget, $grid) {
		$options = ['title' => $model->id, 'class' => $model->getCssClass($model->getLicenseTypeName())];
		if ($model->isExpired()) {
			Html::addCssClass($options, $model->getCssClass('expired'));
		}
		if ($model->hasLicenseInvalidations()) {
			Html::addCssClass($options, $model->getCssClass('invalidated'));
		}
		return $options;
	},
]);

if ((count($searchModel->license_ids) > $dataProvider->totalCount) && ($message=$searchModel->getNotFoundMessage())){
	$this->context->setWarningAlert($message, false);
}

$this->registerJs( "
$('body').on('click', '.collapse-advanced', function(){
	 $('#search-form-container').collapse('hide');
	 $('.alert').hide();
});
");
<?php

use common\helpers\Html;
use common\widgets\grid\GridView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ArrayDataProvider */

$this->title = Yii::t('app', 'Notifications');

$this->params['breadcrumbs'][] = $this->title;

$this->params['pjax_reload_on_change_product'] = 'notifications-grid-pjax';

echo GridView::widget([
	'id' => 'notifications-grid',
	'dataProvider' => $dataProvider,
	'export' => false,
	'itemLabelSingle' => 'template',
	'itemLabelPlural' => 'templates',
	'panel' => [
		'heading' => Html::icon('clock'),
		'footer' => false
	],
	'toolbar' => false,
	'columns' => [
		[
			'attribute' => 'product_id',
			'label' => Yii::t('app', 'Product'),
		],
		[
			'attribute' => 'name',
			'format' => 'raw',
			'content' => function ($data) {
				return Html::a(Html::encode($data['name']),
					['letter', 'product_id' => $data['product_id'], 'mail_template_id' => $data['mail_template_id'], 'letter_id' => $data['id']],
					['title' => Yii::t('app', 'View Letter'), 'data-pjax' => 0, 'data-toggle' => 'modal', 'data-target' => '#main-modal']);
			},
		],
		[
			'attribute' => 'type',
			'label' => Yii::t('app', 'Type'),
			'contentOptions' => ['class' => ''],
		],
		[
			'attribute' => 'days',
			'label' => Yii::t('app', 'Days'),
			'contentOptions' => ['class' => ''],
		],
		[
			'class' => 'common\widgets\grid\BooleanColumn',
			'attribute' => 'enabled',
			'label' => Yii::t('app', 'Enabled'),
		],
		[
			'class' => 'common\widgets\grid\ActionColumn',
			'template' => '{send}',
			'buttons' => [
				'send' => function ($url, $data) {
					return Html::a(Html::icon(Html::ICON_EMAIL),
						['send', 'product_id' => $data['product_id'], 'mail_template_id' => $data['mail_template_id'], 'letter_id' => $data['id']],
						['title' => Yii::t('app', 'Send Test Email'), 'data-pjax' => 0, ]);
				},
			],
		]
	]
]);


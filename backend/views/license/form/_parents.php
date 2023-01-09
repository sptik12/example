<?php

use common\helpers\Html;
use yii\helpers\JSon;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\models\License;
use yii\web\JsExpression;
use common\widgets\Select2;
use yii\helpers\Url;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $models array of common\models\License */

echo $this->render('_select2-format');

$this->registerJs("

$('#copy-parent').click(function(e){
	if (!$(this).hasClass('disabled')){
		$.ajax({
			url: " . JSon::encode(Url::to(['/license/load-same-attributes'])) . ",
			data: {'ids' : $('#" . Html::getInputId($model, 'parent_license_ids') . "').val()}
		}).done(function(data){
			if(data){
				for (var key in data) {
					$('#' + key).val(data[key]);
				} 
			}
		});
	}
	return false;
});

");

$buttons = [
	Html::button(Html::icon('copy', ['class' => 'mr-2']) . Yii::t('app', 'Copy'), ['id' => 'copy-parent', 'class' => 'btn btn-outline-secondary' . (($model->parent_license_ids) ? '' : ' disabled'), 'title' => Yii::t('app', 'Copy Upgraded Licenses Data')]),
];

$header = btnGroup::widget(['buttons' => $buttons, 'groupOptions' => ['class' => 'btn-group-sm float-right bg-white', 'style' => 'margin-top:-.37rem']]);

Card::begin([
	'icon' => Html::ICON_DOWN,
	'show' => $model->parent_license_ids ? true : false,
	'title' => Yii::t('app', 'Predecessor licenses'),
	'header' => $header,
]);

//$data = $model->parent_license_ids ? ArrayHelper::map(License::find()->andWhere(['id' => $model->parent_license_ids])->all(), 'id', 'name') : [];
$data = $model->parent_license_ids ?  ArrayHelper::getColumn(License::find()->andWhere(['id' => $model->parent_license_ids])->all(), 'viewAttributes') : [];

echo $form->field($model, 'parent_license_ids')->widget(Select2::classname(), [
	//'data' => $data,
	'options' => [
		'multiple' => true,
		'onchange' => new JsExpression (" $('#" . Html::getInputId($model, 'parent_license_ids') . " option:selected').length ? $('#copy-parent').removeClass('disabled') : $('#copy-parent').addClass('disabled'); "),
		'placeholder' => 'Search ...',
	],
	'pluginOptions' => [
		'data' =>  $data ,
		'allowClear' => false,
		'minimumInputLength' => 3,
		'language' => [
			'errorLoading' => new JsExpression("function () { return 'Waiting for results...'; }"),
		],
		'ajax' => [
			'url' => Url::to(['/license/items', 'id' => $model->id, 'pid' => $model->product_id]),
			'dataType' => 'json',
			'data' => new JsExpression('function(params) { return {q:params.term}; }')
		],
		'escapeMarkup' => new JsExpression('function (markup) { return markup; }'),
        'templateResult' => new JsExpression('formatLicense'),
        'templateSelection' => new JsExpression('formatLicenseSelection'),
	],
])->label(false);

Card::end();

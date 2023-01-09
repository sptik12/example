<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\models\Delivery;
use common\widgets\btnGroup;

/* @var $this yii\web\View */
/* @var $models array of common\models\Delivery */

$minCount = $model->isAttributeRequired('delivery_count') ? 1 : 0;

$this->registerJs("

$('.copy-email').click(function(e){
	$($(this).data('target')).val($('#" . Html::getInputId($model, 'email_address') . "').val());
});

$('#" . Html::getInputId($model, 'delivery_count') . "').change(function(e){
	var selected = $('#" . Html::getInputId($model, 'delivery_count') . "').val();
	var maxCount = " . count($deliveries) . ";
	var minCount = " . $minCount . ";

	for (index=1; index <= maxCount; index++){
		var container = $('#delivery-' + index);
		if ((index <= selected) && !container.hasClass('disabled')){
			container.collapse('show').find('input, select').prop('disabled', false);
		}
		else{
			container.collapse('hide').find('input, select').prop('disabled', true);
			container.find('input.delivery-disabled').prop('disabled', false);
		}
		container.find('.delivery-bool').each(function() { $(this).checkboxX('refresh') });
	}
	if (selected < maxCount){
		$('#deliveries-add').removeClass('disabled');
	}
});

$('#deliveries-container .close').click(function(e){
	var control = $(this);
	var container = $(control.data('target'));
	container.collapse('hide').addClass('disabled').find('input, select').prop('disabled', true);
	container.find('.delivery-bool').each(function() { $(this).checkboxX('refresh') });
	container.find('input.delivery-disabled').val(1).prop('disabled', false);
});
");

echo Html::beginTag('div', ['id' => 'deliveries-container']);

Card::begin([
	'icon' => Html::ICON_DELIVERY,
	'title' => Yii::t('app', 'Deliveries'),
	'headerOptions' => ['class' => 'bg-light', 'style' => 'padding-bottom: .37rem;'],
	'show' => true,
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
	'footer' => isset($footer) ? $footer : null,
	'collapse' => false,
	'footerOptions' => ['class' => 'text-right'],
]);

foreach ($deliveries as $index => $delivery) {
	echo Html::beginTag('li', ['id' => 'delivery-' . $index, 'class' => 'list-group-item collapse' . ($delivery->disabled ? ' disabled' : (($index <= $model->delivery_count) ? ' show' : ''))]);
	echo $this->render('_delivery', ['form' => $form, 'model' => $delivery, 'index' => $index, 'minCount' => $minCount, 'disabled' => $delivery->disabled || ($index > $model->delivery_count)]);
	echo Html::endTag('li');
}

echo Html::beginTag('li', ['class' => 'list-group-item']);

echo Html::activeHiddenInput($model, 'delivery_count');

echo Html::button(Html::icon('plus', ['class' => 'mr-2']) . Yii::t('app', 'Add'), ['id' => 'deliveries-add', 'class' => 'btn btn-outline-secondary' . (($model->delivery_count < count($deliveries))? '' : ' disabled'), 'title' => Yii::t('app', 'Add Delivery'), 'onclick' => "if (!$(this).hasClass('disabled')){ $(this).addClass('disabled'); $('#".Html::getInputId($model, 'delivery_count')."').val(+$('#".Html::getInputId($model, 'delivery_count')."').val() + 1).trigger('change'); }"]);

echo Html::endTag('li');

Card::end();

echo Html::endTag('div');

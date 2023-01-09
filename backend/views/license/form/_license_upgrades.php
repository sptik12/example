<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;
use common\widgets\Card;
use common\models\Delivery;
use common\widgets\btnGroup;
use yii\helpers\JSon;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $models array of common\models\Delivery */

echo Html::beginTag('div', ['id' => 'license-upgrades-container']);

Card::begin([
	'icon' => Html::ICON_DOWN,
	'title' => Yii::t('app', 'Predecessor licenses'),
	'headerOptions' => ['class' => 'bg-light', 'style' => 'padding-bottom: .37rem;'],
	'show' => $model->license_upgrade_count,
	//'show' => true,
	'bodyTag' => 'ul',
	'bodyClass' => 'list-group list-group-flush',
]);

foreach ($licenseUpgrades as $index => $licenseUpgrade) {
	echo Html::beginTag('li', ['id' => 'license-upgrade-' . $index, 'class' => 'list-group-item collapse' . ( $licenseUpgrade->disabled ? ' disabled' : (($index < $model->license_upgrade_count) ? ' show' : ''))]);
	echo $this->render('_license_upgrade', ['form' => $form, 'model' => $licenseUpgrade, 'index' => $index, 'disabled' => $licenseUpgrade->disabled || ($index >= $model->license_upgrade_count)]);
	echo Html::endTag('li');
}

echo Html::beginTag('li', ['class' => 'list-group-item']);

echo Html::activeHiddenInput($model, 'license_upgrade_count');

echo Html::button(Html::icon('plus', ['class' => 'mr-2']) . Yii::t('app', 'Add'), ['id' => 'license-upgrades-add', 'class' => 'btn btn-outline-secondary' . (($model->license_upgrade_count < count($licenseUpgrades))? '' : ' disabled'), 'title' => Yii::t('app', 'Add'), 'onclick' => "if (!$(this).hasClass('disabled')){ $(this).addClass('disabled'); $('#".Html::getInputId($model, 'license_upgrade_count')."').val(+$('#".Html::getInputId($model, 'license_upgrade_count')."').val() + 1).trigger('change'); }"]);

echo Html::endTag('li');

Card::end();

echo Html::endTag('div');

$this->registerJs("

$('#" . Html::getInputId($model, 'license_upgrade_count') . "').change(function(e){
	var selected = $(this).val();
	var maxCount = " . count($licenseUpgrades) . ";

	for (index=0; index < maxCount; index++){
		var container = $('#license-upgrade-' + index);
		if ((index < selected) && !container.hasClass('disabled')){
			container.collapse('show').find('input.parent-license-id').prop('disabled', false);
		}
		else{
			container.collapse('hide').find('input.parent-license-id').prop('disabled', true);
		}
	}

	if (selected < maxCount){
		$('#license-upgrades-add').removeClass('disabled');
	}
}).trigger('change');

$('#license-upgrades-container .parent-license-id').on('input typeahead:selected', function(e){
	var control = $(this);
	if (control.val().length >= 30){
		$.ajax({
			url: " . JSon::encode(Url::to(['/license/info', 'pid' => $model->product_id, 'date' => $model->created_at])) . ",
			data: {'id' : $.trim(control.val())}
		}).done(function(data){
			$(control.data('target')).html(data);
			if (data){
				control.closest('.list-group-item').find('.copy-contact').removeClass('disabled');
			}
			else{
				control.closest('.list-group-item').find('.copy-contact').addClass('disabled');
			}
		});
	}
	else{
		$(control.data('target')).html('');
		control.closest('.list-group-item').find('.copy-contact').addClass('disabled');
	}
});

$('#license-upgrades-container .copy-contact').click(function(e){
	if (!$(this).hasClass('disabled')){
		var control = $(this);
		$.ajax({
			url: " . JSon::encode(Url::to(['/license/load-same-attributes'])) . ",
			data: {'ids' : $.trim($(control.data('target')).val())}
		}).done(function(data){
			if(data){
				for (var key in data) {
					$('#' + key).val(data[key]);
				}
				$('html, body').animate({
                    scrollTop: $('#license-form').offset().top
                }, 1000);
			}
		});
	}
	return false;
});

$('#license-upgrades-container .close').click(function(e){
	var control = $(this);
	var container = $(control.data('target'));
	container.collapse('hide').addClass('disabled').find('input.parent-license-id').prop('disabled', true);
	container.find('input.license-upgrade-disabled').val(1);
});
");

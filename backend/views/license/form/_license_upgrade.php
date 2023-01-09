<?php

use common\helpers\Html;
use common\widgets\CheckboxX;
use yii\helpers\Url;
use kartik\widgets\Typeahead;

/* @var $this yii\web\View */
/* @var $model common\models\Delivery */

echo Html::button(Html::tag('span', '&times;', ['aria-hidden' => 'true']), ['class' => 'close float-right', 'style' => 'margin-left:.2rem; margin-right:-.8rem; margin-top: -.5rem;', 'aria-label' => 'Close', 'title' => Yii::t('app', 'Remove'), 'data-target' => '#license-upgrade-' . $index]);

echo Html::beginTag('div', ['class' => 'form-row']);

	echo Html::beginTag('div', ['class' => "col-md-6"]);
/*	
	echo $form->field($model, '['  . $index . ']parent_license_id',	 [
		'addon' => [
			'append' => [
				'content' => Html::button(Html::icon('copy'), ['class' => 'btn btn-outline-secondary copy-contact' . ($model->parentLicense ? '' : ' disabled'), 'data-target' => '#' . Html::getInputId($model, '[' . $index . ']parent_license_id'), 'title' => Yii::t('app', 'Copy License data')]),
				'asButton' => true
			]
		]
	])->textInput(['placeholder' => $model->getAttributeLabel('parent_license_id'), 'maxlength' => true, 'disabled' => $disabled, 'autocomplete' => 'off', 'class' => 'parent-license-id', 'data-target' => '#parent-license-container-' . $index])->label(false);
*/
	echo $form->field($model, '['  . $index . ']parent_license_id', [
		'addon' => [
			'append' => [
				'content' => Html::button(Html::icon('copy'), ['class' => 'btn btn-outline-secondary copy-contact' . ($model->parentLicense ? '' : ' disabled'), 'data-target' => '#' . Html::getInputId($model, '[' . $index . ']parent_license_id'), 'title' => Yii::t('app', 'Copy License data')]),
				'asButton' => true
			]
		]
	])->widget(Typeahead::classname(), [
		'options' => [
			'placeholder' => $model->getAttributeLabel('parent_license_id'),
			'maxlength' => true,
			//'disabled' => $disabled,
			'autocomplete' => 'off',
			'class' => 'parent-license-id',
			'data-target' => '#parent-license-container-' . $index,
		],
		'container' => ['style' => 'flex: 1;'],
		'pluginOptions' => [
			'highlight' => true,
			'minLength' => 3,
		],
		'dataset' => [
			[
				'local' => [],
				'limit' => 20,
				'display' => 'value',
				'remote' => [
					'url' => Url::to(['/license/typeahead', 'pid' => $model->product_id, 'date' => $model->created_at, 'q' => 'TERM']),
					'wildcard' => 'TERM',
				],
			],
		],
	])->label(false);
	
	echo Html::activeHiddenInput($model, '['  . $index . ']disabled', ['class' => 'license-upgrade-disabled']);
	
	echo Html::endTag('div');
	
	echo Html::beginTag('div', ['class' => "col-md-6", 'id' => 'parent-license-container-' . $index]);
	
	if ($parentLicense = $model->filterParentLicense){
		echo $this->render('../_info', ['model' => $parentLicense]);
	}
	
	echo Html::endTag('div');

echo Html::endTag('div');

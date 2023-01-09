<?php

use common\helpers\Html;
use common\models\TraitValue;
use common\widgets\CheckboxX;

switch ($model->type) {
	case TraitValue::TYPE_INT:
	case TraitValue::TYPE_FLOAT:
		echo $form->field($model, '[' . $model->trait_name_id . ']' . $model->valueId)->textInput(['maxlength' => true, 'disabled' => $disabled]);
		break;
	case TraitValue::TYPE_STRING:
		echo $form->field($model, '[' . $model->trait_name_id . ']' . $model->valueId)->textarea(['rows' => 2, 'maxlength' => true, 'disabled' => $disabled]);
		break;
	case TraitValue::TYPE_BOOL:
		echo Html::tag('div', $form->field($model, '[' . $model->trait_name_id . ']' . $model->valueId, ['showLabels' => false])->widget(CheckboxX::className(),['autoLabel' => true, 'disabled' => $disabled, 'options' => ['class' => 'trait-bool']]), ['class' => '']);
		break;
	default :
		echo $form->field($model, '[' . $model->trait_name_id . ']' . $model->valueId)->textInput(['maxlength' => true, 'disabled' => $disabled]);

}

?>
<?php

use common\helpers\Html;
use common\widgets\DetailView;
use yii\helpers\ArrayHelper;
use common\widgets\LightCard;

/* @var $this yii\web\View */
/* @var $model common\models\License */


$last_level = -1;
if ($childModels = $model->childModels){	
	$levels = ArrayHelper::getColumn($childModels, 'level');
	$level = max($levels);
	$childModels = array_reverse($childModels);
	foreach($childModels as $childModel){
		$childModel->level = $level - $childModel->level;
		$last_level = $childModel->level;
	}
}
$model->level = $last_level + 1;
$childModels[$model->id] = $model;

if ($parentModels = $model->getParentModels($model->level)){
	$childModels = ArrayHelper::merge($childModels, $parentModels);
}

if (count($childModels) > 1){
	LightCard::begin([
		'icon' => 'share-alt',
		'title' => (count($childModels) > 1) ? Yii::t('app', 'Related Licenses') : Yii::t('app', 'Related License'),
		'addOn' => Html::tag('span', count($childModels), ['class' => 'badge badge-secondary ml-2']),
		'show' => !Yii::$app->request->isAjax,
	]);

	echo $this->render('/license/_grid', ['models' => $childModels, 'active' => $model->id]);

	LightCard::end();
}
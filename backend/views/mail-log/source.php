<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\MailLog */

$this->title = $model->name;

$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Mail Log'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'View Source');

?>
<?= Html::tag('pre', Html::encode($model->message)) ?>

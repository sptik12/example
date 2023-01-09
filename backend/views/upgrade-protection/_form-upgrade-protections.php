<?php

use common\helpers\Html;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $models [common\models\UpgradeProtection] */

echo implode(', ', ArrayHelper::getColumn($models, 'link'));

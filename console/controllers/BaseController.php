<?php

namespace console\controllers;

use Yii;
use yii\console\Controller;

use common\helpers\Format;

/*
 *
 */

class BaseController extends Controller
{
	public $time_start;

	/*
	 * inherit
	 */
	public function beforeAction($action)
	{
		$this->time_start = microtime(true);
		$this->debug(Yii::t('app', 'Start {id}/{action}', ['id' => $this->id, 'action' => $action->id]));
		return parent::beforeAction($action);
	}

	/*
	 * inherit
	 */
	public function afterAction($action, $result)
	{
		$this->debug(Yii::t('app', 'Stop {id}/{action}', ['id' => $this->id, 'action' => $action->id]));
		$time_spent = microtime(true) - $this->time_start;
		$this->debug(Yii::t('app', 'Execution time: {value}', ['value' => Format::minutes($time_spent)]));
		return parent::afterAction($action, $result);
	}

	/*
	 * Debug
	 */
	public function debug($message)
	{
		echo $message . PHP_EOL;
		Yii::info($message, $this->id);
	}

}

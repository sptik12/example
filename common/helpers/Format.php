<?php
namespace common\helpers;

use Yii;
use yii\helpers\Html;
use yii\helpers\Json;
use kartik\datecontrol\DateControl;

class Format
{
	/**
	 *
	 */
	public static function time($seconds)
	{
		return sprintf("%02d%s%02d%s%02d", floor($seconds / 3600), ':', ($seconds / 60) % 60, ':', $seconds % 60);
	}

	/**
	 *
	 */
	public static function minutes($seconds)
	{
		return sprintf("%01d%s%02d",  ($seconds / 60) , ':', $seconds % 60);
	}

	/**
	* @param mixed $date
	* @return string
	 */
	public static function serverDatetime($date, $format='Y-m-d H:i:s')
	{
		$timeZone = Yii::$app->formatter->timeZone;
		$defaultTimeZone = Yii::$app->formatter->defaultTimeZone;
		if ($timeZone != $defaultTimeZone){
			try {
				$dateTime = new \DateTime ($date, new \DateTimeZone($timeZone));
				$dateTime->setTimezone(new \DateTimeZone($defaultTimeZone));
				return $dateTime->format($format);
			} 
			catch (Exception $e) {
			}
		}
		return $date;
	}

	/**
	* Format Price
	* @param float $price
	* @return string
	*/
	public static function formatPrice($price)
	{
		return \Yii::$app->formatter->asCurrency($price, Yii::$app->params['default_currency']);
	}

	/**
	 * Get formatted a GMT/UTC time/date according to locale settings using site configuration settings
	 *
	 *  @param date $format format
	 *  @param time $time
	 * @return string
	 */
	public static function getHomeDateTime($format = null, $time = null) {
		$format = $format ? $format : '%Y-%b-%d';// 2015-Jan-31
		return self::getWithOffset($format, Yii::$app->params['homeTimeZoneOffset'], $time);
	}

	/**
	 * Get formatted a GMT/UTC time/date according to locale settings
	 *
	 *  @param date $format format
	 *  @param timezone $tzOffset offset
	 *  @param time $time
	 *
	 * @return string
	 */
	public static function getWithOffset($format, $tzOffset, $time = null) {
		$time = $time ? $time : time();
		return gmstrftime($format, $time + $tzOffset * 3600);
	}

}

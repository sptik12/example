<?php

/**
 * @package yii2-helpers
 * @version 1.3.9
 */

namespace common\helpers;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;


/**
 * Html provides a set of static methods for generating commonly used HTML tags and extends [[kartikHtml]]
 * with additional bootstrap styled components and markup.
 *
 * Nearly all of the methods in this class allow setting additional html attributes for the html
 * tags they generate. You can specify for example. 'class', 'style'  or 'id' for an html element
 * using the `$options` parameter. See the documentation of the [[tag()]] method for more details.
 *
 * @see http://getbootstrap.com/css
 * @see http://getbootstrap.com/components
 * @since 2.0
 */
class Html extends \kartik\helpers\Html
{
	/**
	 * @var string the icon name
	 */
	const ICON_VIEW = 'eye';
	const ICON_EVENT_LOG = 'edit';
	const ICON_LICENSE = 'key';
	const ICON_SUPPORT = 'phone';
	const ICON_RESTRICTED_DOMAINS = 'ban';
	const ICON_INVALIDATE = 'times';
	const ICON_PROTECTION = 'umbrella';
	const ICON_USER_EDIT = 'user-edit';
	const ICON_EDIT = 'pencil-alt';
	const ICON_DELIVERY = 'shipping-fast';
	const ICON_DOWNLOAD = 'download';
	const ICON_EMAIL = 'envelope';
	const ICON_LIST = 'list';
	const ICON_USER = 'user';
	const ICON_LOG = 'book';
	const ICON_PRODUCT = 'cubes';
	const ICON_SIGN_OUT = 'sign-out';
	const ICON_SETTINGS = 'cog';
	const ICON_GENERATE = 'plus';
	const ICON_DASHBOARD = 'chart-bar';
	const ICON_TRAITS = 'minus';
	const ICON_PENDING = 'clock';
	const ICON_PLUS = 'plus';
	const ICON_REDO = 'times-circle';
	const ICON_UPDATE = 'pencil-alt';
	const ICON_DELETE = 'trash-alt';
	const ICON_LOCK = 'lock';
	const ICON_BACK = 'arrow-left';
	const ICON_DOWN = 'long-arrow-alt-down';
	const ICON_UP = 'long-arrow-alt-up';
	const ICON_CREATE = 'arrow-circle-right';
	const ICON_PAYMENT = 'database';
	const ICON_STATUS_OK = 'thumbs-up';
	const ICON_STATUS_FALSE = 'thumbs-down';
	const ICON_RESET = 'ban';

	/**
	 * Generates a bootstrap icon markup.
	 *
	 * Example:
	 *
	 * ~~~
	 * echo Html::icon('pencil');
	 * echo Html::icon('trash', ['style' => 'color: red; font-size: 2em']);
	 * echo Html::icon('plus', ['class' => 'text-success']);
	 * ~~~
	 *
	 * @see https://fontawesome.com/
	 *
	 * @param string $icon the bootstrap icon name without prefix (e.g. 'plus', 'pencil', 'trash')
	 * @param array $options HTML attributes / options for the icon container
	 * @param string $prefix the css class prefix - defaults to 'fas fa-'
	 * @param string $tag the icon container tag (usually 'span' or 'i') - defaults to 'i'
	 *
	 * @return string
	 */
	public static function icon($icon, $options = [], $prefix = 'fas fa-', $tag = 'i')
	{
		return parent::icon($icon, $options, $prefix, $tag);
	}

	/**
	 * Update a message to replace {placeholders} with values.
	 *
	 * @param string $message the message to be updated.
	 * @param array|object $array values that will be used to replace the corresponding placeholders in the message.
	 * @return string the updated message.
	 */
	public static function substitute($message, $array = [])
	{
		$placeholders = [];
		if (preg_match_all('/\{([a-zA-Z0-9._-]+)\}/', $message, $matches, PREG_SET_ORDER)) {
			foreach ($matches as $match) {
				$placeholders['{' . $match[1] . '}'] = ArrayHelper::getValue($array, $match[1]);
			}
		}
		return ($placeholders === []) ? $message : strtr($message, $placeholders);
	}

	/**
	 * Get Yes/No badge
	 * @return string
	 */
	public static function formatBoolean($value, $labelTrue = 'Yes', $labelFalse = 'No', $labelTrueCss = 'badge badge-success', $labelFalseCss = 'badge badge-danger')
	{
		return $value ? self::tag('span', Yii::t('app', $labelTrue), ['class' => 'label ' . $labelTrueCss]) : self::tag('span', Yii::t('app', $labelFalse), ['class' => 'label ' . $labelFalseCss]);
	}

	/**
	 *
	 * @return string
	 */
	public static function formatFileSize($bytes, $digits = 2)
	{
		if ($bytes) {
			$bytes = (int)$bytes;

			if ($bytes >= 1073741824) {
				return number_format($bytes / 1073741824, $digits) . ' GB';
			}
			if ($bytes >= 1048576) {
				return number_format($bytes / 1048576, $digits) . ' MB';
			}

			return number_format($bytes / 1024, $digits) . ' KB';
		}
		return '0 KB';
	}

	/**
	 *
	 * @return string
	 */
	public static function noData($message = null)
	{
		if (!$message) {
			$message = Yii::t('app', 'No data found');
		}

		return Html::tag('div', $message, ['class' => "alert alert-danger"]);
	}

	/**
	 *
	 */
	public static function downloadFile($file, $file_name, $delete = false)
	{
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . $file_name . '"');
			header('Content-Transfer-Encoding: binary');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			ob_clean();
			flush();
			readfile($file);
			if ($delete) {
				if (is_bool($delete)) {
					unlink($file);
				} else {
					FileHelper::removeDirectory($delete);
				}
			}
			exit;
		}
	}
}

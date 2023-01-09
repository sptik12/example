<?php

namespace console\controllers;

use common\models\Delivery;
use Yii;
use yii\helpers\ArrayHelper;

use common\models\License;
use common\models\UpgradeProtection;
use common\models\MailLog;
use common\models\RestrictedDomain;
use common\helpers\Config;
use common\helpers\Mailer;

/*
 * Send notification emails to customers
 * Should be started by cron
 *
 * To start manually
 * php yii notify
 */

class NotifyController extends BaseController
{
	public $restrictedDomains;

	/*
	 * main action
	 */
	public function actionIndex()
	{
		$this->restrictedDomains  = RestrictedDomain::find()->select('name')->column();
		$product_ids = Config::getProductIds();
		foreach ($product_ids as $product_id) {
			$letters = Config::getLetters($product_id, Config::MAIL_TEMPLATE_NOTIFICATION);
			foreach ($letters as $letter) {
				$this->sendTemplateEmails($letter, $product_id);
			}
		}
	}

	/*
	 * Send Notify Email as template

	 * @param $letter
	 * @param $product_id
	 * @param null $restrictedDomains
	 * @throws \Exception
	 */
	public function sendTemplateEmails($letter, $product_id)
	{
		if (ArrayHelper::getValue($letter, 'enabled', false)) {
			$days = ($letter['type'] == 'beforeExpireDate') ? -$letter['days'] : $letter['days'];
			$dbDateExpression = new \yii\db\Expression("CURDATE() = DATE(DATE_ADD(upgrade_protection_end_date, INTERVAL $days DAY))");

			$licenses = License::find()->with(['email'])
					->andWhere(['not', ['upgrade_protection_end_date' => null]])
					->andWhere($dbDateExpression)->andWhere(['not_after_date' => null])->valid()->filterProductInfo($product_id)->all();

			$emails = [];
			foreach ($licenses as $license) {
				$allowedLicenseTypes = ArrayHelper::getValue($letter, 'licenseTypes');
				if (in_array($license->licenseTypeName, $allowedLicenseTypes)) {
					if (!$this->isEmailRestricted($license->emailAddress)) {
						$licenseEmail = $license->emailAddress;
						$deliveriesEmails = [];
						if ($deliveries = $license->deliveries) {
							$lastDelivery = end($deliveries); //latest delivery

							// filter deliveries to remain recent deliveries sent within 24h
							$recentDeliveries = array_filter($deliveries,
									function ($currentDelivery) use ($lastDelivery) {
										$date2 = new \DateTime($lastDelivery->created_at);
										$date1 = new \DateTime($currentDelivery->created_at);
										return $date2->diff($date1)->days <= 1;
									}, 0);

							foreach ($recentDeliveries as $recentDelivery) {
								// if delivery email not the same as license email and not restricted use this email
								if ($recentDelivery->emailAddress != $licenseEmail) {
									if (!$this->isEmailRestricted($recentDelivery->emailAddress)) {
										$deliveriesEmails[] = $recentDelivery->emailAddress;
									}
								}
							}
						}

						if ($deliveriesEmails) {
							$deliveriesEmails = array_unique($deliveriesEmails);
							foreach ($deliveriesEmails as $deliveryEmail) {
								$emails[$deliveryEmail][] = $license;
							}
						}
						if (!in_array($licenseEmail, $deliveriesEmails)) {
							$emails[$licenseEmail][] = $license;
						}
					}

				}
			}

			$options = [
				'subject' => $letter['subject'],
				'from' => $letter['from'],
				];
			if ($replyTo = ArrayHelper::getValue($letter, 'replyTo', false)) {
				$options['replyTo'] = $replyTo;
			}
			if ($text = ArrayHelper::getValue($letter, 'text', false)) {
				$options['text'] = $text;
			}
			$emailSettings = Config::getSettings($product_id);
			$sales = ArrayHelper::getValue($emailSettings, 'emails.sales');
			$purchaseUrl = ArrayHelper::getValue($emailSettings, 'purchaseUrl');

			$upgradeProtectionsForUpdate = [];
			
			foreach ($emails as $email => $licenses) {
				$licensesForEmail = [];
				foreach ($licenses as $license) {
					$upgradeProtection = UpgradeProtection::find()->where(['license_id' => $license->id,
						'DATE(end_date)' => $license->upgrade_protection_end_date])->one();
					if ($upgradeProtection) {
						if ($upgradeProtection->notify_flags & $letter['notify_flag_value']) {
							// email for this license was already sent earlier
							continue;
						}
						$upgradeProtectionsForUpdate[] = $upgradeProtection;
						$licensesForEmail[] = $license;
					}
				}

				if ($licensesForEmail) {
					$message = Mailer::send(view: $letter['view'],
								to: $email,
								params: [
									'days' => $letter['days'], 'sales' => $sales,
									'purchaseUrl' => $purchaseUrl, 'licenses' => $licensesForEmail
								],
								options: $options,
								log_name: $this->id
							);

					if ($message) {
						$logText = sprintf("Notification email have been sent to %s<%s> using %s", $license->owner_name, $email, $letter['view']);
						$this->debug($logText);

						$model = new MailLog();
						$model->email = $email;
						$model->mail_template_id = Config::MAIL_TEMPLATE_NOTIFICATION;
						$model->letter_id = $letter['id'];
						$model->product_id = $product_id;
						if (count($licensesForEmail) == 1) {
							$model->license_id = $licensesForEmail[0]->id;
						}
						$model->message = $message;
						$model->status_id = MailLog::STATUS_OK;
						$model->save();
					}

					$email = Yii::$app->params['tmpNotifyEmailCopy'];
					$message = Mailer::send(view: $letter['view'],
								to: $email,
								params: [
									'days' => $letter['days'], 'sales' => $sales,
									'purchaseUrl' => $purchaseUrl, 'licenses' => $licensesForEmail
								],
								options: $options,
								log_name: $this->id
					);

				}
			}

			foreach ($upgradeProtectionsForUpdate as $upgradeProtection) {
				$notify_flags = $upgradeProtection->notify_flags | $letter['notify_flag_value'];
				$upgradeProtection->updateAttributes(['notify_flags' => $notify_flags]);
			}
		}
	}

	/*
	 * Check that email assigned to restricted domains
	 * @param string $email
	 */
	public function isEmailRestricted($email)
	{
		if ($this->restrictedDomains) {
			$domain = explode('@', $email, 2);
			return in_array($domain[1], $this->restrictedDomains);
		}
		return false;
	}
}

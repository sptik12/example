<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\data\ArrayDataProvider;
use yii\helpers\Url;

use common\models\License;
use common\models\Payment;

use common\helpers\ArrayHelper;
use common\helpers\Config;
use common\helpers\Mailer;
use common\helpers\Html;

/**
 * MailTemplateController
 */
class MailTemplateController extends BaseController
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => [ 'notifications', 'pending', 'letter', 'send'],
						'allow' => true,
						'roles' => ['mail-log'],
					],
				],
			],
		];
	}

	/**
	 * List of notifications mail templates
	 * @return mixed
	 */
	public function actionNotifications()
	{
		$templates = [];

		$product_id_filter = Config::getProfileProductId();

		$product_ids = Config::getProductIds();
		foreach ($product_ids as $product_id) {
			if ($product_id_filter && $product_id != $product_id_filter) continue;

			$letters = Config::getLetters($product_id, Config::MAIL_TEMPLATE_NOTIFICATION);
			foreach ($letters as $letter){
				$letter['product_id'] = $product_id;
				$letter['mail_template_id'] = Config::MAIL_TEMPLATE_NOTIFICATION;
				$templates[] = $letter;
			}
		}

		$dataProvider = new ArrayDataProvider(['allModels' => $templates]);
		$params = [
				'dataProvider' => $dataProvider,
		];

		Url::remember();

		return Yii::$app->request->isAjax ? $this->renderPartial('notifications', $params) : $this->render('notifications', $params);
	}

	/**
	 * List of pending mail templates
	 * @return mixed
	 */
	public function actionPending()
	{
		$templates = [];

		$product_id_filter = Config::getProfileProductId();

		$product_ids = Config::getProductIds();
		foreach ($product_ids as $product_id) {
			if ($product_id_filter && $product_id != $product_id_filter) continue;

			$letters = Config::getLetters($product_id, Config::MAIL_TEMPLATE_PENDING);
			foreach ($letters as $letter){
				$letter['product_id'] = $product_id;
				$letter['mail_template_id'] = Config::MAIL_TEMPLATE_PENDING;
				$templates[] = $letter;
			}
		}

		$dataProvider = new ArrayDataProvider(['allModels' => $templates]);
		$params = [
			'dataProvider' => $dataProvider,
		];

		Url::remember();

		return Yii::$app->request->isAjax ? $this->renderPartial('pending', $params) : $this->render('pending', $params);
	}

	/**
	 * View letter
	 * @param string $product_id
	 * @param string $mail_template_id
	 * @param string $letter_id
	 * @return mixed
	 * @throws NotFoundHttpException if the letter cannot be found
	 */
	public function actionLetter($product_id, $mail_template_id, $letter_id)
	{
		$letter = Config::getLetter($product_id, $mail_template_id, $letter_id);
		$params = $this->getTestData($product_id, $mail_template_id, $letter_id);
		if (Yii::$app->request->isAjax) {
			$this->layout = 'modal';
		}

		return $this->render('letter', [
			'letter' => $letter,
			'params' => $params
		]);
	}

	/**
	 * Send test letter
	 * @param string $product_id
	 * @param string $mail_template_id
	 * @param string $letter_id
	 * @return mixed
	 * @throws NotFoundHttpException if the letter cannot be found
	 */
	public function actionSend($product_id, $mail_template_id, $letter_id)
	{
		$letter = Config::getLetter($product_id, $mail_template_id, $letter_id);
		$params = $this->getTestData($product_id, $mail_template_id, $letter_id);

		if ($params) {
			$options = [
				'subject' => $params['subject'],
				'from' => $letter['from'],
			];
			if ($replyTo = ArrayHelper::getValue($letter, 'replyTo', false)) {
				$options['replyTo'] = $replyTo;
			}
			if ($text = ArrayHelper::getValue($letter, 'text', false)) {
				$options['text'] = $text;
			}

			$message = Mailer::send(view: $letter['view'],
									to: Yii::$app->params['debugEmail'],
									params: $params,
									options: $options
									);
			$this->successAlert = Yii::t('app', 'Email was successfully sent');
		}
		else {
			$this->errorAlert = Yii::t('app', 'Template parameters list is empty, template cannot be send');
		}

		$this->redirect(Url::previous());
	}

	/**
	 * get test data
	 * @param string $product_id
	 * @param string $mail_template_id
	 * @param string $letter_id
	 * @return mixed
	 * @throws NotFoundHttpException if the letter cannot be found
	 */
	protected function getTestData($product_id, $mail_template_id, $letter_id)
	{
		$letter = Config::getLetter($product_id, $mail_template_id, $letter_id);
		//echo '<pre>';print_r($letter);echo '</pre>';die;
		$params = [];

		switch ($mail_template_id) {
			case Config::MAIL_TEMPLATE_NOTIFICATION:
				$licenses = License::find()->where(['id' => Yii::$app->params['notifyDemoLicenseIds'][$product_id]])->all();
				$l = null;
				if ($product_id == Config::PRODUCT_LDAP_ADMINISTRATOR &&
					($letter_id == 'after_1_day' || $letter_id == 'before_7_days') ) {
					$l = $licenses;
				}
				else {
					$l = [$licenses[0]];
				}

				if ($l) {
					$emailSettings = Config::getSettings($product_id);
					$sales = ArrayHelper::getValue($emailSettings, 'emails.sales');
					$purchaseUrl = ArrayHelper::getValue($emailSettings, 'purchaseUrl');
					$params = ['days' => $letter['days'], 'sales' => $sales, 'purchaseUrl' => $purchaseUrl, 'licenses' => $l, 'subject' => $letter['subject']];
				}
				break;

			case Config::MAIL_TEMPLATE_PENDING:
				$params = [];
				$paymentId = ($product_id == Config::PRODUCT_LDAP_ADMINISTRATOR) ? Yii::$app->params['ldapDemoPaymentId'] : Yii::$app->params['adaxesDemoPaymentId'];
				$payment = Payment::findOne($paymentId);
				if ($payment) {
					$emailSettings = Config::getSettings($payment->product_id);
					$sales = ArrayHelper::getValue($emailSettings, 'emails.sales');
					$params = [
						'payment' => $payment,
						'sales' => $sales,
						'subject' => Html::substitute(ArrayHelper::getValue($letter, 'subject'), ['paymentTypeName' => $payment->paymentTypeName])
					];
				}
				break;
		}

		return $params;
	}
}

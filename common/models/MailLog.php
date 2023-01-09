<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Html;
use common\helpers\Config;

/**
 * This is the model class for table "mail_log".
 *
 * @property int $id
 * @property string $email
 * @property string $product_id
 * @property string $mail_template_id
 * @property string $letter_id
 * @property string $license_id
 * @property string $support_id
 * @property string $delivery_id
 * @property string $message
 * @property int $status_id
 * @property string $created_at
 * @property string $updated_at
 */
class MailLog extends BaseActiveRecord
{
	const STATUS_ERROR = 0;
	const STATUS_OK = 1;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'mail_log';
	}

	/**
	 * {@inheritdoc}
	 * @return MailLogQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new MailLogQuery(get_called_class());
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['email'], 'required'],
			[['license_id', 'delivery_id', 'support_id', 'status_id'], 'safe'],
			[['email', 'message'], 'string'],
			[['created_at', 'updated_at', 'letter_id', 'mail_template_id', 'product_id'], 'safe'],
		];
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/mail-log/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get Link
	 * @return string
	 */
	public function getLink($options = ['class' => 'text-nowrap'])
	{
		return Yii::$app->user->can('mail-log') ? Html::a($this->name, $this->route, $options) : Html::tag('span', Html::encode($this->name), $options);
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName()
	{
		return Yii::$app->formatter->asDatetime($this->created_at);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'ID'),
			'email' =>  Yii::t('app', 'Email'),
			'mail_template_id' => Yii::t('app', 'Mail Action'),
			'letter_id' => Yii::t('app', 'Letter'),
			'product_id' => Yii::t('app', 'Product'),
			'license_id' => Yii::t('app', 'License ID'),
			'support_id' => Yii::t('app', 'Support ID'),
			'delivery_id' => Yii::t('app', 'Delivery'),
			'message' => Yii::t('app', 'Message'),
			'status_id' => Yii::t('app', 'Status'),
			'created_at' => Yii::t('app', 'Sent'),
			'updated_at' => Yii::t('app', 'Updated At'),
		];
	}

	/**
	 * Get License
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicense()
	{
		return $this->hasOne(License::className(), ['id' => 'license_id']);
	}

	/**
	 * Get Support
	 * @return \yii\db\ActiveQuery
	 */
	public function getSupport()
	{
		return $this->hasOne(Support::className(), ['id' => 'support_id']);
	}

	/**
	 * Get Delivery
	 * @return \yii\db\ActiveQuery
	 */
	public function getDelivery()
	{
		return $this->hasOne(Delivery::className(), ['id' => 'delivery_id']);
	}

	/**
	 * @return bool
	 */
	public function isNotification()
	{
		return ($this->mail_template_id == Config::MAIL_TEMPLATE_NOTIFICATION);
	}

	/**
	 * Returns Mail Items 'id' => 'title'
	 * @return array
	 */
	public static function getMailTemplateItems()
	{
		return [
			Config::MAIL_TEMPLATE_NOTIFICATION  => Yii::t('app', 'Notification'),
			Config::MAIL_TEMPLATE_LICENSE  => Yii::t('app', 'License'),
			Config::MAIL_TEMPLATE_UPGRADE_PROTECTION  => Yii::t('app', 'Annual Maintenance and Support'),
			Config::MAIL_TEMPLATE_SUPPORT  => Yii::t('app', 'Support'),
			Config::MAIL_TEMPLATE_PENDING  => Yii::t('app', 'Pending')
		];
	}

	/**
	 * Get mail name
	 * @return string
	 */
	public function getMailTemplateName()
	{
		return ArrayHelper::getValue(static::getMailTemplateItems(), $this->mail_template_id, $this->mail_template_id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLetterName1()
	{
		return ArrayHelper::getValue(self::$letters, $this->letter_id);
	}

	/**
	 * Get letter name
	 * @return string
	 */
	public function getLetterName()
	{
		$letters = $this->getLetterItems();
		return ArrayHelper::getValue($letters, $this->letter_id, $this->letter_id);
	}

	/**
	 *
	 * @return array
	 */
	public function getLetterItems()
	{
		return 	Config::getLetterItems($this->product_id, $this->mail_template_id);
	}

	/**
	 * Returns Letters indexed by id
	 *
	 * @param string $product_id
	 * @param string $mail_id
	 *
	 * @return array
	 */
	public function getNotificationItems()
	{
		$product_ids = $this->product_id ? [$this->product_id] : array_keys(Config::getProducts());
		$res=[];
		foreach($product_ids as $product_id) {
			$notifications = Config::getMailNotifications($product_id);
			$letters = ArrayHelper::getValue($notifications, 'letters', []);
			$res = ArrayHelper::merge($res, ArrayHelper::map($letters, 'id', 'name'));
		}
		ksort($res);
		return $res;
	}

}

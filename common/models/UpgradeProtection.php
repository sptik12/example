<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use common\helpers\Html;

/**
 * This is the model class for table "upgrade_protection".
 *
 * @property string $id
 * @property string $license_id
 * @property int $email_id
 * @property int $product_info_id
 * @property string $owner_name
 * @property string|null $company
 * @property string|null $buy_date
 * @property string|null $end_date
 * @property string|null $created_at
 * @property int|null $owner_id
 * @property string $payment_id
 * @property int $notify_flags
 */
class UpgradeProtection extends BaseActiveRecord
{
	/**
	 * @var string
	 */
	const CUSTOM_PERIOD = '';
	
	/**
	 * @var array
	 */
	public $license_ids = [];

	/**
	 * @var string
	 */
	public $email_address;

	/**
	 * @var string
	 */
	public $product_id;

	/**
	 * @var integer
	 */
	public $version;

	/**
	 * @var integer
	 */
	public $delivery_count=1;

	/**
	 * @var string
	 */
	public $period;

	/**
	 * @var integer
	 */
	public $ignore_ams_date_validation=0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'upgrade_protection';
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			//[['id', 'product_info_id', 'owner_name'], 'required'],
			[['email_id', 'product_info_id', 'owner_id', 'ignore_ams_date_validation'], 'integer'],
			[['buy_date', 'end_date', 'created_at', 'period'], 'safe'],
			[['id'], 'string', 'max' => 36],
			[['license_id'], 'string', 'max' => 64],
			[['owner_name', 'company'], 'string', 'max' => 128],
			[['id'], 'unique'],
			[['email_address'], 'string', 'max' => 64],
			['email_address', 'email'],
			[['delivery_count'], 'integer', 'min' => 0],
			[['payment_id'], 'safe'],
			[['notify_flags'], 'safe'],

			// scenario rules
			[['owner_name', 'email_address', 'company'], 'filter', 'filter' => 'trim', 'on' => ['create', 'bulk-create']],
			[['owner_name', 'email_address', 'buy_date', 'end_date', 'product_info_id'], 'required', 'on' => ['create']],
			
			[['license_id'], 'required', 'on' => ['create']],
			//[['license_ids'], 'required', 'on' => ['bulk-create']],
			[['delivery_count'], 'required', 'on' => ['create', 'delivery', 'bulk-delivery']],
			[['delivery_count'], 'integer', 'min' => 1, 'on' => ['delivery', 'bulk-delivery'], 'tooSmall' => Yii::t('app', 'At least one Delivery required')],
			['end_date', 'compare', 'compareAttribute' => 'buy_date', 'operator' => '>=', 'on' => ['create'], 'enableClientValidation' => false, 'message' => Yii::t('app', 'End Date must be greater than AMS Date.'), 'when' => function ($model) {
				return $model->period ? true : ($model->ignore_ams_date_validation ? false : true);
			}],
			[['end_date'], 'validateEndDate', 'on' => ['create', 'bulk-create']],
		];
	}
	
	/**
	 * {@inheritdoc}
	 */
	public function validateEndDate($attribute, $params)
    {
		if(!$this->hasErrors()){
			$end_date = false;
			if ($this->scenario == 'create'){
				$end_date = ArrayHelper::getValue($this, 'license.not_after_date');
			}
			if ($this->scenario == 'bulk-create'){
				$end_date = License::find()->andWhere(['id' => $this->license_ids])->min('not_after_date');
			}
			if ($end_date){
				if ($end_date < $this->end_date){
					$this->addError('end_date', Yii::t('app', 'AMS End Date: {ams_end_date} cannot be later than License End Date: {end_date}.', ['ams_end_date' => Yii::$app->formatter->asDate($this->end_date), 'end_date' => Yii::$app->formatter->asDate($end_date)]));
				}
			}
		}
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/upgrade-protection/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName()
	{
		return $this->getCompositeAttribute(Yii::$app->params['upgradeProtection.name.layout']);
	}

	/**
	 * Get Link
	 * @return string
	 */
	public function getLink($options = ['class' => 'text-nowrap'])
	{
		return Yii::$app->user->can('upgrade-protection') ? Yii::$app->controller->renderPartial('/upgrade-protection/_link', ['model' => $this, 'options' => $options]) : Html::tag('span', Html::encode($this->name), $options);
	}

	/**
	 * Get email address
	 * @return sting
	 */
	public function getEmailAddress()
	{
		return ArrayHelper::getValue($this, 'email.address');
	}
	
	/**
	 * Get Buy Date
	 * @return string
	 */
	public function getBuyDate()
	{
		return Yii::$app->formatter->asDate($this->buy_date);
	}
	
	/**
	 * Get End Date
	 * @param string $format defaults to null
	 * @return string
	 */
	public function getEndDate($format=null)
	{
		return Yii::$app->formatter->asDate($this->end_date, $format);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'ID'),
			'license_id' => Yii::t('app', 'License'),
			'license_ids' => Yii::t('app', 'Licenses'),
			'email_id' => Yii::t('app', 'Email'),
			'email_address' => Yii::t('app', 'Email'),
			'product_info_id' => Yii::t('app', 'Version'),
			'owner_name' => Yii::t('app', 'Contact'),
			'company' => Yii::t('app', 'Company'),
			'buy_date' => Yii::t('app', 'Start Date'),
			'end_date' => Yii::t('app', 'End Date'),
			'ignore_ams_date_validation' => Yii::t('app', 'Ignore AMS date validity check'),
			'created_at' => Yii::t('app', 'Issue Date'),
			'owner_id' => Yii::t('app', 'Initiator'),
			'payment_id' => Yii::t('app', 'Payment'),
			'notify_flags' => Yii::t('app', 'Notification Flags')
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
	 * Get Payment
	 * @return \yii\db\ActiveQuery
	 */
	public function getPayment()
	{
		return $this->hasOne(Payment::className(), ['id' => 'payment_id']);
	}

	/**
	 * Get Email
	 * @return \yii\db\ActiveQuery
	 */
	public function getEmail()
	{
		return $this->hasOne(Email::className(), ['id' => 'email_id']);
	}

	/**
	 * Get ProductInfo
	 * @return \yii\db\ActiveQuery
	 */
	public function getProductInfo()
	{
		return $this->hasOne(ProductInfo::className(), ['id' => 'product_info_id']);
	}

	/**
	 * Get User
	 * @return \yii\db\ActiveQuery
	 */
	public function getUser()
	{
		return $this->hasOne(User::className(), ['id' => 'owner_id']);
	}

	/**
	 * Get Deliveries
	 * @return \yii\db\ActiveQuery
	 */
	public function getDeliveries()
	{
		Delivery::$item_email_id = $this->email_id;

		return $this->hasMany(Delivery::className(), ['id' => 'delivery_id'])->viaTable('deliveries_upgrade_protections', ['upgrade_protection_id' => 'id']);
	}

	/**
	 * whether Expired
	 * @return bool
	 */
	public function isExpired()
	{
		return $this->end_date < $this->curdateDb;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		$this->logDelete($this->owner_name, $this->license_id);
		return parent::beforeDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		$this->logSave($this->owner_name, $insert, $changedAttributes, $this->license_id);

		if ($license = $this->license) {

			$start_date = date_create(date('Y-m-d', strtotime($license->created_at)));
			$end_date = date_create(date('Y-m-d', strtotime($this->end_date)));

			$interval = date_diff($start_date, $end_date);

			$days = $interval->format('%a');
			
			$save = false;
			
			//update license.upgrade_protection_days
			if (($days > 0) && (empty($license->upgrade_protection_days) || $days > $license->upgrade_protection_days)) {
				$license->upgrade_protection_days = $days;
				$save = true;
			}
			//update license.upgrade_protection_end_date
			if (empty($license->upgrade_protection_end_date) || $this->end_date != $license->upgrade_protection_end_date) {
				$license->upgrade_protection_end_date = $this->end_date;
				if ($productInfo = ProductInfo::find()->andWhere(['product_id' => $license->productId])->andWhere(['<=', 'release_date', $license->upgrade_protection_end_date])->orderBy(['release_date' => SORT_DESC])->one()){
					if($license->product_info_id != $license->id){
						$license->product_info_id = $productInfo->id;
					}
				}
				$save = true;
			}
			//update license.company
			if ($license->company != $this->company){
				$license->company = $this->company;
				$save = true;
			}
			//update license.owner_name
			if ($license->owner_name != $this->owner_name){
				$license->owner_name = $this->owner_name;
				$save = true;
			}
			//update license.email_id
			if ($license->email_id != $this->email_id){
				$license->email_id = $this->email_id;
				$save = true;
			}
			
			if ($save){
				$license->save(false);
			}
		}
		parent::afterSave($insert, $changedAttributes);
	}

	/**
	 *
	 * @return array
	 */
	public function getPeriodItems($count = 5)
	{
		$start_date = ($this->buy_date == $this->curdateDb) ? $this->buy_date : date('Y-m-d', strtotime($this->buy_date . " - 1 day"));

		$items = [
			date('Y-m-d', strtotime($start_date . " +1 year")) => Yii::t('app', '1 year'),
		];
		for ($i = 2; $i <= $count; $i++) {
			$items[date('Y-m-d', strtotime($start_date . " +" . $i . " years"))] = Yii::t('app', '{num} years', ['num' => $i]);
		}
		$items[static::CUSTOM_PERIOD] = Yii::t('app', 'Custom');

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getBulkPeriodItems($count = 5)
	{
		$items = [
			1 => Yii::t('app', '1 year'),
		];
		for ($i = 2; $i <= $count; $i++) {
			$items[$i] = Yii::t('app', '{num} years', ['num' => $i]);
		}
		$items[''] = Yii::t('app', 'Custom');

		return $items;
	}
	
	/**
	 * {@inheritdoc}
	 * @return UpgradeProtectionQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new UpgradeProtectionQuery(get_called_class());
	}
}

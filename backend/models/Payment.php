<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\Html;

use common\helpers\Config;

/**
 * This is the model class for table "payment".
 *
 * @property string  $id
 * @property string  $product_id
 * @property string  $order_id
 * @property string  $cart_id
 * @property decimal $amount
 * @property string  $owner_name
 * @property string  $email
 * @property string  $company
 * @property string|null $delivery_email
 * @property integer $type
 * @property integer $completed
 * @property string|null  $comment
 * @property integer|null $user_number
 * @property string|null  $allowed_dns_domains
 * @property integer|null $enable_support
 * @property integer|null $enable_upgrade_protection
 * @property integer|null $quantity
 * @property string|null  $license_type_name
 * @property integer|null $reseller_id
 * @property datetime $created_at
 * @property datetime $updated_at
 * @property integer|null $version
 * @property integer|null $subversion
 */
class Payment extends BaseActiveRecord
{
	/*
	 *
	 */
	const PAYMENT_COMPLETED  = 1;
	const PAYMENT_INCOMPLETED  = 0;

	/*
	 *
	 */
	const PAYMENT_TYPE_STANDARD = 1;  /* Standard payment */
	const PAYMENT_TYPE_UPGRADE = 3;   /* Upgrade license payment */
	const PAYMENT_TYPE_CUSTOM = 4; /* Custom payments */
	const PAYMENT_TYPE_MAINTENANCE = 7;   /* AUP license payment */
	
	/**
	 * @var string
	 */
	public $card_holder_name;

	/**
	 * @inheritdoc
	 */
	public static function tableName()
	{
		return 'payment';
	}

	/**
	 * {@inheritdoc}
	 * @return PaymentQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new PaymentQuery(get_called_class());
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		return [
			[['id'], 'unique'],
			[['product_id', 'cart_id', 'order_id', 'owner_name', 'email', 'company', 'completed'], 'required'],
			[['amount'], 'number'],
			[['user_number', 'version', 'subversion'], 'integer'],
			[['email','delivery_email', 'financial_email'], 'email'],
			[['owner_name', 'email', 'company', 'comment'], 'filter', 'filter' => 'trim'],
			[['type', 'comment', 'allowed_dns_domains', 'version', 'subversion', 'created_at', 'updated_at'], 'safe'],
			[['enable_support', 'enable_upgrade_protection', 'quantity', 'license_type_name', 'reseller_id', 'card_holder_name'], 'safe']
		];
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' =>  Yii::t('app', 'ID'),
			'product_id' =>  Yii::t('app', 'Product'),
			'order_id' =>  Yii::t('app', 'Order'),
			'cart_id' =>  Yii::t('app', 'Cart Id'),
			'amount' =>  Yii::t('app', 'Amount ($)'),
			'user_number' =>  Yii::t('app', 'User Number'),
			'owner_name' =>  Yii::t('app', 'Customer'),
			'email' =>  Yii::t('app', 'Email'),
			'delivery_email' => Yii::t('app', 'Delivery Email'),
			'financial_email' => Yii::t('app', 'Financial Email'),
			'company' =>  Yii::t('app', 'Company'),
			'completed' =>  Yii::t('app', 'Is Paid'),
			'type' =>  Yii::t('app', 'Type'),
			'comment' =>  Yii::t('app', 'Comment'),
			'allowed_dns_domains' =>  Yii::t('app', 'Allowed DNS Domains'),
			'created_at' =>  Yii::t('app', 'Issued'),
			'updated_at' =>  Yii::t('app', 'Updated at'),
			'enable_upgrade_protection' => Yii::t('app', 'Enable UP'),
			'enable_support' => Yii::t('app', 'Include Support'),
			'quantity' =>  Yii::t('app', 'Quantity'),
			'license_type_name' =>  Yii::t('app', 'License'),
			'reseller_id' => Yii::t('app', 'Reseller'),
			'version' =>  Yii::t('app', 'Version'),
			'subversion' =>  Yii::t('app', 'Subversion'),
		);
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/payment/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get Payment Products
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getPaymentProducts()
	{
		return $this->hasMany(PaymentProduct::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Payment Licenses
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getPaymentLicenses()
	{
		return $this->hasMany(PaymentLicense::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Licenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenses()
	{
		return $this->hasMany(License::className(), ['id' => 'license_id'])->viaTable('payment_license', ['payment_id' => 'id']);
	}

	/**
	 *
	 */
	public function beforeDelete()
	{
		$this->deletePaymentProducts();
		$this->deletePaymentLicenses();

		return parent::beforeDelete();
	}

	/**
	 * Delete Payment Products
	 */
	public function deletePaymentProducts()
	{
		foreach ($this->paymentProducts as $model) {
			$model->delete();
		}
	}

	/**
	 * Delete Payment Licenses
	 */
	public function deletePaymentLicenses()
	{
		foreach ($this->paymentLicenses as $model) {
			$model->delete();
		}
	}

	/*
	 *
	 */
	public function getPaymentTypeName() {
		$names = self::getPaymentTypes();
		$res = ArrayHelper::getValue($names, $this->type);
		//$res =  array_key_exists($this->type, $names) ? $names[$this->type] : '';
		if (!empty($this->reseller_id) && !empty($res) &&  $this->type != self::PAYMENT_TYPE_CUSTOM) {
			$res = 'Resellers';
		}
		return $res;
	}

	/**
	 * Get possible Payment Types
	 *
	 * @return mixed
	 */
	public static function getPaymentTypes()
	{
		return [
			self::PAYMENT_TYPE_STANDARD => 'Standard',
			self::PAYMENT_TYPE_UPGRADE => 'Upgrade',
			self::PAYMENT_TYPE_MAINTENANCE => 'Maintenance',
			self::PAYMENT_TYPE_CUSTOM => 'Custom',
		];
	}

	/**
	 * Get Product
	 * @return \yii\db\ActiveQuery
	 */
	public function getProduct()
	{
		return $this->hasOne(Product::className(), ['id' => 'product_id']);
	}

	/**
	 * Get Product Name
	 * @return string
	 */
	public function getProductName()
	{
		return ArrayHelper::getValue($this, 'product.name');
	}

	/**
	 * Get Product Info
	 * @return string
	 */
	public function getProductInfo()
	{
		return $this->hasOne(ProductInfo::className(), ['product_id' => 'product_id', 'version' => 'version', 'subversion' => 'subversion']);
	}

	/**
	 * Get pending update licenses count
	 * @return string
	 */
	public function getPendingLicenseUpdatesCount($options = [], $route = ['/pending-license-update', 'clear' => 1], $param = 'PendingLicenseUpdateSearch[payment_id]')
	{
		$count = count($this->pendingLicenseUpdates);
		$filter = count($this->filterPendingLicenseUpdates);
		$count = ($count == $filter) ? $count : Html::substitute('{filter}/{count}', ['filter' => $filter, 'count' => $count]);
		if ($route && $count) {
			$options['title'] = Yii::t('app', 'Search for License Updates by {value}', ['value' => $this->id]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Pending Update Licenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getPendingLicenseUpdates()
	{
		return $this->hasMany(PendingLicenseUpdate::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Pending Update Licenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getFilterPendingLicenseUpdates()
	{
		$product_id = Config::getProfileProductId();
		//$version = Config::getProfileVersion();
		//$subversion = Config::getProfileSubversion();

		return $this->getPendingLicenseUpdates()->filterProductInfo($product_id);
	}

	/**
	 * Get pending Annual Maintenances and Supports count
	 * @return string
	 */
	public function getPendingLicenseUpgradeProtectionCount($options = [], $route = ['/pending-license-upgrade-protection', 'clear' => 1], $param = 'PendingLicenseUpgradeProtectionSearch[payment_id]')
	{
		$count = count($this->pendingLicenseUpgradeProtections);
		$filter = count($this->filterPendingLicenseUpgradeProtections);
		$count = ($count == $filter) ? $count : Html::substitute('{filter}/{count}', ['filter' => $filter, 'count' => $count]);
		if ($route && $count) {
			$options['title'] = Yii::t('app', 'Search for License Annual Maintenances and Supports by {value}', ['value' => $this->id]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Pending Annual Maintenances and Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getPendingLicenseUpgradeProtections()
	{
		return $this->hasMany(PendingLicenseUpgradeProtection::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Pending Annual Maintenances and Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getFilterPendingLicenseUpgradeProtections()
	{
		$product_id = Config::getProfileProductId();
		return $this->getPendingLicenseUpgradeProtections()->filterProductInfo($product_id);
	}

	/**
	 * Get pending supports count
	 * @return string
	 */
	public function getPendingLicenseSupportCount($options = [], $route = ['/pending-license-support', 'clear' => 1], $param = 'PendingLicenseSupportSearch[payment_id]')
	{
		$count = count($this->pendingLicenseSupports);
		$filter = count($this->filterPendingLicenseSupports);
		$count = ($count == $filter) ? $count : Html::substitute('{filter}/{count}', ['filter' => $filter, 'count' => $count]);
		if ($route && $count) {
			$options['title'] = Yii::t('app', 'Search for License Supports by {value}', ['value' => $this->id]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Pending Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getPendingLicenseSupports()
	{
		return $this->hasMany(PendingLicenseSupport::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Pending Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getFilterPendingLicenseSupports()
	{
		$product_id = Config::getProfileProductId();
		return $this->getPendingLicenseSupports()->filterProduct($product_id);
	}

	/**
	 * Get Generated Licenses
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getGeneratedLicenses()
	{
		return $this->hasMany(License::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Generated Supports
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getGeneratedSupports()
	{
		return $this->hasMany(Support::className(), ['payment_id' => 'id']);
	}

	/**
	 * Get Generated Annual Maintenances and Supports
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getGeneratedUpgradeProtections()
	{
		return $this->hasMany(UpgradeProtection::className(), ['payment_id' => 'id']);
	}


}

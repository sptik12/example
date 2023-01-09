<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use yii\web\ServerErrorHttpException;
use yii\web\NotFoundHttpException;
use common\helpers\Html;
use common\helpers\Generator;
use common\helpers\KeyGenerator;
use common\helpers\Config;
use backend\models\ProfileForm;

/**
 * This is the model class for table "license".
 *
 * @property string $id
 * @property int|null $email_id
 * @property int $product_info_id
 * @property int $license_type_id
 * @property string|null $not_before_date
 * @property string|null $not_after_date
 * @property string $owner_name
 * @property string|null $company
 * @property int $flags
 * @property string|null $details
 * @property int|null $upgrade_protection_days
 * @property int|null $is_uploaded_to_site
 * @property string|null $created_at
 * @property string|null $updated_at
 * @property int|null $owner_id
 * @property int|null $is_valid
 * @property string $payment_id
 * @property string|null $upgrade_protection_end_date
 */
class License extends BaseActiveRecord
{
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
	public $subversion;

	/**
	 * @var string
	 */
	public $license_type_name;

	/**
	 * @var string
	 */
	public $email_address;

	/**
	 * @var int
	 */
	public $quantity=1;

	/**
	 * @var integer
	 */
	public $date_limit;

	/**
	 * @var string
	 */
	public $date_limit_period;

	/**
	 * @var string
	 */
	public $license_id_prefix;

	/**
	 * @var array
	 */
	public $parent_license_ids = [];

	/**
	 * @var array
	 */
	public $files = [];

	/**
	 * @var array
	 */
	public $trait_values = [];

	/**
	 * @var array
	 */
	public $attachment_ids = [];

	/**
	 * @var integer
	 */
	public $include_support;

	/**
	 * @var integer
	 */
	public $max_count_request;

	/**
	 * @var string
	 */
	public $support_end_date;

	/**
	 * @var string
	 */
	public $up_min_date;

	/**
	 * @var string
	 */
	public $up_max_date;

	/**
	 * @var integer
	 */
	public $delivery_count=1;

	/**
	 * @var integer
	 */
	public $license_upgrade_count=0;

	/**
	 * @var integer
	 */
	public $level=0;

	/**
	 * @var integer
	 */
	public $ams;

	/**
	 * @var string
	 */
	public $ams_period;

	/**
	 * @var integer
	 */
	public $ignore_ams_date_validation=0;

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'license';
	}

	/**
	 * {@inheritdoc}
	 * @return LicenseQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new LicenseQuery(get_called_class());
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['owner_name', 'email_address', 'company'], 'filter', 'filter' => 'trim', 'on' => ['create', 'update', 'bulk-update']],
			[['license_id_prefix', 'details'], 'filter', 'filter' => 'trim', 'on' => ['create']],
			//[['id', 'license_type_id', 'owner_name', 'flags'], 'required'],
			[['email_id', 'product_info_id', 'initial_product_info_id', 'license_type_id', 'flags', 'upgrade_protection_days', 'is_uploaded_to_site', 'date_limit', 'ams', 'license_upgrade_count'], 'integer'],
			[['not_before_date', 'not_after_date', 'created_at', 'updated_at', 'parent_license_ids', 'files', 'trait_values', 'attachment_ids', 'license_type_name', 'upgrade_protection_end_date', 'ams_period', 'date_limit_period', 'up_min_date', 'up_max_date'], 'safe'],
			[['id'], 'string', 'max' => 64],
			[['owner_name', 'company'], 'string', 'max' => 128],
			[['details'], 'string', 'max' => 256],
			[['id'], 'unique'],
			[['email_address'], 'string', 'max' => 64],
			['email_address', 'email'],
			[['quantity', 'subversion'], 'integer'],
			[['traits'], 'safe'],
			[['is_valid'], 'safe'],
			[['license_id_prefix'], 'string', 'max' => 28],
			['license_id_prefix', 'match', 'pattern' => '/^[a-z0-9\-_]*$/'],
            [['files'], 'file', 'skipOnEmpty' => true, 'extensions' => Yii::$app->params['attachment.extensions'], 'maxFiles' => Yii::$app->params['attachment.maxFiles']],
			[['delivery_count'], 'integer', 'min' => 0],
			[['payment_id'], 'safe'],

			// scenario rules
			[['quantity', 'subversion'], 'required', 'on' => ['create']],
			[['owner_name', 'email_address', 'license_type_name'], 'required', 'on' => ['create', 'update', 'bulk-update']],
			[['not_before_date', 'not_after_date'], 'filter', 'filter' => function ($value) {
				return null;
			}, 'on' => ['create'], 'when' => function ($model) {
				return $model->date_limit ? false : true;
			}],
			[['not_before_date'], 'filter', 'filter' => function ($value) {
				return $this->curdateDb;
			}, 'on' => ['create'], 'when' => function ($model) {
				return $model->date_limit;
			}],
			[['not_after_date'], 'required', 'on' => ['create'], 'enableClientValidation' => false, 'when' => function ($model) {
				return $model->date_limit;
			}],
			[['delivery_count'], 'required', 'on' => ['bulk-delivery']],
			[['delivery_count'], 'integer', 'min' => 1, 'on' => ['bulk-delivery'], 'tooSmall' => Yii::t('app', 'At least one Delivery required')],
			[['upgrade_protection_end_date'], 'filter', 'filter' => function ($value) {
				return null;
			}, 'on' => ['create'], 'when' => function ($model) {
				return $model->ams ? false : true;
			}],
			[['upgrade_protection_end_date'], 'required', 'enableClientValidation' => false, 'on' => ['create'], 'when' => function ($model) {
				return $model->ams;
			}],
			[['upgrade_protection_end_date'], 'validateUpgradeProtectionEndDate', 'on' => ['create'], 'when' => function ($model) {
				return $model->ams && $model->date_limit;
			}],

			[['upgrade_protection_end_date'], 'required', 'on' => ['up-bulk-create']],
			['upgrade_protection_end_date', 'compare', 'compareAttribute' => 'up_min_date', 'operator' => '>', 'on' => ['up-bulk-create'], 'enableClientValidation' => false, 'message' => Yii::t('app', 'AMS Date must be greater than current AMS Date.'), 'when' => function ($model) {
				return $model->ignore_ams_date_validation ? false : true;
			}],
			['upgrade_protection_end_date', 'compare', 'compareAttribute' => 'up_max_date', 'operator' => '<=', 'on' => ['up-bulk-create'], 'enableClientValidation' => false, 'message' => Yii::t('app', 'AMS Date cannot be later than License Valid until date.')],

			//support
			[['include_support', 'max_count_request', 'support_end_date'], 'safe'],
			[['max_count_request', 'support_end_date'], 'required', 'on' => ['create'], 'enableClientValidation' => false, 'when' => function ($model) {
				return $model->include_support;
			}],


		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function validateUpgradeProtectionEndDate($attribute, $params)
    {
		if(!$this->hasErrors()){
			if ($this->hasDateLimit() && ($this->not_after_date < $this->upgrade_protection_end_date)){
				$this->addError('upgrade_protection_end_date', Yii::t('app', 'AMS End Date: {ams_end_date} cannot be later than License End Date: {end_date}.', ['ams_end_date' => Yii::$app->formatter->asDate($this->upgrade_protection_end_date), 'end_date' => Yii::$app->formatter->asDate($this->not_after_date)]));
			}
		}
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/license/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName()
	{
		return Html::substitute(Yii::$app->params['license.name.layout'], $this);
	}

	/**
	 * Get encoded Id with type
	 * @return string
	 */
	public function getEncodedId()
	{
		return Html::substitute('<span class="force-select">{id}</span> (<b>{licenseTypeName}</b>)', ['id' => Html::encode($this->id), 'licenseTypeName' => Html::encode($this->licenseTypeDisplayName)]);
	}

	/**
	 * Get Issued
	 * @return string
	 */
	public function getIssued()
	{
		return Html::substitute('{created_at} ({initialProductInfoName})', ['created_at' => Yii::$app->formatter->asDate($this->created_at), 'initialProductInfoName' => $this->initialProductInfoName]);
	}

	/**
	 * Get Issue Date
	 * @param string $format defaults to null
	 * @return string
	 */
	public function getIssueDate($format=null)
	{
		return Yii::$app->formatter->asDate($this->created_at, $format);
	}

	/**
	 * Get License End Date
	 * @param string $format defaults to 'short'
	 * @return string
	 */
	public function getEndDate($format='short')
	{
		return $this->not_after_date? Yii::$app->formatter->asDate($this->not_after_date, $format) : '';
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
	 * Get safe Company
	 * @return string
	 */
	public function getSafeCompany($default='Unknown company or home user', $encode=true)
	{
		$res = ($this->company != null && strlen($this->company)) ? $this->company : $default;
		return $encode ? Html::encode($res) : $res;
	}

	/**
	 * Get owner name
	 * @return string
	 */
	public function getOwnerName($encode=true)
	{
		return $encode ? Html::encode($this->owner_name) : $this->owner_name;
	}

	/**
	 * Get ProductInfo Name
	 * @return string
	 */
	public function getProductInfoName()
	{
		return ArrayHelper::getValue($this, 'productInfo.name');
	}

	/**
	 * Get initial ProductInfo Name
	 * @return string
	 */
	public function getInitialProductInfoName()
	{
		return ArrayHelper::getValue($this, 'initialProductInfo.name');
	}

	/**
	 * Get LicenseType name
	 * @return string
	 */
	public function getLicenseTypeName()
	{
		return ArrayHelper::getValue($this, 'licenseType.name');
	}

	/**
	 * Get LicenseType name
	 * @return string
	 */
	public function getLicenseTypeDisplayName()
	{
		$name = $this->getLicenseTypeName();

		$product_id = ($this->product_info_id) ? $this->getProductId() : $this->product_id;

		if ($product_id){
			if ($template = Config::getLicenseTemplate($product_id, null, $name)){
				$name = ArrayHelper::getValue($template, 'name', $name);
			}
		}
		return $name;
	}

	/**
	 * Get License Type Full Name
	 * @return string
	 */
	public function getLicenseTypeFullName()
	{
		$name = $this->getLicenseTypeName();

		$product_id = ($this->product_info_id) ? $this->getProductId() : $this->product_id;

		if ($product_id){
			if($template = Config::getLicenseTemplate($product_id, null, $name)){
				$name = ArrayHelper::getValue($template, 'full_name', $name);
			}
		}
		return $name;
	}

	/**
	 * Get Product id
	 * @return string
	 */
	public function getProductId()
	{
		return ArrayHelper::getValue($this, 'productInfo.product_id');
	}

	/**
	 * Get Product Name
	 * @return string
	 */
	public function getProductName()
	{
		return ArrayHelper::getValue($this, 'productInfo.product.name');
	}

	/**
	 * Get Product Commercial Name
	 * @return string
	 */
	public function getProductCommercialName()
	{
		return ArrayHelper::getValue($this, 'productInfo.commercialName');
	}

	/**
	 * Get Product commercial_name
	 * @return string
	 */
	public function getCommercialName()
	{
		return ArrayHelper::getValue($this, 'productInfo.commercial_name');
	}

	/**
	 * Get License Product base version
	 * @return string
	 */
	public function getProductVersion()
	{
		return ArrayHelper::getValue($this, 'productInfo.version');
	}

	/**
	 * Get License Product subversion
	 * @return string
	 */
	public function getProductSubversion()
	{
		return ArrayHelper::getValue($this, 'productInfo.subversion');
	}

	/**
	 * Get Product title
	 * @return string
	 */
	public function getProductTitle()
	{
		if ($this->product_id){
			$res = ArrayHelper::getValue(Config::getProduct($this->product_id), 'name');
			if ($this->version !== null){
				$res .= Html::substitute(Yii::$app->params['user.version.layout'], ['version' => $this->version]);
			}
			return $res;
		}
	}

	/**
	 * Get Users Number
	 * @return string
	 */
	public function getUsersNumber()
	{
		$traitValues = $this->getTraitValues()->joinWith('traitName')->where(['trait_name.name'=>'user.number'])->all();
		if ($traitValues) {
			$traitValue = $traitValues[0];
			return $traitValue->int_value;
		}
		return null;
	}

	/**
	 * Get value of the specified trait
	 * @param $type string Trait type (string, int, etc.)
	 * @param $name string Trait name (e.g. 'user.number')
	 * @return mixed
	 */
	public function getTrait($type, $name)
	{
		$traitValues = $this->getTraitValues()->joinWith('traitName')->where(['trait_name.name'=>$name])->all();
		if ($traitValues) {
			$traitValue = $traitValues[0];
			return match ($type) {
				'int' => $traitValue->int_value,
				'string' => $traitValue->string_value,
				default => null,
			};
		}
		return null;
	}

	/**
	 * Get Link
	 * @return string
	 */
	public function getLink($options=['class' => 'text-nowrap'])
	{
		return Yii::$app->user->can('license') ? Yii::$app->controller->renderPartial('/license/_link', ['model' => $this, 'options' => $options]) : Html::tag('span', Html::encode($this->id), $options);
	}

	/**
	 * Get Card
	 * @return string
	 */
	public function getCard($options=[])
	{
		return Yii::$app->controller->renderPartial('/license/_card', ['model' => $this, 'options' => $options]);
	}

	/**
	 * Get View Attributes
	 * @return array
	 */
	public function getViewAttributes()
	{
		return [
			'id' => $this->id,
			'encodedId' => $this->encodedId,
			'issued' => $this->issued,
			'ams' => $this->getAMSEndDateMessage(),
			'amsClass' => $this->upgradeProtectionCss,
			'text' => $this->name,
			'email' => Html::encode($this->emailAddress),
			'company' => Html::encode($this->company),
			'contact' => Html::encode($this->owner_name),
		];
	}

	/**
	 * Get Search Link
	 * @return string
	 */
	public function getSearchLink($attribute, $icon='search', $iconOptions = ['class' => 'ml-2'], $options=[])
	{
		$value = $this->getAttribute($attribute);
		$options['title'] = Yii::t('app', 'Search for Licenses by {value}', ['value' => $value]);
		$route = ['/license', 'clear' => 1, 'LicenseSearch[' . $attribute . ']' => $value];

		return Html::a(Html::icon($icon, $iconOptions), $route, $options);
	}

	/**
	 * Get Parent Links
	 * @return string
	 */
	public function getParentLinks($parent_delimiter=' &lArr; ', $sibling_delimiter=', ', $options=[], $level=0)
	{
		$level++;
		$values = [];
		foreach ($this->parents as $model){
			$value = $model->getLink($options);
			if ($level < 10 && ($parent_links = $model->getParentLinks($parent_delimiter, $sibling_delimiter, $options, $level))){
				$value .= $parent_delimiter . $parent_links;
			}
			$values[] = $value;
		}
		return (count($values) > 1 && $level > 1) ? '[' . implode($sibling_delimiter, $values) . ']' : implode($sibling_delimiter, $values);
	}

	/**
	 * Get Parent Models
	 * @return array
	 */
	public function getParentModels($level=-1)
	{
		$level++;
		$res = [];
		foreach ($this->getParents()->with(['licenseType', 'licenseInvalidations', 'productInfo', 'email', 'user'])->ordered()->all() as $model){
			$model->level = $level;
			$res[$model->id] = $model;
			if ($level < 100 && ($models = $model->getParentModels($level))){
				$res = ArrayHelper::merge($res, $models);
			}
		}
		return $res;
	}

	/**
	 * Get Child Models
	 * @return array
	 */
	public function getChildModels($level=-1)
	{
		$level++;
		$res = [];
		foreach ($this->getChildren()->with(['licenseType', 'licenseInvalidations', 'productInfo', 'email', 'user'])->ordered()->all() as $model){
			$model->level = $level;
			$res[$model->id] = $model;
			if ($level < 100 && ($models = $model->getChildModels($level))){
				$res = ArrayHelper::merge($res, $models);
			}
		}
		return $res;
	}

	/**
	 * Get Children Links
	 * @return string
	 */
	public function getChildrenLinks($parent_delimiter=' &rArr; ', $sibling_delimiter=', ', $options=[], $level=0)
	{
		$level++;
		$values = [];
		foreach ($this->children as $model){
			$value = $model->getLink($options);
			if ($level < 10 && ($parent_links = $model->getChildrenLinks($parent_delimiter, $sibling_delimiter, $options, $level))){
				$value .= $parent_delimiter . $parent_links;
			}
			$values[] = $value;
		}
		return (count($values) > 1 && $level > 1) ? '[' . implode($sibling_delimiter, $values) . ']' : implode($sibling_delimiter, $values);
	}

	/**
	 * Get Annual Maintenance and Support End Date message
	 * @return string
	 */
	public function getUpgradeProtectionEndDateMessage()
	{
		if ($end_date = $this->getUpgradeProtectionEndDate()) {
			return $this->isUpgradeProtectionExpired() ? Yii::t('app', 'Expired on {date}', ['date' => Yii::$app->formatter->asDate($end_date)])
				: Yii::t('app', 'Valid until {date}', ['date' => Yii::$app->formatter->asDate($end_date)]);
		}
		return Yii::t('app', 'None');
	}

	/**
	 * Get AMS End Date message
	 * @return string
	 */
	public function getAMSEndDateMessage()
	{
		if  ($this->getUpgradeProtectionEndDate()) {
			return $this->isVersionUpdated() ? Html::substitute('{upgradeProtectionEndDateMessage} ({productInfoName})', $this) : $this->upgradeProtectionEndDateMessage;
		}
		return Yii::t('app', 'None');
	}

	/**
	 * Get Annual Maintenance and Support End Date formatted by $format param
	 * @param string $default defaults to null
	 * @param string $format defaults to 'Y-m-d'
	 * @return string
	 */
	public function getUpgradeProtectionEndDate($default = null, $format = 'Y-m-d')
	{
		return $this->hasUpgradeProtection() ? date($format, strtotime($this->upgrade_protection_end_date)) : $default;
		//return ($days = $this->upgrade_protection_days) ? date($format, strtotime($this->created_at . ' + ' . (int)$days . ' days')) : $default;
	}

	/**
	 * Get UpgradeProtection css class
	 * @return string
	 */
	public function getUpgradeProtectionCss()
	{
		if ($this->hasUpgradeProtection()){
			$key = $this->isUpgradeProtectionExpired() ? 'upgradeProtection.expired' : ($this->isUpgradeProtectionExpiresSoon() ? 'upgradeProtection.expiresSoon' : 'upgradeProtection.valid');
		}
		else{
			$key = 'upgradeProtection.no';
		}
		return $this->getCssClass($key);
	}

	/**
	 * whether Annual Maintenance and Support Expired
	 * @return bool
	 */
	public function isUpgradeProtectionExpired()
	{
		return ($end_date = $this->getUpgradeProtectionEndDate()) ? $end_date < $this->curdateDb : null;
	}

	/**
	 * whether Annual Maintenance and Support Expires Soon
	 * @return bool
	 */
	public function isUpgradeProtectionExpiresSoon()
	{
		return ($end_date = $this->getUpgradeProtectionEndDate()) ?
			$end_date >= $this->curdateDb && $end_date <= date('Y-m-d', strtotime($this->curdateDb . ' + ' . Yii::$app->params['upgradeProtection.daysToExpireWarning'] . ' days'))
				: null;
	}

	/**
	 * whether Date limit is set and Expired
	 * @return bool
	 */
	public function isExpired()
	{
		return $this->hasDateLimit() ? $this->not_after_date < $this->curdateDb : false;
	}

	/**
	 * whether has Date limit
	 * @return bool
	 */
	public function hasDateLimit()
	{
		return $this->not_after_date ? true : false;
	}

	/**
	 * Get Support End Date formatted by $format param
	 * @param string $default defaults to null
	 * @param string $format defaults to 'Y-m-d'
	 * @return string
	 */
	public function getSupportEndDate($default = null, $format = 'Y-m-d')
	{
		$key = 'getSupportEndDate-'. $this->id;
		if (!array_key_exists($key, static::$_cache)) {
			//$end_date = $this->getSupports()->select('MAX(support.end_date)')->scalar();
			if ($end_dates = ArrayHelper::getColumn($this->supports, 'end_date')){
				$end_date = max($end_dates);
			}
			else{
				$end_date = false;
			}
			static::$_cache[$key] = $end_date;
		}
		$end_date = static::$_cache[$key];

		return ($end_date) ? date($format, strtotime($end_date)) : $default;
	}

	/**
	 * Get Support End Date message
	 * @return string
	 */
	public function getSupportEndDateMessage()
	{
		if ($end_date = $this->getSupportEndDate()) {
			return $this->isSupportExpired() ? Yii::t('app', 'Expired on {date}', ['date' => Yii::$app->formatter->asDate($end_date)])
				: Yii::t('app', 'Valid until {date}', ['date' => Yii::$app->formatter->asDate($end_date)]);
		}
	}

	/**
	 * whether Support Expired
	 * @return bool
	 */
	public function isSupportExpired()
	{
		return ($end_date = $this->getSupportEndDate()) ? $end_date < $this->curdateDb : null;
	}

	/**
	 * Get Support css class
	 * @return string
	 */
	public function getSupportCss()
	{
		$key = (($isSupportExpired = $this->isSupportExpired()) === null) ? 'support.no' : ($isSupportExpired ? 'support.expired' : 'support.valid');
		return $this->getCssClass($key);
	}

	/**
	 * Get date limit
	 * @return string
	 */
	public function getDateLimit($delimiter = ' - ')
	{
		$res = [];
		if ($this->not_before_date || $this->not_after_date) {
			$res[] = Yii::$app->formatter->asDate($this->not_before_date);
			$res[] = Yii::$app->formatter->asDate($this->not_after_date);
		}
		return implode($delimiter, $res);
	}

	/**
	 * Set date limit
	 * @return void
	 */
	public function setDateLimit($period)
	{
		if ($period) {
			$this->date_limit = 1;
			$this->not_before_date = $this->curdateDb;
			$this->not_after_date = date('Y-m-d', strtotime($this->curdateDb . ' + ' . (int)$period . ' days'));
			$this->date_limit_period = ''; //custom
		}
	}

	/**
	 * Get Config Settings
	 * @return array
	 */
	public function getSettings()
	{
		$settings = ($this->product_info_id) ? ArrayHelper::getValue($this, 'productInfo.settings') : Config::getSettings($this->product_id, $this->version);
		if (empty($settings)) {
			return [];
			throw new ServerErrorHttpException(Yii::t('app', 'Invalid configuration data, settings for product "{product_id}" not found.', ['product_id' => $this->product_id]));
		}
		return $settings;
	}

	/**
	 * Get Config Setting
	 * @param string $key
	 * @param mixed $default
	 * @return string
	 */
	public function getSetting($key, $default=null)
	{
		return ArrayHelper::getValue($this->settings, $key, ArrayHelper::getValue(Yii::$app->params, 'product.' . $key, $default));
	}

	/**
	 * Get Annual Maintenance and Support count
	 * @return string
	 */
	public function getUpgradeProtectionCount($count=null, $options = [], $route = ['/upgrade-protection'], $param = 'id')
	{
		$count = ($count===null) ? count($this->upgradeProtections) : $count;
		if ($route && $count && Yii::$app->user->can('upgrade-protection')) {
			$options['data-pjax'] = 0;
			$options['title'] = Yii::t('app', 'Open Annual Maintenances and Supports of License: {value}', ['value' => $this->id]);
			$count = ($route) ? Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options) : $count;
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}
	
	/**
	 * Get latest UpgradeProtection Id
	 * @return string
	 */
	public function getUpgradeProtectionId()
	{
		return $this->getUpgradeProtections()->select('id')->ordered()->scalar();
	}

	/**
	 * Get Support count
	 * @return string
	 */
	public function getSupportCount($count=null, $options = [], $route = ['/support', 'clear' => 1], $param = 'SupportSearch[license_id]')
	{
		$count = ($count===null) ? count($this->supports) : $count;
		if ($route && $count && Yii::$app->user->can('support')) {
			$options['data-pjax'] = 0;
			$options['title'] = Yii::t('app', 'Search for Supports by License: {value}', ['value' => $this->id]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Delivery count
	 * @return string
	 */
	public function getDeliveryCount($count=null, $options = [], $route = ['/delivery', 'clear' => 1], $param = 'DeliverySearch[license_id]')
	{
		$count = ($count===null) ? count($this->deliveries) : $count;
		if ($route && $count && Yii::$app->user->can('delivery')) {
			$options['data-pjax'] = 0;
			$options['title'] = Yii::t('app', 'Search for Deliveries by License: {value}', ['value' => $this->id]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'License ID'),
			'email_id' => Yii::t('app', 'Email'),
			'email_address' => Yii::t('app', 'Email'),
			'product_info_id' => Yii::t('app', 'Product'),
			'initial_product_info_id' => Yii::t('app', 'Initial Version'),
			'subversion' => Yii::t('app', 'Version'),
			'license_type_id' => Yii::t('app', 'License Type'),
			'not_before_date' => Yii::t('app', 'Start Date'),
			'not_after_date' => Yii::t('app', 'License valid until'),
			'owner_name' => Yii::t('app', 'Contact'),
			'company' => Yii::t('app', 'Company'),
			'flags' => Yii::t('app', 'Flags'),
			'details' => Yii::t('app', 'Message'),
			'upgrade_protection_days' => Yii::t('app', 'Annual Maintenance and Support'),
			'upgrade_protection_end_date' => ($this->scenario == 'create') ? Yii::t('app', 'AMS valid until') :  Yii::t('app', 'AMS Date'),
			'is_uploaded_to_site' => Yii::t('app', 'Is Uploaded To Site'),
			'created_at' => Yii::t('app', 'Issue Date'),
			'issued' => Yii::t('app', 'Issued'),
			'updated_at' => Yii::t('app', 'Updated At'),
			'owner_id' => Yii::t('app', 'Initiator'),
			'is_valid' => Yii::t('app', 'Is Valid'),
			'parent_license_ids' => Yii::t('app', 'Upgraded Licenses'),
			'files' => Yii::t('app', 'Add Attachments'),
			'attachments' => Yii::t('app', 'Attachments'),
			'attachment_ids' => Yii::t('app', 'Attachments'),
			'license_type_name' => Yii::t('app', 'License Type'),
			'include_support' => Yii::t('app', 'Include Support'),
			'max_count_request' => Yii::t('app', 'Max Qty of Requests'),
			'support_end_date' => Yii::t('app', 'End Date'),
			'keyword' => Yii::t('app', 'Text with License IDs'),
			'phrase' => Yii::t('app', 'Search'),
			'payment_id' => Yii::t('app', 'Payment'),
			'quantity' => Yii::t('app', 'Number of licenses'),
			'ams' => Yii::t('app', 'Annual Maintenance and Support'),
			'ams_period' => Yii::t('app', 'AMS period'),
			'date_limit_period' => Yii::t('app', 'Period'),
			'license_id_prefix' => Yii::t('app', 'License ID Prefix'),
			'date_limit' => Yii::t('app', 'Date limit'),
		];
	}


	/**
	 * Get Attachments
	 * @return \yii\db\ActiveQuery
	 */
	public function getAttachments()
	{
		return $this->hasMany(Attachment::className(), ['id' => 'attachment_id'])->viaTable('attachments_licenses', ['license_id' => 'id']);
	}

	/**
	 * Get AttachmentsLicenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getAttachmentsLicenses()
	{
		return $this->hasMany(AttachmentsLicenses::className(), ['license_id' => 'id']);
	}

	/**
	 * Get LicenseInvalidations
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenseInvalidations()
	{
		return $this->hasMany(LicenseInvalidation::className(), ['license_id' => 'id']);
	}

	/**
	 * Get LicenseUpgrades
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenseUpgrades()
	{
		return $this->hasMany(LicenseUpgrade::className(), ['license_id' => 'id']);
	}

	/**
	 * Get Parent LicenseUpgrades
	 * @return \yii\db\ActiveQuery
	 */
	public function getParentLicenseUpgrades()
	{
		return $this->hasMany(LicenseUpgrade::className(), ['parent_license_id' => 'id']);
	}

	/**
	 * Get License Parents
	 * @return \yii\db\ActiveQuery
	 */
	public function getParents()
	{
		return $this->hasMany(License::className(), ['id' => 'parent_license_id'])->viaTable('license_upgrade', ['license_id' => 'id']);
	}

	/**
	 * Get License Children
	 * @return \yii\db\ActiveQuery
	 */
	public function getChildren()
	{
		return $this->hasMany(License::className(), ['id' => 'license_id'])->viaTable('license_upgrade', ['parent_license_id' => 'id']);
	}

	/**
	 * Get TraitValues
	 * @return \yii\db\ActiveQuery
	 */
	public function getTraitValues()
	{
		return $this->hasMany(TraitValue::className(), ['license_id' => 'id']);
	}

	/**
	 * Get UpgradeProtections
	 * @return \yii\db\ActiveQuery
	 */
	public function getUpgradeProtections()
	{
		return $this->hasMany(UpgradeProtection::className(), ['license_id' => 'id']);
	}

	/**
	 * Get Licenses Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenseSupports()
	{
		return $this->hasMany(LicenseSupport::className(), ['license_id' => 'id']);
	}

	/**
	 * Get Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getSupports()
	{
		return $this->hasMany(Support::className(), ['id' => 'support_id'])->viaTable('licenses_supports', ['license_id' => 'id']);
	}

	/**
	 * Get Deliveries
	 * @return \yii\db\ActiveQuery
	 */
	public function getDeliveries()
	{
		Delivery::$item_email_id = $this->email_id;

		return $this->hasMany(Delivery::className(), ['id' => 'delivery_id'])->viaTable('deliveries_licenses', ['license_id' => 'id']);
	}

	/**
	 * Get EventLogs
	 * @return \yii\db\ActiveQuery
	 */
	public function getEventLogs()
	{
		return $this->hasMany(EventLog::className(), ['license_id' => 'id']);
	}

	/**
	 * Get LicenseHistories
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenseHistories()
	{
		return $this->hasMany(LicenseHistory::className(), ['license_id' => 'id']);
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
	 * Get initial ProductInfo
	 * @return \yii\db\ActiveQuery
	 */
	public function getInitialProductInfo()
	{
		return $this->hasOne(ProductInfo::className(), ['id' => 'initial_product_info_id']);
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
	 * Get LicenseType
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenseType()
	{
		return $this->hasOne(LicenseType::className(), ['id' => 'license_type_id']);
	}

	/**
	 * @return bool
	 */
	public function hasUpgradeProtection()
	{
		return $this->upgrade_protection_end_date ? true : false;
	}

	/**
	 * @return bool
	 */
	public function hasLicenseInvalidations()
	{
		return count($this->licenseInvalidations) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function hasLicenseUpgrades()
	{
		return count($this->licenseUpgrades) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function hasParentLicenseUpgrades()
	{
		return count($this->parentLicenseUpgrades) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function hasUpgradeProtections()
	{
		return count($this->upgradeProtections) ? true : false;
	}

	/**
	 * @return bool
	 */
	public function hasSupports()
	{
		return count($this->supports) ? true : false;
	}

	/**
	 *
	 * @param array $parent_license_ids defaults to []
	 * @param int $count defaults to 20
	 */
	public function initLicenseUpgrades($parent_license_ids=[], $count = 20, $scenario='create')
	{
		$res = [];
		foreach($parent_license_ids as $parent_license_id){
			$res[]= new LicenseUpgrade(['license_id' => $this->id, 'parent_license_id' => $parent_license_id, 'product_id' => $this->product_id, 'max_created' => $this->created_at, 'scenario' => $scenario]);
		}
		$this->license_upgrade_count = count($res);

		while (count($res) < $count) {
			$res[]= new LicenseUpgrade(['license_id' => $this->id, 'product_id' => $this->product_id, 'max_created' => $this->created_at, 'scenario' => $scenario]);
		}
		return $res;
	}

	/**
	 *
	 * @param array $parent_license_ids defaults to []
	 */
	public function updateLicenseUpgrades($ids = [])
	{
		//$ids = $this->parent_license_ids ? $this->parent_license_ids : $parent_license_ids;
		$old_ids = [];
		foreach ($this->licenseUpgrades as $model) {
			$old_ids[] = $model->parent_license_id;
			if (!in_array($model->parent_license_id, $ids)) {
				$model->delete();
			}
		}
		foreach ($ids as $id) {
			if (!in_array($id, $old_ids)) {
				$this->addLicenseUpgrades($id);
			}
		}
	}

	/**
	 *
	 * @param array|string $parent_license_ids
	 */
	public function addLicenseUpgrades($parent_license_ids)
	{
		$parent_license_ids = is_array($parent_license_ids) ? $parent_license_ids : [$parent_license_ids];
		foreach ($parent_license_ids as $parent_license_id) {
			$model = new LicenseUpgrade();
			$model->parent_license_id = $parent_license_id;
			$model->license_id = $this->id;
			if(!$model->save(false)){
				throw new ServerErrorHttpException(Yii::t('app', 'Unable upgrade License "{parent_id}" to "{id}".', ['parent_license_id' => $parent_license_id, 'id' => $this->id]));
			}
			if ($parentLicense = $model->parentLicense){
				$parentLicense->addUpgradeLicenseInvalidation();
			}
		}
	}

	/**
	 *
	 */
	public function updateAttachmentsLicenses()
	{
		$ids = $this->attachment_ids ? $this->attachment_ids : [];
		$old_ids = [];
		foreach ($this->attachmentsLicenses as $model) {
			$old_ids[] = $model->attachment_id;
			if (!in_array($model->attachment_id, $ids)) {
				$model->delete();
			}
		}
		foreach ($ids as $id) {
			if (!in_array($id, $old_ids)) {
				$model = new AttachmentsLicenses();
				$model->attachment_id = $id;
				$model->license_id = $this->id;
				$model->save();
			}
		}
	}

	/**
	 * Add Attachments
	 * @return array added attachment ids
	 */
	public function addAttachments()
	{
		if ($this->files != null) {
			foreach($this->files as $file){
				$model = new Attachment();
				$model->file_name = $file->name;
				$model->file_type = $file->type;
				$model->file_content = file_get_contents($file->tempName);
				if($model->save()){
					$this->attachment_ids[] = $model->id;
				}
			}
		}
		return $this->attachment_ids;
	}

	/**
	 * Add UpgradeProtection
	 * @return int
	 */
	public function addUpgradeProtection()
	{
		if($this->ams){
			$model = new UpgradeProtection();
			$model->id = Generator::upgradeProtectionId();
			$model->license_id = $this->id;
			$model->email_id = $this->email_id;
			$model->owner_name = $this->owner_name;
			$model->company = $this->company;
			$model->buy_date = $this->curdateDb;
			$model->end_date = $this->upgrade_protection_end_date;
			$model->product_info_id = $this->product_info_id;
			$model->payment_id = $this->payment_id;
			if($model->save(false)){
				return $model->id;
			}
			else{
				throw new ServerErrorHttpException(Yii::t('app', 'Unable to add Annual Maintenance and Support to License "{id}".', ['id' => $this->id]));
			}
		}
	}

	/**
	 * Add Support
	 * @return bool
	 */
	public function addSupport()
	{
		if ($this->include_support) {
			$model = new Support();
			$model->id = Generator::supportId();
			$model->product_id = $this->product_id;
			$model->email_id = $this->email_id;
			$model->owner_name = $this->owner_name;
			$model->company = $this->company;
			$model->max_count_request = $this->max_count_request;
			$model->buy_date = $this->curdateDb;
			$model->end_date = $this->support_end_date;
			if($model->save(false)){
				return $model->id;
			}
			else{
				throw new ServerErrorHttpException(Yii::t('app', 'Unable to add Support to License "{id}"', ['id' => $this->id]));
			}
		}
	}

	/**
	 * Add LicenseSupport
	 * @return bool
	 */
	public function addLicenseSupport($support_id)
	{
		$model = new LicenseSupport();
		$model->license_id = $this->id;
		$model->support_id = $support_id;
		if(!$model->save(false)){
			throw new ServerErrorHttpException(Yii::t('app', 'Unable to add LicenseSupport to License "{id}".', ['id' => $this->id]));
		};
	}

	/**
	 * Add LicenseInvalidation
	 * @return bool
	 */
	public function addUpgradeLicenseInvalidation()
	{
		if ($license_ids = $this->getParentLicenseUpgrades()->select('license_id')->column()){
			$prefix = '-> ';
			$reason = $prefix . implode(', ', $license_ids);
			if ($model = $this->getLicenseInvalidations()->one()){
				$model->reason = (substr($model->reason, 0, strlen($prefix)) === $prefix) ? $reason : $model->reason . ', ' . $reason;
				if(!$model->save()){
					throw new ServerErrorHttpException(Yii::t('app', 'Unable to update License Invalidation of License "{id}".', ['id' => $this->id]));
				};
			}
			else{
				$model = new LicenseInvalidation(['license_id' => $this->id, 'reason' => $reason]);
				if(!$model->save()){
					throw new ServerErrorHttpException(Yii::t('app', 'Unable to add License Invalidation to License "{id}".', ['id' => $this->id]));
				};
			}
			if ($this->is_valid){
				$this->is_valid = 0;
				$this->save(false);
			}
		}
	}
	
	/**
	 *  @return bool
	 */
	public function isSupportEnabled($default=true)
	{
		return $this->getSetting('supportEnabled', $default);
	}

	/**
	 *  @return bool
	 */
	public function isLdap()
	{
		return $this->productId == Config::PRODUCT_LDAP_ADMINISTRATOR;
	}

	/**
	 *  @return bool
	 */
	public function isAdaxes()
	{
		return $this->productId == Config::PRODUCT_ADAXES;
	}

	/**
	 *  @return bool
	 */
	public function isVersionUpdated()
	{
		return $this->initial_product_info_id != $this->product_info_id;
	}

	/**
	 *  @return int
	 */
	public function getDefaultSupportRequests()
	{
		$licenseTemplate = Config::getLicenseTemplate($this->productId, $this->productVersion,
			($this->license_type_id) ? $this->licenseTypeName : $this->license_type_name);
		return ArrayHelper::getValue($licenseTemplate, 'defaultSupportRequests', 0);
	}

	/**
	 * Returns License Template Names as 'id' => 'name'
	 *
	 * @return array
	 */
	public function getLicenseTemplateItems()
	{
		return ($res = Config::getLicenseTemplateItems($this->product_id, $this->version)) ? $res : [];
	}

	/**
	 * Returns first License Template Id
	 *
	 * @return string
	 */
	public function getFirstLicenseTemplateId()
	{
		if ($items = Config::getLicenseTemplateItems($this->product_id, $this->version)) {
			reset($items);
			return key($items);
		}
	}

	/**
	 * generate key
	 *
	 * @return string
	 */
	public function getKey()
	{
		return KeyGenerator::generateLicense($this);
	}

	/**
	 * saves key and returns file path
	 *
	 * @return string
	 */
	public function getFilePath()
	{
		$licenseFileName = ArrayHelper::getValue($this, 'productInfo.settings.licenseFileName');
		if (empty($licenseFileName)){
			throw new ServerErrorHttpException(Yii::t('app', 'Invalid configuration data, "licenseFileName" not found in {product_id} settings.', ['product_id' => $this->product_id]));
		}
		$filePath = $this->path . "/" . $licenseFileName;
		file_put_contents($filePath, $this->getKey());

		if (file_exists($filePath)) {
			return $filePath;
		}
		else{
			throw new \yii\web\NotFoundHttpException ('license file does not exist');
		}
	}

	/**
	 * get path
	 *
	 * @return string
	 */
	public function getPath()
	{
		$path = Yii::$app->runtimePath . '/tmp/' . $this->id;
		if (!is_dir($path)){
			FileHelper::createDirectory($path);
		}
		return $path;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeSave($insert)
	{
		if ($insert) {
			$this->initial_product_info_id = $this->product_info_id;
		}
		return parent::beforeSave($insert);
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		UpgradeProtection::deleteAll(['license_id' => $this->id]);
		TraitValue::deleteAll(['license_id' => $this->id]);
		LicenseInvalidation::deleteAll(['license_id' => $this->id]);
		LicenseHistory::deleteAll(['license_id' => $this->id]);
		LicenseUpgrade::deleteAll(['license_id' => $this->id]);
		LicenseUpgrade::deleteAll(['parent_license_id' => $this->id]);
		DeliveriesLicenses::deleteAll(['license_id' => $this->id]);

		$this->logDelete($this->owner_name, $this->id);
		return parent::beforeDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		$this->logSave($this->owner_name, $insert, $changedAttributes, $this->id);
		parent::afterSave($insert, $changedAttributes);
	}
}

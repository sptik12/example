<?php

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;

use common\helpers\Config;
use common\helpers\Html;

/**
 * This is the model class for table "email".
 *
 * @property int $id
 * @property string $email
 * @property int $subscribe_ldap
 * @property int $subscribe_adaxes
 * @property int $is_valid
 * @property int $validate_count
 * @property string|null $created_at
 * @property string|null $updated_at
 */
class Email extends BaseActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'email';
	}

	/**
	 * @param string $email
	 * @param bool $insert
	 * @return string
	 */
	public static function getIdByAddress($email, $insert = true)
	{
		$model = static::findOne(['email' => $email]);
		if ($model) {
			return $model->id;
		} elseif ($insert) {
			$model = new Email();
			$model->email = $email;
			$model->subscribe_ldap = 0;
			$model->subscribe_adaxes = 0;
			$model->is_valid = 1;
			if ($model->save(true)) {
				return $model->id;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * @return EmailQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new EmailQuery(get_called_class());
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['email', 'subscribe_ldap', 'subscribe_adaxes'], 'required'],
			[['subscribe_adaxes', 'subscribe_ldap', 'is_valid', 'validate_count'], 'integer'],
			[['email'], 'string', 'max' => 64],
			[['email'], 'email'],
			[['created_at', 'updated_at'], 'safe'],
		];
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/email/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get Link
	 * @return string
	 */
	public function getLink($options = [])
	{
		return Yii::$app->user->can('email') ? Yii::$app->controller->renderPartial('/email/_link', ['model' => $this, 'options' => $options]) : Html::tag('span', Html::encode($this->address), $options);
	}

	/**
	 * Get decripted email address
	 * @return string
	 */
	public function getAddress()
	{
		return $this->email;
	}

	/**
	 * Get license count
	 * @return string
	 */
	public function getLicenseCount($options = [], $route = ['/license', 'clear' => 1], $param = 'LicenseSearch[email_address]')
	{
		$count = count($this->filterLicenses);
		if ($route && $count && Yii::$app->user->can('license')) {
			$options['title'] = Yii::t('app', 'Search for Licenses by {value}', ['value' => $this->address]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->address]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Delivery count
	 * @return string
	 */
	public function getDeliveryCount($options = [], $route = ['/delivery', 'clear' => 1], $param = 'DeliverySearch[email_address]')
	{
		$count = count($this->deliveries);
		if ($route && $count && Yii::$app->user->can('delivery')) {
			$options['title'] = Yii::t('app', 'Search for Deliveries by {value}', ['value' => $this->address]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->address]), $options);
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Annual Maintenance and Support count
	 * @return string
	 */
	public function getUpgradeProtectionCount($options = [], $route = ['/upgrade-protection/email', 'clear' => 1], $param = 'id')
	{
		$count = count($this->upgradeProtections);
		if ($route && $count && Yii::$app->user->can('upgrade-protection')) {
			$options['data-pjax'] = 0;
			$options['title'] = Yii::t('app', 'Open Annual Maintenances and Supports of {value}', ['value' => $this->address]);
			$count = ($route) ? Html::a($count, ArrayHelper::merge($route, [$param => $this->id]), $options) : $count;
		} else {
			$count = ($options) ? Html::tag('span', $count, $options) : $count;
		}
		return $count;
	}

	/**
	 * Get Support count
	 * @return string
	 */
	public function getSupportCount($options = [], $route = ['/support', 'clear' => 1], $param = 'SupportSearch[email_address]')
	{
		$count = count($this->supports);
		if ($route && $count && Yii::$app->user->can('support')) {
			$options['title'] = Yii::t('app', 'Search for Supports by {value}', ['value' => $this->address]);
			$count = Html::a($count, ArrayHelper::merge($route, [$param => $this->address]), $options);
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
			'id' => Yii::t('app', 'ID'),
			'email' => Yii::t('app', 'Email'),
			'subscribe_ldap' => Yii::t('app', 'Subscribed to LDAP'),
			'subscribe_adaxes' => Yii::t('app', 'Subscribed to Adaxes'),
			'is_valid' => Yii::t('app', 'Valid'),
			'validate_count' => Yii::t('app', 'Validate Count'),
			'created_at' => Yii::t('app', 'Created'),
			'owner_id' => Yii::t('app', 'Initiator'),
		];
	}

	/**
	 * Get UpgradeProtections
	 * @return \yii\db\ActiveQuery
	 */
	public function getUpgradeProtections()
	{
		return $this->hasMany(UpgradeProtection::className(), ['email_id' => 'id']);
	}

	/**
	 * Get Supports
	 * @return \yii\db\ActiveQuery
	 */
	public function getSupports()
	{
		return $this->hasMany(Support::className(), ['email_id' => 'id']);
	}

	/**
	 * Get Deliveries
	 * @return \yii\db\ActiveQuery
	 */
	public function getDeliveries()
	{
		Delivery::$item_email_id = $this->id;

		return $this->hasMany(Delivery::className(), ['email_id' => 'id']);
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
	 * Get Licenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getFilterLicenses()
	{
		$product_id = Config::getProfileProductId();
		//$version = Config::getProfileVersion();
		//$subversion = Config::getProfileSubversion();

		return $this->getLicenses()->filterProductInfo($product_id);
	}

	/**
	 * Get Licenses
	 * @return \yii\db\ActiveQuery
	 */
	public function getLicenses()
	{
		return $this->hasMany(License::className(), ['email_id' => 'id']);
	}

	/**
	 * whether valid
	 * @return bool
	 */
	public function isValid()
	{
		return $this->is_valid ? true : false;
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		$this->logDelete($this->email);
		return parent::beforeDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		$this->logSave($this->email, $insert, $changedAttributes);
		parent::afterSave($insert, $changedAttributes);
	}

}

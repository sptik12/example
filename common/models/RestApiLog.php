<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

/**
* This is the model class for table "rest_api_log".
*
* @property int $id
* @property string $product_id
* @property integer $version
* @property integer $subversion
* @property string $params
* @property string $url
* @property string $response
* @property integer $action
* @property integer $status
* @property string $created_at
*/
class RestApiLog extends BaseActiveRecord
{
	/*
	 *
	 */
	const STATUS_SUCCESS = 1;
	const STATUS_ERROR = 0;

	/*
	 *
	 */
	const ACTION_EMAIL_SUBSCRIBE = 1;
	const ACTION_EMAIL_UNSUBSCRIBE = 2;
	const ACTION_LICENSE_GET = 3;
	const ACTION_LICENSE_VALID = 4;
	const ACTION_DELIVERY_DOWNLOAD = 5;
	const ACTION_DELIVERY_ADD_DOWNLOAD = 6;
	const ACTION_PAYMENT_NEW = 7;
	const ACTION_PAYMENT_TRIAL = 8;

	/**
	* {@inheritdoc}
	*/
	public static function tableName()
	{
		return 'rest_api_log';
	}

	/**
	* {@inheritdoc}
	*/
	public function rules()
	{
		return [
			[['status'], 'integer'],
			[['url', 'params', 'response', 'referer'], 'string'],
			[['product_id', 'version', 'subversion', 'action', 'created_at'], 'safe'],
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'Id'),
			'product_id' => Yii::t('app', 'Product'),
			'version' => Yii::t('app', 'Version'),
			'subversion' => Yii::t('app', 'Subversion'),
			'params' => Yii::t('app', 'Initial Data'),
			'url' => Yii::t('app', 'Url'),
			'status' => Yii::t('app', 'Status'),
			'action' => Yii::t('app', 'Action'),
			'response' => Yii::t('app', 'Response'),
			'created_at' => Yii::t('app', 'Date'),
			'referer' =>  Yii::t('app', 'Referer'),
		];
	}

	/**
	 * Get route
	 * @return array
	 */
	public function getRoute($params = [])
	{
		return ArrayHelper::merge(['/rest-api-log/view'], $params, ['id' => $this->id]);
	}

	/**
	 * Get Event Actions
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getRestApiEventActions()
	{
		return $this->hasMany(RestApiLogAction::className(), ['rest_api_log_id' => 'id']);
	}

	/**
	 *
	 */
	public function beforeDelete()
	{
		$this->deleteRestApiEventActions();
		return parent::beforeDelete();
	}

	/**
	 * Delete Event Actions
	 */
	public function deleteRestApiEventActions()
	{
		foreach ($this->restApiEventActions as $model) {
			$model->delete();
		}
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
	 * {@inheritdoc}
	 * @return RestApiLogQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new RestApiLogQuery(get_called_class());
	}
}

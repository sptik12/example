<?php

namespace backend\models;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;

use common\models\Payment;
use common\models\ProductInfo;
use common\helpers\Config;
use common\helpers\Period;

/**
 * PaymentSearch represents the model behind the search form of `common\models\Payment`.
 */
class PaymentSearch extends Payment
{
	/**
	 * @var integer
	 */
	public $period;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->product_id = Config::getProfileProductId();
		//$this->version = Config::getProfileVersion();
		//$this->subversion = Config::getProfileSubversion();
		parent::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[[ 'id', 'order_id', 'cart_id', 'amount', 'email', 'owner_name', 'company', 'product_id', 'version', 'subversion'], 'safe'],
			[[ 'delivery_email', 'period', 'created_at', 'updated_at', 'completed', 'type'], 'safe'],
			[[ 'id', 'order_id', 'cart_id', 'email', 'owner_name', 'company'], 'filter','filter' => 'trim']
		];
	}

	/**
	 * @inheritdoc
	 */
	public function attributeLabels()
	{
		$rules = parent::rules();
		return $rules;
	}

	/**
	 * Returns Period Titles as 'id' => 'title'
	 *
	 * @return array
	 */
	public function getPeriodItems()
	{
		return Period::getItems();
	}

	/**
	 *
	 * @return array
	 */
	public function getProductInfoItems()
	{
		$items = ProductInfo::find()->with('product')->andFilterWhere(['product_id' => $this->product_id, 'version' => $this->version])
			->orderBy(['version' => SORT_ASC, 'subversion' => SORT_ASC])->all();
		return ArrayHelper::map($items, 'id', 'fullVersion');
	}

	/**
	 * Returns License Type Names as 'id' => 'name'
	 *
	 * @return array
	 */
	public function getLicenseTypeItems()
	{
		$ids = self::find()->select(['license_type_id'])->distinct()->filterProductInfo($this->product_id, $this->version, $this->subversion)->column();
		if ($this->license_type_id) {
			$ids[] = $this->license_type_id;
		}
		return LicenseType::find()->select('name')->andWhere(['id' => $ids])->ordered()->indexBy('id')->column();
	}

	/**
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = Payment::find()->indexBy('id');
		$query->with(['product']);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		$query->andFilterWhere(['like', self::tableName() . '.email', $this->email])
			->andFilterWhere(['like', self::tableName() . '.company', $this->company])
			->andFilterWhere(['like', self::tableName() . '.owner_name', $this->owner_name])
			->andFilterWhere(['like', self::tableName() . '.delivery_email', $this->delivery_email])
			->andFilterWhere(['like', self::tableName() . '.id', $this->id])
			->andFilterWhere(['like', self::tableName() . '.order_id', $this->order_id])
			->andFilterWhere(['like', self::tableName() . '.cart_id', $this->cart_id]);

		$query->andFilterWhere([self::tableName() . '.completed' => $this->completed])
				->andFilterWhere([self::tableName() . '.type' => $this->type]);

		if ($this->period) {
			$query->andWhere(Period::getCondition(self::tableName() . '.created_at', $this->period));
		}

		$query->andFilterWhere(['product_id' => $this->product_id,
			'version' => $this->version,
			'subversion' => $this->subversion]);

		return $dataProvider;
	}
}

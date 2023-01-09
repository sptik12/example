<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\License;
use common\models\LicenseType;
use common\models\ProductInfo;
use common\helpers\Config;
use common\helpers\Period;
use common\helpers\Html;
use yii\helpers\ArrayHelper;

/**
 * LicenseSearch represents the model behind the search form of `common\models\License`.
 */
class LicenseSearch extends License
{
	/**
	 * @var string
	 */
	const ID_DELIMITER = ',';
	
	/**
	 * @var int
	 */
	const ID_INVALIDATED = -1;
	
	/**
	 * @var integer
	 */
	public $period;
	
	/**
	 * @var integer
	 */
	public $up_period;
	
	/**
	 * @var integer
	 */
	public $valid_period;

	/**
	 * @var string
	 */
	public $support_id;

	/**
	 * @var string
	 */
	public $delivery_id;
	
	/**
	 * @var string
	 */
	public $keyword;
	
	/**
	 * @var string
	 */
	public $phrase;
	
	/**
	 * @var integer
	 */
	public $invalidated;

	/**
	 * @var array
	 */
	public $license_ids = [];
	
	/**
	 * @inheritdoc
	 */
	public function init()
	{
		$this->product_id = Config::getProfileProductId();
		parent::init();
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'not_before_date', 'not_after_date', 'owner_name', 'company', 'details', 'created_at', 'updated_at', 'email_address', 'product_id', 'support_id', 'keyword', 'phrase', 'upgrade_protection_end_date', 'product_info_id'], 'safe'],
			[['email_id', 'license_type_id', 'flags', 'upgrade_protection_days', 'is_uploaded_to_site', 'period', 'version', 'subversion', 'owner_id', 'delivery_id', 'up_period', 'invalidated', 'valid_period'], 'integer'],
			[['keyword', 'phrase', 'owner_name', 'company', 'email_address'], 'filter', 'filter' => 'trim'],
			[['product_info_id'], 'filter', 'filter' => function ($value) {	
				return ($this->product_id && $value && !ProductInfo::find()->with('product')->andWhere(['product_id' => $this->product_id, 'id' => explode(self::ID_DELIMITER, $value)])->exists()) ? null : $value;
			}],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function scenarios()
	{
		// bypass scenarios() implementation in the parent class
		return Model::scenarios();
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
		$items = LicenseType::find()->select('name')->andWhere(['id' => $ids])->ordered()->indexBy('id')->column();
		
		$product_ids = $this->product_id ? [$this->product_id] : Config::getProductIds();
		$templates = [];
		foreach($product_ids as $product_id){
			$templates = ArrayHelper::merge($templates, Config::getLicenseTemplateItems($product_id, null));
		}

		foreach($items as $id => $item){
			$items[$id] = ArrayHelper::getValue($templates, $item, $item);
		}
		return ArrayHelper::merge([static::ID_INVALIDATED =>  Yii::t('app', 'Invalidated')], $items);
	}

	/**
	 * Returns NotFoundMessage
	 *
	 * @return string
	 */
	public function getNotFoundMessage()
	{
		if ($count=count($this->license_ids)){
			$license_ids=License::find()->where(['id' => $this->license_ids])->select('id')->column();
			if ($not_found = array_diff($this->license_ids, $license_ids)){
				return (count($not_found) > 1) ?
					Yii::t('app', '{count} licenses were not found:<br/>{list}', ['count' => count($not_found), 'list' => implode('<br/>', $not_found)]) 
					: Yii::t('app', '1 license was not found: {list}', ['count' => count($not_found), 'list' => implode('<br/>', $not_found)]) ;
			}
		}
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
	public function getProductInfoItems(&$options=[])
	{
		$models = ProductInfo::find()->with('product')->andFilterWhere(['product_id' => $this->product_id])
			->orderBy(['product_id' => SORT_ASC,'version' => SORT_DESC, 'subversion' => SORT_DESC])->all();
			
		$products = ArrayHelper::index($models, null, 'productName');
		$res = $items = [];
		foreach($products as $product => $models){
			$versions = ArrayHelper::map($models, 'id', 'commercialVersion', 'version');
			$items = [];
			foreach($versions as $version => $subversions){
				$ids = implode(static::ID_DELIMITER, ArrayHelper::merge([0], array_keys($subversions)));
				$items[$ids] = Html::substitute('Version {version}', ['version' => Html::encode($version)]);
				$options[$ids] = ['class' => 'option-group'];
				foreach ($subversions as $id => $subversion){
					$items[$id] = '&emsp;' . Html::encode($subversion);
					$options[$id] = ['class' => 'option-child'];
				}
			}
			$res[$product] = $items;
		}
		return (count($res) > 1) ? $res : $items;
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
		$query = License::find()->indexBy('id');
		$query->with(['licenseType', 'productInfo']);
		$query->with(['email' => function (\yii\db\ActiveQuery $query) {
			$query->with([
				'licenses' => function (\yii\db\ActiveQuery $query) {
					$query->select(['id', 'email_id'])->asArray();
				},
				'filterLicenses' => function (\yii\db\ActiveQuery $query) {
					$query->select(['id', 'email_id'])->asArray();
				}
			]);
		}]);
		$query->with([
			'licenseInvalidations' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'license_id'])->asArray();
			},
			'licenseUpgrades' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'license_id'])->asArray();
			},
			'parentLicenseUpgrades' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'parent_license_id'])->asArray();
			},
			'supports' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'end_date'])->asArray();
			},
		]);
		
		// add conditions that should always apply here
		$query->filterProductInfo($this->product_id, $this->version, $this->subversion);

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);
		
		$dataProvider->sort->attributes['upgrade_protection_end_date'] = [
			'asc' => ['ISNULL(upgrade_protection_end_date)' => SORT_ASC, 'upgrade_protection_end_date' => SORT_ASC],
			'desc'=> ['upgrade_protection_end_date' => SORT_DESC]
		];

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		// grid filtering conditions
		$query->andFilterWhere([
			self::tableName() . '.email_id' => $this->email_id,
			'flags' => $this->flags,
			'upgrade_protection_days' => $this->upgrade_protection_days,
			'is_uploaded_to_site' => $this->is_uploaded_to_site,
			self::tableName() . '.is_valid' => $this->is_valid,
			self::tableName() . '.owner_id' => $this->owner_id,
		]);
		
		if ($this->license_type_id == static::ID_INVALIDATED){
			$query->invalidated();
		}
		else{
			$query->andFilterWhere([
				self::tableName() . '.license_type_id' => $this->license_type_id,
			]);
		}


		$query->andFilterWhere(['like', 'id', $this->id])
			->andFilterWhere(['like', self::tableName() . '.owner_name', $this->owner_name])
			->andFilterWhere(['like', self::tableName() . '.company', $this->company])
			->andFilterWhere(['like', self::tableName() . '.details', $this->details]);

		if ($this->period) {
			$query->andWhere(Period::getCondition(self::tableName() . '.created_at', $this->period));
		}
		if ($this->up_period) {
			$query->andWhere(Period::getCondition(self::tableName() . '.upgrade_protection_end_date', $this->up_period));
		}
		if ($this->valid_period) {
			$query->andWhere(Period::getCondition(self::tableName() . '.not_after_date', $this->valid_period));
		}
		if ($this->keyword) {
			$this->license_ids = self::extractIds($this->keyword);
			$query->andWhere([self::tableName() . '.id' => $this->license_ids]);
		}
		if ($this->invalidated) {
			$query->invalidated();
		}
		
		if ($this->product_info_id) {
			$query->andWhere([self::tableName() . '.product_info_id' => explode(self::ID_DELIMITER, $this->product_info_id)]);
		}

		$query->filterPhrase($this->phrase);
		$query->filterEmail($this->email_address);
		$query->filterSupport($this->support_id);
		$query->filterDelivery($this->delivery_id);

		return $dataProvider;
	}

	/**
	 * Returns list of ids from text
	 * @param string $text
	 * @return array
	 */
	public static function extractIds($text)
	{
		$res = [];
		if ($text){
			if (preg_match_all ('/[a-z0-9\-_]*[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}/si' , $text, $matches)){
				foreach ($matches[0] as $id) {
					$res[] = $id;
				}
			}
		}
		return array_unique($res);
	}
}

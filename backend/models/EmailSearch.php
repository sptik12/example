<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\Email;
use common\helpers\Period;

/**
 * EmailSearch represents the model behind the search form of `common\models\Email`.
 */
class EmailSearch extends Email
{

	/**
	 * @var integer
	 */
	public $period;

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'subscribe_ldap', 'subscribe_adaxes', 'is_valid', 'validate_count', 'period'], 'integer'],
			[['email', 'created_at'], 'safe'],
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
	 * Returns Period Titles as 'id' => 'title'
	 *
	 * @return array
	 */
	public function getPeriodItems()
	{
		return Period::getItems();
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
		$query = Email::find();
		$query->with([
			'licenses' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'email_id'])->asArray();
			},
			'upgradeProtections' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'email_id'])->asArray();
			},
			'supports' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'email_id'])->asArray();
			},
			'deliveries' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'email_id'])->asArray();
			},
		]);

		// add conditions that should always apply here

		$dataProvider = new ActiveDataProvider([
			'query' => $query,
		]);

		$this->load($params);

		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		// grid filtering conditions
		$query->andFilterWhere([
			'id' => $this->id,
			'subscribe_ldap' => $this->subscribe_ldap,
			'subscribe_adaxes' => $this->subscribe_adaxes,
			'is_valid' => $this->is_valid,
			'validate_count' => $this->validate_count,
			'created_at' => $this->created_at,
		]);
		
		if ($this->period) {
			$query->andWhere(Period::getCondition(self::tableName() . '.created_at', $this->period));
		}

		$query->andFilterWhere(['like', 'email', $this->email]);

		return $dataProvider;
	}
}

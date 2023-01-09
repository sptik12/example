<?php

namespace backend\models;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use common\models\UpgradeProtection;

/**
 * UpgradeProtectionSearch represents the model behind the search form of `common\models\UpgradeProtection`.
 */
class UpgradeProtectionSearch extends UpgradeProtection
{
	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id', 'license_id', 'owner_name', 'company', 'buy_date', 'end_date', 'created_at'], 'safe'],
			[['email_id', 'product_info_id', 'owner_id'], 'integer'],
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
	 * Creates data provider instance with search query applied
	 *
	 * @param array $params
	 *
	 * @return ActiveDataProvider
	 */
	public function search($params)
	{
		$query = UpgradeProtection::find();

		$query->with(['user', 'productInfo']);
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
			'email_id' => $this->email_id,
			'product_info_id' => $this->product_info_id,
			'buy_date' => $this->buy_date,
			'end_date' => $this->end_date,
			'created_at' => $this->created_at,
			'owner_id' => $this->owner_id,
		]);

		$query->andFilterWhere(['like', 'id', $this->id])
			->andFilterWhere(['like', 'license_id', $this->license_id])
			->andFilterWhere(['like', 'owner_name', $this->owner_name])
			->andFilterWhere(['like', 'company', $this->company]);

		return $dataProvider;
	}
}

<?php

namespace backend\models;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

use common\models\MailLog;
use common\helpers\Config;
use common\helpers\Format;
use yii\helpers\ArrayHelper;

/**
 * MailLogSearch represents the model behind the search form of `common\models\MailLog`.
 */
class MailLogSearch extends MailLog
{
	/**
	 * @var string
	 */
	public $start_date;

	/**
	 * @var string
	 */
	public $end_date;

	const RANGE_SEPARATOR = ' - ';

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['id'], 'integer'],
			[['email', 'product_id', 'license_id', 'support_id', 'delivery_id', 'created_at', 'start_date', 'end_date', 'letter_id', 'mail_template_id'], 'safe'],
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
	 * @inheritdoc
	 */
	public function init()
	{
		$this->product_id = Config::getProfileProductId();
		parent::init();
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
		$query = MailLog::find();
		$query->with([
			'license' => function (\yii\db\ActiveQuery $query) {
			},
			'support' => function (\yii\db\ActiveQuery $query) {
			},
			'delivery' => function (\yii\db\ActiveQuery $query) {
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
			'product_id' => $this->product_id,
			'mail_template_id' => $this->mail_template_id,
			'letter_id' => $this->letter_id,
		]);

		$query->andFilterWhere(['like', 'email', $this->email]);
		$query->andFilterWhere(['like', 'delivery_id', $this->delivery_id]);
		$query->andFilterWhere(['like', 'support_id', $this->support_id]);
		$query->andFilterWhere(['like', 'license_id', $this->license_id]);

		if ($this->created_at){
			$array = explode(self::RANGE_SEPARATOR, $this->created_at);
			$this->end_date = end($array);
			$this->start_date = reset($array);
		}

		if (!empty($this->start_date) && !empty($this->end_date)) {
			$query->andFilterWhere(['between', 'created_at',  Format::serverDatetime($this->start_date . ' 00:00:01'),  Format::serverDatetime($this->end_date . ' 23:59:59')]);
		}

		return $dataProvider;
	}

}

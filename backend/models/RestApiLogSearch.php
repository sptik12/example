<?php

namespace backend\models;

use Yii;
use yii\data\ActiveDataProvider;

use common\models\RestApiLog;
use common\helpers\Config;
use common\helpers\Format;
use yii\helpers\ArrayHelper;

/**
 * RestApiLogSearch represents the model behind the search form of `common\models\RestApiLog`.
 */
class RestApiLogSearch extends RestApiLog
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
			[['status', 'url', 'params', 'response', 'product_id', 'version', 'subversion', 'action', 'created_at', 'start_date', 'end_date', 'referer'], 'safe'],
		];
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
		$query = RestApiLog::find()->indexBy('id');
		$query->with(['restApiEventActions', 'product']);

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
			'status' => $this->status,
			'action' => $this->action,
		]);

		$query->andFilterWhere(['product_id' => $this->product_id,
			'version' => $this->version,
			'subversion' => $this->subversion]);

		if ($this->created_at){
			$array = explode(self::RANGE_SEPARATOR, $this->created_at);
			$this->end_date = end($array);
			$this->start_date = reset($array);
		}

		if (!empty($this->start_date) && !empty($this->end_date)) {
			$query->andFilterWhere(['between', 'created_at', Format::serverDatetime($this->start_date . ' 00:00:01'), Format::serverDatetime($this->end_date . ' 23:59:59')]);
		}

		$query->andFilterWhere(['like', self::tableName() . '.params', $this->params])
			->andFilterWhere(['like', self::tableName() . '.response', $this->response])
			->andFilterWhere(['like', self::tableName() . '.url', $this->url])
			->andFilterWhere(['like', self::tableName() . '.referer', $this->referer]);

		return $dataProvider;
	}
}

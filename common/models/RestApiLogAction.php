<?php

namespace common\models;

use Yii;
use yii\data\ActiveDataProvider;

/**
* This is the model class for table "rest_api_log_actions".
*
* @property int $id
* @property int $rest_api_log_id
* @property string $description
* @property string $created_at
* @property integer $status
 */
class RestApiLogAction extends BaseActiveRecord
{
	/**
	* {@inheritdoc}
	*/
	public static function tableName()
	{
		return 'rest_api_log_action';
	}

	/**
	* {@inheritdoc}
	*/
	public function rules()
	{
		return [
			[['rest_api_log_id', 'status'], 'integer'],
			[['rest_api_log_id'], 'required'],
			[['description', 'created_at'], 'safe'],
		];
	}

	/**
	* {@inheritdoc}
	*/
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app','Id'),
			'rest_api_log_id' => Yii::t('app','Rest Api Event Log Id'),
			'description' => Yii::t('app', 'Action'),
			'created_at' => Yii::t('app', 'Date'),
			'status' => Yii::t('app', 'Status'),
		];
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
		$query = RestApiLogAction::find();
		$dataProvider = new ActiveDataProvider([
			'query' => $query,
			'sort' => [
				'defaultOrder' => ['created_at' => SORT_ASC, 'id' => SORT_ASC],
				'attributes' => [
					'created_at'
				]
			],
		]);

		$this->load($params);
		if (!$this->validate()) {
			// uncomment the following line if you do not want to return any records when validation fails
			// $query->where('0=1');
			return $dataProvider;
		}

		// grid filtering conditions
		$query->andFilterWhere([
			'rest_api_log_id' => $this->rest_api_log_id,
		]);

		return $dataProvider;
	}

	/**
	* {@inheritdoc}
	* @return RestApiLogActionQuery the active query used by this AR class.
	*/
	public static function find()
	{
		return new RestApiLogActionQuery(get_called_class());
	}

	/**
	 * Get Parent Record
	 *
	 * @return \yii\db\ActiveQuery
	 */
	public function getParent()
	{
		return $this->hasOne(RestApiLog::className(), ['id' => 'rest_api_log_id']);
	}

}

<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[RestApiLog]].
 *
 * @see RestApiLog
 */
class RestApiLogQuery extends \yii\db\ActiveQuery
{
	/**
	 * {@inheritdoc}
	 * @return EventLog[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return RestApiLog|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/**
	 * Ordered scope
	 *
	 * @return $this
	 */
	public function ordered()
	{
		return $this->orderBy(['rest_api_log.created_at' => SORT_DESC]);
	}

}

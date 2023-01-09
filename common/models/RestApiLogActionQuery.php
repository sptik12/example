<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[RestApiLogAction]].
 *
 * @see RestApiLogAction
 */
class RestApiLogActionQuery extends \yii\db\ActiveQuery
{
	/**
	 * {@inheritdoc}
	 * @return RestApiLogAction[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return RestApiLogAction|array|null
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
		return $this->orderBy(['rest_api_log_actions.created_at' => SORT_ASC, 'id' => SORT_ASC]);
	}

}

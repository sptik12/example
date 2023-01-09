<?php

namespace common\models;

/**
 * This is the PaymentQuery class for [[PaymentQuery]].
 *
 * @see PaymentQuery
 */
class PaymentQuery extends \yii\db\ActiveQuery
{
	/**
	 * {@inheritdoc}
	 * @return License[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return Payment|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/*
	 * Completed payments
	 * @return $this
	 */
	public function completed()
	{
		return $this->andWhere("payment.completed = 1");
	}

	/*
	 * Incompleted payments
	 * @return $this
	 */
	public function incompleted()
	{
		return $this->andWhere("payment.completed = 0");
	}

	/**
	 * Ordered scope
	 *
	 * @return $this
	 */
	public function ordered()
	{
		return $this->orderBy(['payment.created_at' => SORT_ASC]);
	}

}

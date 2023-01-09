<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[TraitValue]].
 *
 * @see TraitValue
 */
class TraitValueQuery extends \yii\db\ActiveQuery
{
	/*public function active()
	{
		return $this->andWhere('[[status]]=1');
	}*/

	/**
	 * {@inheritdoc}
	 * @return TraitValue[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return TraitValue|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}

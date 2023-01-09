<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[UpgradeProtection]].
 *
 * @see UpgradeProtection
 */
class UpgradeProtectionQuery extends \yii\db\ActiveQuery
{
	/*public function active()
	{
		return $this->andWhere('[[status]]=1');
	}*/

	/**
	 * Ordered scope
	 *
	 * @return $this
	 */
	public function ordered()
	{
		return $this->orderBy(['upgrade_protection.created_at' => SORT_DESC]);
	}

	/**
	 * {@inheritdoc}
	 * @return UpgradeProtection[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return UpgradeProtection|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}

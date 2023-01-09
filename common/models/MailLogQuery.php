<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[MailLog]].
 *
 * @see MailLog
 */
class MailLogQuery extends \yii\db\ActiveQuery
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
		return $this->orderBy(['mail_log.created_at' => SORT_ASC]);
	}

	/**
	 * {@inheritdoc}
	 * @return MailLog[]|array
	 */
	public function all($db = null)
	{
		return parent::all($db);
	}

	/**
	 * {@inheritdoc}
	 * @return MailLog|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}
}

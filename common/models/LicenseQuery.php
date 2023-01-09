<?php

namespace common\models;

/**
 * This is the ActiveQuery class for [[License]].
 *
 * @see License
 */
class LicenseQuery extends \yii\db\ActiveQuery
{
	/*public function active()
	{
		return $this->andWhere('[[status]]=1');
	}*/

	/**
	 * Product Info table filter
	 * @param string|array $product_id
	 * @param integer|array $version
	 * @param integer|array $subversion
	 * @return $this
	 */
	public function filterProductInfo($product_id = null, $version = null, $subversion = null)
	{
		if ($product_id != null || $version !== null || $subversion !== null) {
			return $this->andWhere([
				'license.product_info_id' => ProductInfo::find()->select(['id'])->andFilterWhere([
					'product_id' => $product_id,
					'version' => $version,
					'subversion' => $subversion
				])->column()
			]);
		}
		return $this;
	}
	
	/**
	 * Support table filter
	 * @param string $support_id
	 * @return $this
	 */
	public function filterSupport($support_id)
	{
		if ($support_id != null) {
			return $this->andWhere([
				'license.id' => LicenseSupport::find()->select(['license_id'])->andWhere(['support_id' => $support_id])->column()
			]);
		}
		return $this;
	}
	
	/**
	 * Delivery table filter
	 * @param string $delivery_id
	 * @return $this
	 */
	public function filterDelivery($delivery_id)
	{
		if ($delivery_id != null) {
			return $this->andWhere([
				'license.id' => DeliveriesLicenses::find()->select(['license_id'])->andWhere(['delivery_id' => $delivery_id])->column()
			]);
		}
		return $this;
	}

	/**
	 * LicenseType table filter
	 * @param string|array $name
	 * @return $this
	 */
	public function whereLicenseTypeName($name = null)
	{
		return $this->andWhere([
			'license.license_type_id' => LicenseType::find()->select(['id'])->andWhere([
				'name' => $name,
			])->column()
		]);
	}
	
	/**
	 * Email table filter
	 * @param string $email
	 * @return $this
	 */
	public function filterEmail($email = null)
	{
		if ($email != null) {
			return $this->andWhere([
				'license.email_id' => Email::find()->select(['id'])->andFilterWhere(['like', 'email', $email])->column()
			]);
		}
		return $this;
	}
	
	/**
	 * ID, Email, Company, Contact filter
	 * @param string $phrase
	 * @return $this
	 */
	public function filterPhrase($phrase = null)
	{
		if ($phrase != null) {
			$this->andFilterWhere([
				'or',
				['like', 'license.id', $phrase],
				['like', 'license.owner_name', $phrase],
				['like', 'license.company', $phrase],
				['license.email_id' => Email::find()->select(['id'])->andFilterWhere(['like', 'email', $phrase])->column()]
			]);
		}
		return $this;
	}
	
	/**
	 * @param string $date
	 * @return $this
	 */
	public function filterMaxCreated($date = null)
	{
		if ($date != null) {
			return $this->andWhere(['<', 'license.created_at', $date]);
		}
		return $this;
	}
	
	/**
	 * @return $this
	 */
	public function invalidated()
	{
		return $this->andWhere(['license.id' => LicenseInvalidation::find()->select(['license_id'])->column()]);
	}

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
	 * @return License|array|null
	 */
	public function one($db = null)
	{
		return parent::one($db);
	}

	/*
	 * Valid licenses
	 * @return $this
	 */
	public function valid()
	{
		return $this->andWhere("license.is_valid = 1");
	}

	/*
	 * InValid licenses
	 * @return $this
	 */
	public function invalid()
	{
		return $this->andWhere("license.is_valid = 0");
	}

	/**
	 * Ordered scope
	 *
	 * @return $this
	 */
	public function ordered()
	{
		return $this->orderBy(['license.created_at' => SORT_ASC]);
	}

}

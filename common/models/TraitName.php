<?php

namespace common\models;

use Yii;
use common\helpers\Config;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "trait".
 *
 * @property int $id
 * @property string|null $name
 */
class TraitName extends BaseActiveRecord
{
	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'trait_name';
	}

	/**
	 * @param string $name
	 * @param bool $insert
	 * @return string
	 */
	public static function getIdByName($name, $insert = true)
	{
		$model = static::findOne(['name' => $name]);
		if ($model) {
			return $model->id;
		} elseif ($insert) {
			$model = new TraitName();
			$model->name = $name;
			if ($model->save(false)) {
				return $model->id;
			}
		}
	}

	/**
	 * {@inheritdoc}
	 * @return TraitNameQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new TraitNameQuery(get_called_class());
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['name'], 'string', 'max' => 64],
		];
	}

	/**
	 * Get Trait Description
	 * @return string
	 */
	public function getDescription()
	{
		return ArrayHelper::getValue(Config::getTrait($this->name), 'description', $this->name);
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		return [
			'id' => Yii::t('app', 'ID'),
			'name' => Yii::t('app', 'Name'),
		];
	}
}

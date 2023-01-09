<?php

namespace common\models;

use Yii;
use yii\base\Exception;
use yii\web\NotFoundHttpException;
use yii\helpers\ArrayHelper;
use common\helpers\Config;
use yii\web\ServerErrorHttpException;

/**
 * This is the model class for table "trait_value".
 *
 * @property int $id
 * @property string $license_id
 * @property int $trait_name_id
 * @property int|null $int_value
 * @property int|null $bool_value
 * @property float|null $float_value
 * @property string|null $string_value
 * @property string|null $date_value
 * @property string|null $type
 */
class TraitValue extends BaseActiveRecord
{

	/**
	 * @var string
	 */
	const TYPE_INT = 'int';

	/**
	 * @var string
	 */
	const TYPE_STRING = 'string';

	/**
	 * @var string
	 */
	const TYPE_BOOL = 'bool';

	/**
	 * @var string
	 */
	const TYPE_DATE = 'date';

	/**
	 * @var string
	 */
	const TYPE_FLOAT = 'float';

	public static $types = [self::TYPE_INT, self::TYPE_STRING, self::TYPE_BOOL, self::TYPE_DATE, self::TYPE_FLOAT];

	/**
	 * {@inheritdoc}
	 */
	public static function tableName()
	{
		return 'trait_value';
	}

	/**
	 * Returns new list of TraitValue objects based on array
	 *
	 * @param array $traits
	 * @param array|null $initTraitValues - array of initial values in format  ['trait name' => value]
	 * @return array
	 */
	public static function initModels($traits, $initTraitValues = [])
	{
		$res = [];
		if ($traits) {
			foreach ($traits as $trait) {
				$model = static::initByAttributes($trait);

				$name = ArrayHelper::getValue($trait, 'name');
				if (array_key_exists($name, $initTraitValues)) {
					try {
						$model->setAttributes([$model->valueId => $initTraitValues[$name]]);
					}
					catch (\Exception $e) {}
				}

				$res[$model->trait_name_id] = $model;
			}
		}
		return $res;
	}

	/**
	 * Returns new object based on attributes ['name' => {name}, 'value' => {value}]
	 *
	 * @param array $attributes
	 * @return TraitValue
	 */
	public static function initByAttributes($attributes)
	{
		$name = ArrayHelper::getValue($attributes, 'name');

		if (empty($name)) {
			throw new ServerErrorHttpException(Yii::t('app', 'Invalid configuration data, name not found in licenseTemplates[ traits[ [ name={name}]]]', ['name' => $name]));
		}

		$trait = Config::getTrait($name);

		if (empty($trait)) {
			throw new ServerErrorHttpException(Yii::t('app', 'Invalid configuration data, trait definition with name "{name}" not found in traits.', ['name' => $name]));
		}

		$model = new TraitValue();

		$model->type = ArrayHelper::getValue($trait, 'type');

		if (!$model->validate('type')) {
			throw new ServerErrorHttpException(Yii::t('app', 'Invalid configuration data, incorrect type in traits[ [ name={name}, type={type}]]. Allowed types: {types}', ['name' => $name, 'type' => $model->type, 'types' => implode(', ', self::$types)]));
		}

		$model->scenario = 'create-' . $model->type;

		$model->setAttributes([$model->valueId => ArrayHelper::getValue($attributes, 'value'), 'trait_name_id' => TraitName::getIdByName($name)]);

		return $model;
	}

	/**
	 * {@inheritdoc}
	 * @return TraitValueQuery the active query used by this AR class.
	 */
	public static function find()
	{
		return new TraitValueQuery(get_called_class());
	}

	/**
	 * {@inheritdoc}
	 */
	public function rules()
	{
		return [
			[['trait_name_id', 'type'], 'required'],
			[['trait_name_id', 'int_value'], 'integer'],
			[['bool_value'], 'integer', 'min' => 0, 'max' => 1],
			[['float_value'], 'number'],
			[['string_value'], 'string'],
			[['date_value'], 'safe'],
			[['license_id'], 'string', 'max' => 64],
			[['type'], 'string', 'max' => 32],
			['type', 'in', 'range' => self::$types],

			// scenario rules
			[['int_value'], 'required', 'on' => ['create-int', 'update-int']],
			[['bool_value'], 'integer', 'min' => 0, 'max' => 1, 'on' => ['create-bool', 'update-bool']],
			[['float_value'], 'required', 'on' => ['create-float', 'update-float']],
			[['string_value'], 'required', 'on' => ['create-string', 'update-string']],
			[['date_value'], 'required', 'on' => ['create-date', 'update-date']],
		];
	}

	/**
	 * Get TraitName name
	 * @return string
	 */
	public function getName()
	{
		return ArrayHelper::getValue($this, 'traitName.name');
	}

	/**
	 * Get Trait description
	 * @return string
	 */
	public function getDescription()
	{
		return ArrayHelper::getValue($this, 'traitName.description');
	}

	/**
	 * @return string
	 */
	public function getValue()
	{
		return $this->getAttribute($this->getValueId());
	}

	/**
	 * @return string
	 */
	public function getValueId()
	{
		return $this->type . '_value';
	}

	/**
	 * @param mixed $value
	 * @return void
	 */
	public function setValue($value)
	{
		return $this->setAttributes([$this->valueId => $value]);
	}

	/**
	 * @return string
	 */
	public function getDisplayValue()
	{
		switch ($this->type) {
			case self::TYPE_BOOL :
				return $this->value ? 'True' : 'False';
				break;
			default :
				return $this->value;
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function attributeLabels()
	{
		$name = $this->description;
		return [
			'id' => Yii::t('app', 'ID'),
			'license_id' => Yii::t('app', 'License'),
			'trait_name_id' => Yii::t('app', 'Trait'),
			'int_value' => $name,
			'bool_value' => $name,
			'float_value' => $name,
			'string_value' => $name,
			'date_value' => $name,
			'type' => Yii::t('app', 'Type'),
		];
	}

	/**
	 * Get TraitName
	 * @return \yii\db\ActiveQuery
	 */
	public function getTraitName()
	{
		return $this->hasOne(TraitName::className(), ['id' => 'trait_name_id']);
	}

	/**
	 * @inheritdoc
	 */
	public function beforeDelete()
	{
		$this->logDelete($this->description, $this->license_id);
		return parent::beforeDelete();
	}

	/**
	 * @inheritdoc
	 */
	public function afterSave($insert, $changedAttributes)
	{
		$this->logSave($this->description, $insert, $changedAttributes, $this->license_id);
		parent::afterSave($insert, $changedAttributes);
	}
}

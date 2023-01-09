<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\ServerErrorHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\db\Expression;

use common\helpers\Config;
use common\helpers\Generator;
use common\helpers\Html;
use common\helpers\ArrayHelper;

use common\models\License;
use common\models\PendingLicense;
use common\models\PendingLicenseUpdate;
use common\models\ProductInfo;
use common\models\Email;
use common\models\TraitValue;
use common\models\LicenseType;
use common\models\Delivery;
use common\models\UpgradeProtection;

use backend\models\LicenseSearch;
use backend\models\AddLicenseForm;

/**
 * LicenseController implements the CRUD actions for License model.
 */
class LicenseController extends BaseController
{
	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::className(),
				'rules' => [
					[
						'actions' => ['index', 'view', 'index-view', 'items', 'load-same-attributes', 'info', 'typeahead'],
						'allow' => true,
						'roles' => ['license'],
					],
					[
						'actions' => ['create', 'add'],
						'allow' => true,
						'roles' => ['license-create'],
					],
					[
						'actions' => ['update', 'bulk-update'],
						'allow' => true,
						'roles' => ['license-update'],
					],
					[
						'actions' => ['bulk-delivery'],
						'allow' => true,
						'roles' => ['delivery-create'],
					],
				],
			],
		];
	}

	/**
	 * Lists all License models.
	 * @param string $advanced
	 * @return mixed
	 */
	public function actionIndex($advanced=0)
	{
		$searchModel = new LicenseSearch();

		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['created_at' => SORT_DESC]);

		Url::remember(['index'], 'license_form');

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
			'advanced' => $advanced,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
	}

	/**
	 * Displays a single License model.
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionIndexView($buttons=false)
	{
		if (isset($_POST['expandRowKey'])) {
			$model = $this->findModel($_POST['expandRowKey']);
			return $buttons ? $this->renderPartial('index-view', ['model' => $model]) : $this->renderPartial('_index-view', ['model' => $model, 'options' => []]);
		} else {
			return Html::noData();
		}
	}

	/**
	 * Finds the License model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return License the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = License::findOne($id)) !== null) {
			return $model;
		}
		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}

	/**
	 * Displays a single License model.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		$model = $this->findModel($id);
		Url::remember(['view', 'id' => $id], 'license_form');
		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * Displays a single License model.
	 * @param string $id
	 * @return mixed
	 */
	public function actionInfo($id, $pid = null, $date = null, $default='')
	{
		if ($model = License::find()->andWhere(['id' => $id])->filterProductInfo($pid)->filterMaxCreated($date)->one()) {
			return $this->renderPartial('_info', ['model' => $model]);
		}
		else{
			return $default;
		}
	}

	/**
	 * Creates a new License model.
	 * If creation is successful, the browser will be redirected to the 'create' page.
	 * @return mixed
	 */
	public function actionAdd($lt = null)
	{
		$model = new AddLicenseForm();

		if ($model->load(Yii::$app->request->post())) {
			if ($model->validate()) {

				Url::remember(['add', 'lt' => $model->license_template_id], 'license_form');

				return $this->redirect(['create', 'pid' => $model->product_id, 'ver' => $model->version, 'lt' => $model->license_template_id]);
			}
		} else {
			$model->license_template_id = ($lt && array_key_exists($lt, $model->getLicenseTemplateItems())) ? $lt : $model->getFirstLicenseTemplateId();
			if ($version_ids = Config::getVersionIds($model->product_id)){
				$model->version = max($version_ids);
			}
			$model->validate(['product_id']);
			//$model->validate();
		}

		return $this->render('add', [
			'model' => $model,
		]);
	}

	/**
	 * Creates a new License model.
	 * If creation is successful, the browser will be redirected to the 'index' page.
	 * @param string $pid product id
	 * @param string $ver version
	 * @param string $lt license template id
	 * @param string|null $pending_id pending license id
	 * @param string|null $pending_license_update_ids pending license update ids (comma separated list of ids in case of group update ldap licenses)
	 * @return mixed
	 */
	public function actionCreate($pid, $ver, $lt, $pending_license_id = null, $pending_license_update_ids = null)
	{
		$modelPending = null;
		$initTraitValues = [];
		$parent_license_ids = [];

		if ($pending_license_id) {
			$modelPending = PendingLicense::findOne($pending_license_id);
			if (!$modelPending) {
				throw new NotFoundHttpException(Yii::t('app', 'Invalid pending license'));
			}
			else {
				$pid = $modelPending->getProductId();
				$ver = $modelPending->getProductVersion();
				$lt =  $modelPending->getLicenseTypeName();
				if ($modelPending->allowed_dns_domains) { // possible oml ldap license property
					$initTraitValues['allowed.dns.domains'] = $modelPending->allowed_dns_domains;
				}
				if ($modelPending->user_number) { // adaxes license property
					$initTraitValues['user.number'] = $modelPending->user_number;
				}
			}
		}
		else {
			if ($pending_license_update_ids) {
				$pending_license_update_ids = explode(',', $pending_license_update_ids);
				// in case of group update ldap licenses they are always have the same properties, so we check the first license
				$modelPending = PendingLicenseUpdate::findOne($pending_license_update_ids[0]);
				if (!$modelPending) {
					throw new NotFoundHttpException(Yii::t('app', 'Invalid pending update license'));
				} else {
					$pid = $modelPending->getProductId();
					$ver = $modelPending->getProductVersion();
					$lt = $modelPending->getLicenseTypeName();

					if ($modelPending->user_number) { // adaxes license property
						$initTraitValues['user.number'] = $modelPending->user_number;
					}
					$parent_license_ids = PendingLicenseUpdate::find()->select(['license_id'])->where(['id' => $pending_license_update_ids])->column();
				}
			}
		}

		$license_template = Config::getLicenseTemplate($pid, $ver, $lt);

		if (empty($license_template)) {
			throw new NotFoundHttpException(Yii::t('app', 'Invalid license template: {license_template}', ['license_template' => $lt]));
		}

		$route = ($url = Url::previous('license_form')) ? $url : ['add', 'lt' => $lt];

		$model = new License();
		$model->scenario = 'create';

		//echo $pid; echo $ver; echo $lt; die;
		$model->product_id = $pid;
		$model->version = $ver;
		$model->license_type_name = $lt;

		$traitValues = TraitValue::initModels(ArrayHelper::getValue($license_template, 'traits'), $initTraitValues);

		$deliveries = Delivery::initLicenseModels(['license_type_name' => $model->license_type_name, 'product_id' => $model->product_id,
			'email_address' => ($modelPending) ? $modelPending->delivery_email : '']);

		$licenseUpgrades = $model->initLicenseUpgrades($parent_license_ids);

		if ($model->load(Yii::$app->request->post())) {
			Model::loadMultiple($traitValues, Yii::$app->request->post());
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			Model::loadMultiple($licenseUpgrades, Yii::$app->request->post());

			$model->license_type_id = LicenseType::getIdByName($model->license_type_name);
			$model->files = UploadedFile::getInstances($model, 'files');
			$model->flags = ArrayHelper::getValue($license_template, 'flags');
			$model->id = Generator::licenseId($model->license_id_prefix);
			Delivery::$licenses_count = $model->quantity;
			$validate = $model->validate();
			$validate = ($traitValues) ? Model::validateMultiple($traitValues) && $validate : $validate;
			foreach ($licenseUpgrades as $index => $licenseUpgrade) {
				$validate = (($index < $model->license_upgrade_count) && !$licenseUpgrade->isDisabled()) ? $licenseUpgrade->validate() && $validate : $validate;
			}
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$model->email_id = Email::getIdByAddress($model->email_address);
				$model->product_info_id = ProductInfo::getIdBySubversion($model->product_id, $model->version, $model->subversion);

				// store payment id
				if ($modelPending) {
					$model->payment_id = $modelPending->payment_id;
				}

				$attachment_ids = $model->addAttachments();
				$support_id = $model->addSupport();

				$tries = 0;
				$license_ids = [];
				$upgrade_protection_ids = [];
				$parent_license_ids = array_filter(ArrayHelper::getColumn($licenseUpgrades, 'parentLicenseId'));

				while (count($license_ids) < $model->quantity && $tries < 50) {
					$model->id = Generator::licenseId($model->license_id_prefix);
					$model->isNewRecord = true;
					if ($model->save()) {
						foreach ($traitValues as $traitValue) {
							$traitValue->license_id = $model->id;
							$traitValue->save(false);
						}
						if ($parent_license_ids){
							$model_parent_license_ids = (count($parent_license_ids) == $model->quantity) ? ArrayHelper::getValue($parent_license_ids, count($license_ids)) : $parent_license_ids;
							$model->addLicenseUpgrades($model_parent_license_ids);
						}
						if ($upgrade_protection_id = $model->addUpgradeProtection()) {
							$upgrade_protection_ids[] = $upgrade_protection_id;
						}
						if ($support_id) {
							$model->addLicenseSupport($support_id);
						}
						if ($attachment_ids) {
							$model->updateAttachmentsLicenses();
						}
						$license_ids[] = $model->id;
					}
					$tries++;
				}
				if ($count = count($license_ids)) {

					foreach ($deliveries as $index => $delivery) {
						if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
							if ($delivery->add($license_ids, $upgrade_protection_ids, $support_id)) {
								$delivery->sendEmail();
							}
						}
					}

					// auto remove pending licenses
					if ($modelPending) {
						if ($pending_license_id) {
							$modelPending->delete();
						}
						else {
							if ($pending_license_update_ids) {
								foreach ($pending_license_update_ids as $pending_license_update_id) {
									$pendingLicenseUpdateModel = PendingLicenseUpdate::findOne(['id' => $pending_license_update_id]);
									if ($pendingLicenseUpdateModel) {
										$pendingLicenseUpdateModel->delete();
									}
								}
							}
						}
					}

					$this->successAlert = ($count > 1) ?
						Yii::t('app', '{count} Licenses for {owner_name} has been successfully generated.', ['count' => $count, 'owner_name' => $model->owner_name])
						: Yii::t('app', 'License for {owner_name} has been successfully generated.', ['owner_name' => $model->owner_name]);
					return $this->redirect(['index']);
				}

			}
		} else {
			$model->support_end_date = date('Y-m-d', strtotime(" +1 year"));
			$model->max_count_request = ArrayHelper::getValue($license_template, 'defaultSupportRequests', 0);
			$model->not_after_date = $model->date_limit_period = date('Y-m-d', strtotime(" +30 days")); // 30 days
			$model->dateLimit = ArrayHelper::getValue($license_template, 'dateLimit.corePeriod');
			if ($model->date_limit && $model->not_after_date){
				$model->upgrade_protection_end_date = $model->not_after_date;
				$model->ams_period = ''; // custom
			}
			else{
				$model->upgrade_protection_end_date = $model->ams_period = date('Y-m-d', strtotime(" +1 year")); // 1 year
			}
			// Get prefix and message from the template
			$model->license_id_prefix = ArrayHelper::getValue($license_template, 'license_id_prefix', '');
			$details_template = ArrayHelper::getValue($license_template, 'details', '');
			$details_template = str_replace('{end_date}', date('m/d/Y', strtotime($model->not_after_date)), $details_template);
			$model->details = $details_template;
			$model->ams = ArrayHelper::getValue($license_template, 'ams', false);
			if (!$modelPending) {
				$model->subversion = $this->getLastSubversion($model->product_id, $model->version);
				$model->product_info_id = ProductInfo::getIdBySubversion($model->product_id, $model->version, $model->subversion);
			}
			else {
				$model->product_info_id = $modelPending->product_info_id;
				$model->subversion = $modelPending->getProductSubversion();
				$model->email_address = $modelPending->email;
				$model->owner_name = $modelPending->owner_name;
				$model->company = $modelPending->company;
				$model->quantity = ($pending_license_update_ids) ? count($pending_license_update_ids) : $modelPending->quantity;
				$model->include_support = $modelPending->enable_support;
				if ($modelPending->enable_upgrade_protection) {
					$model->ams = 1;
				}
			}
		}

		return $this->render('create', [
			'model' => $model,
			'trait_values' => [$model->license_type_name => $traitValues],
			'deliveries' => $deliveries,
			'licenseUpgrades' => $licenseUpgrades,
			'route' => $route,
		]);
	}

	/**
	 *
	 * @return int
	 */
	public function getLastSubversion($product_id, $version)
	{
		return count($subversion_ids = Config::getSubversionIds($product_id, $version)) ? end($subversion_ids) : null;
	}

	/**
	 * Updates an existing License model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);
		$model->scenario = 'update';
		$model->product_id = $model->productId;
		$model->version = $model->productVersion;
		$model->license_type_name = $model->licenseTypeName;

		$route = ($url = Url::previous('license_form')) ? $url : ['view', 'id' => $model->id];

		$license_templates = Config::getLicenseTemplates($model->product_id, $model->version);
		$trait_values = [];
		foreach ($license_templates as $license_template_id => $license_template) {
			if ($model->license_type_name == $license_template_id) {
				$traitValues = $model->getTraitValues()->with('traitName')->indexBy('trait_name_id')->all();
				foreach ($traitValues as $id => $traitValue) {
					$traitValue->scenario = 'update-' . $traitValue->type;
				}
			} else {
				$traitValues = TraitValue::initModels(ArrayHelper::getValue($license_template, 'traits'));
			}
			$trait_values[$license_template_id] = $traitValues;
		}
		$deliveries = Delivery::initLicenseModels(['license_type_name' => $model->license_type_name, 'product_id' => $model->product_id]);

		$licenseUpgrades = $model->initLicenseUpgrades($model->getLicenseUpgrades()->select('parent_license_id')->ordered()->column());

		$license_type_name = $model->license_type_name;

		if ($model->load(Yii::$app->request->post())) {
			$traitValues = ArrayHelper::getValue($trait_values, $model->license_type_name, []);
			Model::loadMultiple($traitValues, Yii::$app->request->post());
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			Model::loadMultiple($licenseUpgrades, Yii::$app->request->post());

			if ($license_type_changed = ($model->license_type_name != $license_type_name)) {
				$model->license_type_id = LicenseType::getIdByName($model->license_type_name);
			}

			$model->files = UploadedFile::getInstances($model, 'files');
			$validate = $model->validate();
			$validate = ($traitValues) ? Model::validateMultiple($traitValues) && $validate : $validate;
			foreach ($licenseUpgrades as $index => $licenseUpgrade) {
				$validate = (($index < $model->license_upgrade_count) && !$licenseUpgrade->isDisabled()) ? $licenseUpgrade->validate() && $validate : $validate;
			}
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				if ($model->email_address != $model->emailAddress) {
					$model->email_id = Email::getIdByAddress($model->email_address);
				}
				if ($model->save(false)) {
					if ($license_type_changed) {
						foreach ($model->traitValues as $traitValue) {
							$traitValue->delete();
						}
						foreach ($traitValues as $traitValue) {
							$traitValue->license_id = $model->id;
							$traitValue->save(false);
						}
					} else {
						foreach ($traitValues as $traitValue) {
							$traitValue->save(false);
						}
					}
					$parent_license_ids = array_filter(ArrayHelper::getColumn($licenseUpgrades, 'parentLicenseId'));
					$model->updateLicenseUpgrades($parent_license_ids);

					$model->addAttachments();
					$model->updateAttachmentsLicenses();

					foreach ($deliveries as $index => $delivery) {
						if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
							if ($delivery->add($model->id, $model->upgradeProtectionId)) {
								$delivery->sendEmail();
							}
						}
					}

					$this->successAlert = Yii::t('app', 'License: {id} has been successfully updated.', ['id' => $model->id]);
					return $this->redirect($route);
				}
			}
		} else {
			$model->email_address = $model->emailAddress;
			$model->parent_license_ids = ArrayHelper::getColumn($model->licenseUpgrades, 'parent_license_id');
			$model->attachment_ids = ArrayHelper::getColumn($model->attachmentsLicenses, 'attachment_id');
		}

		return $this->render('update', [
			'model' => $model,
			'trait_values' => $trait_values,
			'deliveries' => $deliveries,
			'licenseUpgrades' => $licenseUpgrades,
			'route' => $route,
		]);
	}

	/**
	 * Updates an existing License models.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param array $ids
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionBulkUpdate(array $ids)
	{
		$model = new License();
		$model->scenario = 'bulk-update';
		$model->isNewRecord = false;

		$route = ($url = Url::previous('license_form')) ? $url : ['index'];

		if ($licenses = License::find()->with(['licenseType', 'licenseInvalidations', 'productInfo', 'email', 'user'])->andWhere(['id' => $ids])->indexBy('id')->all()) {

			$license_ids = array_keys($licenses);

			$model->license_type_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'licenseTypeName'));
			if (empty($model->license_type_name)) {
				$this->warningAlert = Yii::t('app', 'Updated Licenses must have the same Type.');
				return $this->redirect($route);
			}
			$model->product_id = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productId'));
			if (empty($model->product_id)) {
				$this->warningAlert = Yii::t('app', 'Updated Licenses must have the same Product.');
				return $this->redirect($route);
			}
			$model->version = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productVersion'), false);
			if ($model->version === false) {
				$this->warningAlert = Yii::t('app', 'Updated Licenses must have the same Version.');
				return $this->redirect($route);
			}

			$model->created_at = max(ArrayHelper::getColumn($licenses, 'created_at'));

			$licenseUpgrades = (count($licenses) == 1) ? $model->initLicenseUpgrades(reset($licenses)->getLicenseUpgrades()->select('parent_license_id')->ordered()->column()) : [];

			$license_templates = Config::getLicenseTemplates($model->product_id, $model->version);
			$trait_values = [];
			foreach ($license_templates as $license_template_id => $license_template) {
				$traitValues = TraitValue::initModels(ArrayHelper::getValue($license_template, 'traits'));
				if ($model->license_type_name == $license_template_id) {
					foreach ($traitValues as $trait_name_id => $traitValue) {
						$licenseTraitValues = TraitValue::find()->select($traitValue->valueId)->andWhere(['license_id' => $license_ids, 'trait_name_id' => $trait_name_id])->column();
						$traitValue->value = ArrayHelper::sameValue($licenseTraitValues);
						$traitValue->scenario = 'update-' . $traitValue->type;
					}
				}
				$trait_values[$license_template_id] = $traitValues;
			}
		} else {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		$deliveries = Delivery::initLicenseModels(['license_type_name' => $model->license_type_name, 'product_id' => $model->product_id]);

		$license_type_name = $model->license_type_name;

		if ($model->load(Yii::$app->request->post())) {
			$traitValues = ArrayHelper::getValue($trait_values, $model->license_type_name, []);
			Model::loadMultiple($traitValues, Yii::$app->request->post());
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			Model::loadMultiple($licenseUpgrades, Yii::$app->request->post());
			$model->files = UploadedFile::getInstances($model, 'files');
			$validate = $model->validate();
			$validate = ($traitValues) ? Model::validateMultiple($traitValues) && $validate : $validate;
			foreach ($licenseUpgrades as $index => $licenseUpgrade) {
				$validate = (($index < $model->license_upgrade_count) && !$licenseUpgrade->isDisabled()) ? $licenseUpgrade->validate() && $validate : $validate;
			}
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$model->email_id = Email::getIdByAddress($model->email_address);
				$model->license_type_id = LicenseType::getIdByName($model->license_type_name);

				$attachment_ids = $model->addAttachments();
				$count = 0;
				foreach ($licenses as $license) {
					$license->setAttributes($model->getAttributes(['email_id', 'owner_name', 'company', 'license_type_id']));
					if ($license->save(false)) {
						$count++;
					}
					if ($attachment_ids) {
						$license->updateAttachmentsLicenses();
					}
				}
				if ($count == 1) {
					$parent_license_ids = array_filter(ArrayHelper::getColumn($licenseUpgrades, 'parentLicenseId'));
					reset($licenses)->updateLicenseUpgrades($parent_license_ids);
				}
				foreach ($traitValues as $trait_name_id => $traitValue) {
					if ($model->license_type_name != $license_type_name) {
						foreach ($license_ids as $license_id) {
							$traitValue->isNewRecord = true;
							$traitValue->license_id = $license_id;
							$traitValue->save(false);
						}
					} else {
						$licenseTraitValues = TraitValue::find()->andWhere(['license_id' => $license_ids, 'trait_name_id' => $trait_name_id])->all();
						foreach ($licenseTraitValues as $licenseTraitValue) {
							$licenseTraitValue->value = $traitValue->value;
							$licenseTraitValue->save(false);
						}
					}
				}
				if ($count) {
					$upgrade_protection_ids = array_filter(ArrayHelper::getColumn($licenses, 'upgradeProtectionId'));

					foreach ($deliveries as $index => $delivery) {
						if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
							if ($delivery->add($license_ids, $upgrade_protection_ids)) {
								$delivery->sendEmail();
							}
						}
					}

					$this->successAlert = ($count > 1) ? Yii::t('app', '{count} Licenses have been successfully updated.', ['count' => $count])
						: Yii::t('app', 'License has been successfully updated.');
				}
				return $this->redirect($route);
			}
		} else {
			$model->email_address = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'emailAddress'));
			$model->owner_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'owner_name'));
			$model->company = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'company'));
		}

		// set license id with min created date for parent search
		$min_date = $model->nowDb;

		foreach ($licenses as $license) {
			if ($license->created_at < $min_date) {
				$min_date = $license->created_at;
				$model->id = $license->id;
			}
		}

		return $this->render('bulk-update', [
			'model' => $model,
			'trait_values' => $trait_values,
			'deliveries' => $deliveries,
			'licenseUpgrades' => $licenseUpgrades,
			'route' => $route,
			'licenses' => $licenses,
		]);
	}

	/**
	 * Add delivery to existing License models.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param array $ids
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionBulkDelivery(array $ids)
	{
		$model = new License();
		$model->scenario = 'bulk-delivery';
		$model->isNewRecord = false;

		$route = ($url = Url::previous('license_form')) ? $url : ['index'];

		if ($licenses = License::find()->with(['licenseType', 'licenseInvalidations', 'productInfo', 'email', 'user'])->andWhere(['id' => $ids])->indexBy('id')->all()) {

			$license_ids = array_keys($licenses);
			
			$model->product_id = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productId'));
			if (empty($model->product_id)) {
				$this->warningAlert = Yii::t('app', 'Delivered Licenses must have the same Product.');
				return $this->redirect($route);
			}

			$model->license_type_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'licenseTypeName'));

			$model->version = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productVersion'));

			$model->email_address = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'emailAddress'));
		} else {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		$deliveries = Delivery::initLicenseModels(['license_type_name' => $model->license_type_name, 'product_id' => $model->product_id]);

		if ($model->load(Yii::$app->request->post())) {
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			$validate = $model->validate();
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$count = 0;
				$upgrade_protection_ids = array_filter(ArrayHelper::getColumn($licenses, 'upgradeProtectionId'));
				
				foreach ($deliveries as $index => $delivery) {
					if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
						if ($delivery->add($license_ids, $upgrade_protection_ids)) {
							$delivery->sendEmail();
							$count++;
						}
					}
				}
				if ($count) {
					$this->successAlert = ($count > 1) ? Yii::t('app', '{count} Deliveries have been successfully added.', ['count' => $count])
						: Yii::t('app', 'Delivery has been successfully added.');
				}
				return $this->redirect($route);
			}
		}

		return $this->render('bulk-delivery', [
			'model' => $model,
			'deliveries' => $deliveries,
			'route' => $route,
			'licenses' => $licenses,
		]);
	}

	/**
	 *
	 * @return mixed
	 */
	public function actionItems($q = null, $id = null, $pid = null, $limit = 100)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$out = ['results' => ['id' => '', 'text' => '']];
		if ($id) {
			$model = $this->findModel($id);
			$pid = ArrayHelper::getValue($model, 'productInfo.product_id');
		}

		if (!is_null($q)) {
			$query = License::find()->with('email', 'productInfo', 'licenseType')->filterProductInfo($pid)->filterPhrase($q);
			if ($id) {
				$query->andWhere(['<', 'created_at', $model->created_at]);
				//$query->andWhere(['<', 'created_at', new Expression("STR_TO_DATE('" . $model->created_at . "', '%Y-%m-%d %H:%i:%s')")]);
			}
			$licenses = $query->limit($limit)->all();
			$results = ArrayHelper::getColumn($licenses, 'viewAttributes');
			$out['results'] = $results;
		}

		return $out;
	}

	/**
	 *
	 * @return mixed
	 */
	public function actionTypeahead($q = null, $date = null, $pid = null, $limit = 20)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		if (!is_null($q) && strlen($q) >=3) {
			return License::find()->select('id as value')->filterProductInfo($pid)->filterMaxCreated($date)->filterPhrase($q)->limit($limit)->asArray()->all();
		}
		return [];
	}

	/**
	 *
	 * @return mixed
	 */
	public function actionLoadSameAttributes(array $ids = null)
	{
		\Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		$out = [];
		if ($ids && ($licenses = License::find()->with(['licenseType', 'productInfo', 'email', 'user'])->andWhere(['id' => $ids])->indexBy('id')->all())) {

			$license_ids = array_keys($licenses);
			$model = reset($licenses);

			$license_type_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'licenseTypeName'));

			if ($email_address = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'emailAddress'))){
				$out[Html::getInputId($model, 'email_address')] = $email_address;
			}
			if ($owner_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'owner_name'))){
				$out[Html::getInputId($model, 'owner_name')] = $owner_name;
			}
			if ($company = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'company'))){
				$out[Html::getInputId($model, 'company')] = $company;
			}

			$product_id = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productId'));
			$version = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'productVersion'), false);
			if ($product_id && $version ){
				$license_templates = Config::getLicenseTemplates($product_id, $version);
				$trait_values = [];
				foreach ($license_templates as $license_template_id => $license_template) {
					$traitValues = TraitValue::initModels(ArrayHelper::getValue($license_template, 'traits'));
					if ($license_type_name == $license_template_id) {
						foreach ($traitValues as $trait_name_id => $traitValue) {
							$licenseTraitValues = TraitValue::find()->select($traitValue->valueId)->andWhere(['license_id' => $license_ids, 'trait_name_id' => $trait_name_id])->column();
							if ($value = ArrayHelper::sameValue($licenseTraitValues)){
								$out[Html::getInputId($traitValue, '[' . $traitValue->trait_name_id . ']' . $traitValue->valueId)] = $value;
							}
						}
					}
				}
			}
		}

		return $out;
	}

	/**
	 *
	 * @return array
	 */
	public function getQuantityItems($count = 30)
	{
		$items = [];

		for ($i = 1; $i <= $count; $i++) {
			$items[$i] = $i;
		}

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getUpgradeProtectionDaysItems($count = 5)
	{
		$items = [
			0 => Yii::t('app', 'No'),
			365 => Yii::t('app', '1 year'),
		];

		for ($i = 2; $i <= $count; $i++) {
			$items[$i * 365] = Yii::t('app', '{num} years', ['num' => $i]);
		}

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getAMSPeriodItems($count = 5)
	{
		$items = [
			date('Y-m-d', strtotime(" +1 year")) => Yii::t('app', '1 year'),
		];
		for ($i = 2; $i <= $count; $i++) {
			$items[date('Y-m-d', strtotime(" +" . $i . " years"))] = Yii::t('app', '{num} years', ['num' => $i]);
		}
		$items[''] = Yii::t('app', 'Custom');

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getDateLimitPeriodItems()
	{
		$items = [
			date('Y-m-d', strtotime(" +7 days")) => Yii::t('app', '7 days'),
			date('Y-m-d', strtotime(" +14 days")) => Yii::t('app', '14 days'),
			date('Y-m-d', strtotime(" +30 days")) => Yii::t('app', '30 days'),
		];

		$items[''] = Yii::t('app', 'Custom');

		return $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getProductInfoItems($product_id, $version)
	{
		$items = ProductInfo::find()->with('product')->andWhere(['product_id' => $product_id, 'version' => $version])->orderBy(['subversion' => SORT_DESC])->all();
		return ArrayHelper::map($items, 'id', 'name');
	}

	/**
	 *
	 * @return array
	 */
	public function getSubversionItems($product_id, $version, $reverse = true)
	{
		$subversions = Config::getSubversions($product_id, $version);
		$items = [];
		foreach($subversions as $id => $subversion){
			$items[$id] = ArrayHelper::getValue($subversion, 'name') . ' (' . $version . '.' . $id . ')';
		}
		return $reverse ? array_reverse($items, true) : $items;
	}

	/**
	 *
	 * @return array
	 */
	public function getLicenseTypeItems($names)
	{
		return LicenseType::find()->select('name')->andWhere(['name' => $names])->orderBy(['id' => SORT_ASC])->indexBy('id')->column();
	}
}

<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\base\Model;

use common\models\PendingLicenseUpgradeProtection;
use common\models\UpgradeProtection;
use common\models\ProductInfo;
use common\models\Email;
use common\models\License;
use common\models\Delivery;

use common\helpers\Generator;
use common\helpers\Config;
use common\helpers\ArrayHelper;

use backend\models\UpgradeProtectionSearch;

/**
 * UpgradeProtectionController implements the CRUD actions for UpgradeProtection model.
 */
class UpgradeProtectionController extends BaseController
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
						'actions' => ['index', 'index-view', 'view', 'email'],
						'allow' => true,
						'roles' => ['upgrade-protection'],
					],
					[
						'actions' => ['create', 'bulk-create'],
						'allow' => true,
						'roles' => ['upgrade-protection-create'],
					],
					[
						'actions' => ['delivery', 'bulk-delivery'],
						'allow' => true,
						'roles' => ['delivery-create'],
					],
				],
			],
		];
	}

	/**
	 * Lists all UpgradeProtection models for license.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the license model cannot be found
	 */
	public function actionIndex($id)
	{
		$searchModel = new UpgradeProtectionSearch();
		$searchModel->license_id = $id;

		if ($searchModel->license == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		Url::remember(['index', 'id' => $id], 'license_form');

		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['buy_date' => SORT_DESC]);

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
	}

	/**
	 * Displays a single UpgradeProtection model.
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionIndexView()
	{
		if (isset($_POST['expandRowKey'])) {
			$model = $this->findModel($_POST['expandRowKey']);
			return $this->renderPartial('_view', ['model' => $model, 'options' => []]);
		} else {
			return Html::noData();
		}
	}

	/**
	 * Finds the UpgradeProtection model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param string $id
	 * @return UpgradeProtection the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = UpgradeProtection::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}

	/**
	 * Lists all UpgradeProtection models for email.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the email model cannot be found
	 */
	public function actionEmail($id)
	{
		$searchModel = new UpgradeProtectionSearch();
		$searchModel->email_id = $id;

		if ($searchModel->email == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		Url::remember(['email', 'id' => $id], 'license_form');

		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['buy_date' => SORT_DESC]);
		$dataProvider->query->with(['license' => function (\yii\db\ActiveQuery $query) {
			$query->with(['licenseInvalidations' => function (\yii\db\ActiveQuery $query) {
				$query->select(['id', 'license_id'])->asArray();
			}]);
		}]);

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('email', $params) : $this->render('email', $params);
	}

	/**
	 * Displays a single UpgradeProtection model.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		$model = $this->findModel($id);
		if ($model->license == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		Url::remember(['view', 'id' => $id], 'license_form');

		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * Creates a new UpgradeProtection model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @param string $id license id
	 * @param integer $pending_upgrade_protection_id
	 * @return mixed
	 */
	public function actionCreate($id, $pending_upgrade_protection_id = null)
	{
		$model = new UpgradeProtection();
		$model->scenario = 'create';

		$model->license_id = $id;

		if ($model->license == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		$model->product_info_id = $model->license->product_info_id;

		$modelPending = null;
		if ($pending_upgrade_protection_id) {
			$modelPending = PendingLicenseUpgradeProtection::findOne($pending_upgrade_protection_id);
			if (!$modelPending) {
				throw new NotFoundHttpException(Yii::t('app', 'Invalid pending Annual Maintenance and Support license'));
			}
		}

		$route = ($url = Url::previous('license_form')) ? $url : ['/license/view', 'id' => $model->license_id];

		$deliveries = Delivery::initUpgradeProtectionModels(['license_type_name' => $model->license->licenseTypeName, 'product_id' => $model->license->productId,
			'email_address' => ($modelPending) ? $modelPending->delivery_email : '']);

		if ($model->load(Yii::$app->request->post())) {
			$model->id = Generator::upgradeProtectionId();
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			$validate = $model->validate();
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$model->email_id = Email::getIdByAddress($model->email_address);

				// store payment id
				if ($modelPending) {
					$model->payment_id = $modelPending->payment_id;
				}
				if ($model->buy_date > $model->end_date){
					$model->buy_date = $model->curdateDb;
				}

				if ($model->save(false)) {
					foreach ($deliveries as $index => $delivery) {
						if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
							if ($delivery->add($model->license_id, $model->id)) {
								$delivery->sendEmail();
							}
						}
					}

					if ($modelPending) {
						$modelPending->delete();
					}

					$this->successAlert = Yii::t('app', 'New Annual Maintenance and Support has been successfully added to License: {id}.', ['id' => $model->license->id]);
					return $this->redirect($route);
				}
			}
		} 
		else {
			if (!$modelPending) {
				$model->email_address = $model->license->emailAddress;
				$model->owner_name = $model->license->owner_name;
				$model->company = $model->license->company;
			}
			else {
				$model->email_address = $modelPending->email;
				$model->owner_name = $modelPending->owner_name;
				$model->company = $modelPending->company;
			}
			$end_date = $model->license->getUpgradeProtectionEndDate();
			if ($end_date && ($end_date >= $model->curdateDb)){
				$model->buy_date = date('Y-m-d', strtotime($end_date . " + 1 day"));
				$model->end_date = date('Y-m-d', strtotime($end_date . " + 1 year"));
			}
			else{
				$model->buy_date = $model->curdateDb;
				$model->end_date = date('Y-m-d', strtotime($model->buy_date . " + 1 year"));
			}
			$model->period = $model->end_date;
			
			if ($end_date = $model->license->not_after_date){
				if ($end_date < $model->end_date){
					if ($model->buy_date <= $end_date){
						$model->end_date = date('Y-m-d', strtotime($end_date));
					}
					else{
						$model->end_date = $model->buy_date;
					}
					$model->period = '';
				}
			}
		}

		return $this->render('create', [
			'model' => $model,
			'deliveries' => $deliveries,
			'route' => $route,
		]);
	}

	/**
	 * Creates a new UpgradeProtection model.
	 * If creation is successful, the browser will be redirected to the 'view' page.
	 * @param array $ids license ids
	 * @param string $pid product id
	 * @param string $ver version
	 * @param string|null $pending_license_upgrade_protection_ids pending license Annual Maintenance and Support ids (comma separated list of ids in case of group update ldap licenses)
	 * @return mixed
	 */
	public function actionBulkCreate(array $ids, $pid, $ver = null, $pending_license_upgrade_protection_ids = null)
	{
		$model = new UpgradeProtection();
		$model->scenario = 'bulk-create';
		$model->product_id = $pid;
		$model->version = $ver;

		$route = ($url = Url::previous('license_form')) ? $url : ['/license'];

		if ($licenses = License::find()->with(['licenseType', 'licenseInvalidations', 'productInfo', 'email', 'user'])->andWhere(['id' => $ids])->indexBy('id')->all()){
			foreach ($licenses as $license) {
				$license->scenario = 'up-bulk-create';
			}
			$model->license_ids = array_keys($licenses);
			$model->product_info_id = max(ArrayHelper::getColumn($licenses, 'product_info_id'));

			$modelPending = null;
			if ($pending_license_upgrade_protection_ids) {
				$pending_license_upgrade_protection_ids = explode(',', $pending_license_upgrade_protection_ids);
				// in case of group update ldap licenses they are always have the same properties, so we check the first license
				$modelPending = PendingLicenseUpgradeProtection::findOne($pending_license_upgrade_protection_ids[0]);
				if (!$modelPending) {
					throw new NotFoundHttpException(Yii::t('app', 'Invalid pending Annual Maintenance and Support license'));
				}
			}

			$deliveries = Delivery::initUpgradeProtectionModels(['product_id' => $model->product_id,
				'email_address' => ($modelPending) ? $modelPending->delivery_email : '']);
		} 
		else {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}


		if ($model->load(Yii::$app->request->post())) {
			$model->id = Generator::upgradeProtectionId();
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			Model::loadMultiple($licenses, Yii::$app->request->post());
			$validate = $model->validate();
			foreach ($licenses as $license) {
				$license->ignore_ams_date_validation = $model->period ? 0 : $model->ignore_ams_date_validation;
				$validate = $license->validate() && $validate;
			}
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$model->email_id = Email::getIdByAddress($model->email_address);
				$count = 0;
				$upgrade_protection_ids = [];
				foreach ($licenses as $license) {
					$model->isNewRecord = true;
					$model->id = Generator::upgradeProtectionId();
					$model->license_id = $license->id;
					$model->buy_date = date('Y-m-d', strtotime($license->up_min_date . " + 1 day"));
					$model->end_date = $license->upgrade_protection_end_date;
					if ($model->buy_date > $model->end_date){
						$model->buy_date = $model->curdateDb;
					}

					// store payment id
					if ($modelPending) {
						$model->payment_id = $modelPending->payment_id;
					}

					if ($model->save(false)) {
						$upgrade_protection_ids[] = $model->id;
						$count++;
					}
				}

				foreach ($deliveries as $index => $delivery) {
					if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
						if ($delivery->add($model->license_ids, $upgrade_protection_ids)) {
							$delivery->sendEmail();
						}
					}
				}

				if ($pending_license_upgrade_protection_ids) {
					foreach ($pending_license_upgrade_protection_ids as $pending_license_upgrade_protection_id) {
						$pendingLicenseUpgradeProtectionModel = PendingLicenseUpgradeProtection::findOne(['id' => $pending_license_upgrade_protection_id]);
						if ($pendingLicenseUpgradeProtectionModel) {
							$pendingLicenseUpgradeProtectionModel->delete();
						}
					}
				}

				$this->successAlert = Yii::t('app', '{count} Annual Maintenance(s) and Support(s) have been successfully added to Licenses: {ids}.', ['count' => count($upgrade_protection_ids), 'ids' => implode(', ', $model->license_ids)]);
				return $this->redirect($route);
			}
		} else {
			if ($licenses) {
				//$model->license_ids = ArrayHelper::getColumn($licenses, 'id');

				if (!$modelPending) {
					$model->email_address = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'emailAddress'));
					$model->owner_name = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'owner_name'));
					$model->company = ArrayHelper::sameValue(ArrayHelper::getColumn($licenses, 'company'));
				} else {
					$model->email_address = $modelPending->email;
					$model->owner_name = $modelPending->owner_name;
					$model->company = $modelPending->company;
				}
				foreach($licenses as $license){
					$end_date = $license->upgradeProtectionEndDate;
					if ($end_date && ($end_date >= $license->curdateDb)){
						$license->up_min_date = $end_date;
					}
					else{
						$license->up_min_date = $license->curdateDb;
						//$license->up_min_date = date('Y-m-d', strtotime($license->curdateDb . " - 1 day"));
					}
					if ($end_date=$license->not_after_date){
						$license->up_max_date = date('Y-m-d', strtotime($end_date));						
					}
					else{
						$license->up_max_date = date('Y-m-d', strtotime($license->curdateDb . " + 20 years"));
					}
				}
				$model->period = UpgradeProtection::CUSTOM_PERIOD;				
			}
		}

		return $this->render('bulk-create', [
			'model' => $model,
			'deliveries' => $deliveries,
			'route' => $route,
			'licenses' => $licenses,
		]);
	}

	/**
	 * Add delivery to existing UpgradeProtection model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param array $ids
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDelivery($id)
	{
		$model = $this->findModel($id);
		if ($model->license == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		$model->scenario = 'delivery';

		$route = ($url = Url::previous('license_form')) ? $url : ['view', 'id' => $id];

		$deliveries = Delivery::initUpgradeProtectionModels(['product_id' => $model->license->productId]);

		if ($model->load(Yii::$app->request->post())) {
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			$validate = $model->validate();
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$count = 0;
				foreach ($deliveries as $index => $delivery) {
					if (($index <= $model->delivery_count) && !$delivery->isDisabled()) {
						if ($delivery->add($model->license_id, $model->id)) {
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
		} else {
			$model->email_address = $model->emailAddress;
		}

		return $this->render('delivery', [
			'model' => $model,
			'deliveries' => $deliveries,
			'route' => $route,
		]);
	}

	/**
	 * Add delivery to existing UpgradeProtection models.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param array $ids
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionBulkDelivery(array $ids, $id)
	{
		$model = new UpgradeProtection();
		$model->scenario = 'bulk-delivery';
		$model->email_id = $id;

		if ($model->email == null) {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}

		$route = ($url = Url::previous('license_form')) ? $url : ['/upgrade-protection/email', 'id' => $id];

		if ($upgradeProtections = UpgradeProtection::find()->with('email', 'productInfo', 'license')->andWhere(['id' => $ids])->ordered()->indexBy('id')->all()) {

			$upgrade_protection_ids = array_keys($upgradeProtections);
			$license_ids = ArrayHelper::getColumn($upgradeProtections, 'license_id');

			$model->product_id = ArrayHelper::sameValue(ArrayHelper::getColumn($upgradeProtections, 'productInfo.product_id'));
			if (empty($model->product_id)) {
				$this->warningAlert = Yii::t('app', 'Delivered Annual Maintenances and Supports must have the same Product.');
				return $this->redirect($route);
			}

			$model->email_address = ArrayHelper::sameValue(ArrayHelper::getColumn($upgradeProtections, 'email.address'));
		} else {
			throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
		}
		$deliveries = Delivery::initUpgradeProtectionModels(['product_id' => $model->product_id]);

		if ($model->load(Yii::$app->request->post())) {
			Model::loadMultiple($deliveries, Yii::$app->request->post());
			$validate = $model->validate();
			foreach ($deliveries as $index => $delivery) {
				$validate = (($index <= $model->delivery_count) && !$delivery->isDisabled()) ? $delivery->validate() && $validate : $validate;
			}
			if ($validate) {
				$count = 0;
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
			'upgradeProtections' => $upgradeProtections,
		]);
	}

	/**
	 * Updates an existing UpgradeProtection model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionUpdate($id)
	{
		$model = $this->findModel($id);

		if ($model->load(Yii::$app->request->post()) && $model->save()) {
			return $this->redirect(['view', 'id' => $model->id]);
		}

		return $this->render('update', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing UpgradeProtection model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();

		return $this->redirect(['index']);
	}

	/**
	 *
	 * @return array
	 */
	public function getProductInfoItems($product_id, $version)
	{
		$items = ProductInfo::find()->with('product')->andFilterWhere(['product_id' => $product_id, 'version' => $version])->orderBy(['subversion' => SORT_DESC])->all();
		return ArrayHelper::map($items, 'id', 'commercialVersion');
	}
}

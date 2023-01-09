<?php

namespace backend\controllers;

use Yii;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;

use common\helpers\Html;
use common\models\Payment;
use common\models\PaymentProduct;
use common\models\PaymentLicense;

use backend\models\PaymentSearch;

/**
 * PaymentController implements the CRUD actions for Payment model.
 */
class PaymentController extends BaseController
{

	/**
	 * {@inheritdoc}
	 */
	public function behaviors()
	{
		return [
			'access' => [
				'class' => AccessControl::class,
				'rules' => [
					[
						'allow' => true,
						'roles' => ['payment'],
					],
				],
			],
		];
	}

	/**
	 * Lists all License models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new PaymentSearch();

		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['created_at' => SORT_DESC]);

		Url::remember(['index'], 'payment_form');

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
	}

	/**
	 * Displays a single Payment model.
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
	 * Displays a single Payment model.
	 * @param string $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		$model = $this->findModel($id);
		Url::remember(['view', 'id' => $id], 'payment_form');
		return $this->render('view', [
			'model' => $model,
		]);
	}

	/**
	 * Deletes an existing Payment model.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDelete($id)
	{
		$this->findModel($id)->delete();
		return $this->redirect(['index']);
	}

	/**
	 * Deletes an existing Payment models
	 * @param array $ids
	 * @return mixed
	 */
	public function actionBulkDelete(array $ids, $msg = 0)
	{
		$models = Payment::find()->andWhere(['id' => $ids])->all();

		foreach ($models as $model) {
			$model->delete();
		}
		if ($msg) {
			if ($count = count($models)) {
				return ($count > 1) ? Yii::t('app', '{count} Payment removed', ['count' => $count]) : Yii::t('app', 'Selected Payments removed');
			}
		}
	}

	/**
	 * Finds the Payment model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Payment the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Payment::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}
}

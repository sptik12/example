<?php

namespace backend\controllers;

use Yii;
use common\models\Email;
use backend\models\EmailSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;
use yii\helpers\Url;

/**
 * EmailController implements the CRUD actions for Email model.
 */
class EmailController extends BaseController
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
						'actions' => ['index', 'index-view', 'view'],
						'allow' => true,
						'roles' => ['email'],
					],
				],
			],
		];
	}

	/**
	 * Lists all Email models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new EmailSearch();
		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['id' => SORT_DESC]);

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
	}

	/**
	 * Displays a single Delivery model.
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
	 * Finds the Email model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return Email the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = Email::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}

	/**
	 * Displays a single Email model.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		Url::remember(['view', 'id' => $id], 'license_form');
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	 * Updates an existing Email model.
	 * If update is successful, the browser will be redirected to the 'view' page.
	 * @param integer $id
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
	 * Deletes an existing Email model.
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
}

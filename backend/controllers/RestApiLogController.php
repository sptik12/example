<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

use common\helpers\Html;

use common\models\RestApiLog;
use common\models\RestApiLogAction;
use backend\models\RestApiLogSearch;

/**
* RestApiLogController implements the CRUD actions for RestApiLog model.
*/
class RestApiLogController extends BaseController
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
						'actions' => ['index', 'index-view'],
						'allow' => true,
						'roles' => ['rest-api-log'],
					],
					[
						'actions' => ['delete-bulk', 'delete-period'],
						'allow' => true,
						'roles' => ['rest-api-log'],
					],
				],
			],
 		];
	}

	/**
	* Lists all RestApiLog models.
	* @return mixed
	*/
	public function actionIndex()
	{
		$searchModel = new RestApiLogSearch();
		
		$dataProvider = $searchModel->search(Yii::$app->request->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['created_at' => SORT_DESC]);
		
		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];
		
		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
 	}

	/**
	* Displays a single RestApiLog model.
	* @return mixed
	* @throws NotFoundHttpException if the model cannot be found
	*/
	public function actionIndexView()
	{
		if (isset($_POST['expandRowKey'])) {

			$model = $this->findModel($_POST['expandRowKey']);

			$searchModel = new RestApiLogAction();
			$searchModel->rest_api_log_id = $model->id;
			
			$dataProvider = $searchModel->search([]);
			$dataProvider->query->ordered();
			$dataProvider->pagination= false;
			$dataProvider->sort = false;

			return $this->renderPartial('_view', ['searchModel' => $searchModel, 'model' => $model, 'dataProvider' => $dataProvider]);
		} 
		else {
			return Html::noData();
		}
	}

	/**
	* Deletes an existing RestApiLog models.
	* If deletion is successful, the browser will be redirected to the 'index' page.
	* @param array $ids
	* @return mixed
	* @throws NotFoundHttpException if the model cannot be found
	*/
	public function actionDeleteBulk(array $ids)
	{
		foreach ($ids as $id) {
			$this->findModel($id)->delete();
		}
		if (!Yii::$app->request->isAjax) {
			$this->successAlert = Yii::t('app', 'Log Records were successfully removed');
			return $this->redirect(['index']);
		}
	}

	/**
	* Deletes an existing RestApiLog models.
	* If deletion is successful, the browser will be redirected to the 'index' page.
	* @param array $ids
	* @return mixed
	* @throws NotFoundHttpException if the model cannot be found
	*/
	public function actionDeletePeriod($start_date, $end_date)
	{
		$model =  new RestApiLogSearch();
		RestApiLogAction::deleteAll(['between', 'created_at', $start_date . ' 00:00:01', $end_date . ' 23:59:59']);
		RestApiLog::deleteAll(['between', 'created_at', $start_date . ' 00:00:01', $end_date . ' 23:59:59']);
		$this->successAlert = Yii::t('app', 'Log Records were successfully removed');
		return $this->redirect(['index']);
	}
	
	/**
	* Finds the RestApiLog model based on its primary key value.
	* If the model is not found, a 404 HTTP exception will be thrown.
	* @param integer $id
	* @return RestApiLog the loaded model
	* @throws NotFoundHttpException if the model cannot be found
	*/
	protected function findModel($id)
	{
		if (($model = RestApiLog::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}
}

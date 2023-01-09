<?php

namespace backend\controllers;

use Yii;
use yii\web\NotFoundHttpException;
use yii\filters\AccessControl;

use common\models\MailLog;
use backend\models\MailLogSearch;

/**
 * MailLogController implements the CRUD actions for MailLog model.
 */
class MailLogController extends BaseController
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
						'actions' => [ 'index', 'view', 'index-view', 'source'],
						'allow' => true,
						'roles' => ['mail-log'],
					],
					[
						'actions' => ['delete-bulk', 'delete-period'],
						'allow' => true,
						'roles' => ['mail-log'],
					],
				],
			],
		];
	}

	/**
	 * Lists all MailLog models.
	 * @return mixed
	 */
	public function actionIndex()
	{
		$searchModel = new MailLogSearch();
		$dataProvider = $searchModel->search($this->queryParams);
		$dataProvider->pagination->pageSize = $this->gridPageSize;
		$dataProvider->sort->defaultOrder = $searchModel->getSortDefaultOrder(['created_at' => SORT_DESC]);

		$params = [
			'searchModel' => $searchModel,
			'dataProvider' => $dataProvider,
		];

		return Yii::$app->request->isAjax ? $this->renderPartial('index', $params) : $this->render('index', $params);
	}

	/**
	 * Displays a mail content
	 * @param string $type text|html|null
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionIndexView($type = null)
	{
		if (isset($_POST['expandRowKey'])) {
			$model = $this->findModel($_POST['expandRowKey']);
			if ($model) {
				return $this->renderPartial('_view', ['model' => $model, 'type' => $type]);
			}
		}
		else {
			return Html::noData();
		}
	}

	/**
	 * Displays a single MailLog model.
	 * @param integer $id
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionView($id)
	{
		return $this->render('view', [
			'model' => $this->findModel($id),
		]);
	}

	/**
	* Views source an existing MailLog model.
	* If update is successful, the browser will be redirected to the 'view' page.
	* @param integer $id
	* @return mixed
	* @throws NotFoundHttpException if the model cannot be found
	*/
	public function actionSource($id)
	{
		$model = $this->findModel($id);

		if (Yii::$app->request->isAjax) {
			$this->layout = 'modal';
		}

		return $this->render('source', [
			'model' => $model,
		]);
	}

	/**
	 * Finds the MailLog model based on its primary key value.
	 * If the model is not found, a 404 HTTP exception will be thrown.
	 * @param integer $id
	 * @return MailLog the loaded model
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	protected function findModel($id)
	{
		if (($model = MailLog::findOne($id)) !== null) {
			return $model;
		}

		throw new NotFoundHttpException(Yii::t('app', 'The requested page does not exist.'));
	}

	/**
	 * Deletes an existing MailLog models.
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
	 * Deletes an existing MailLog models.
	 * If deletion is successful, the browser will be redirected to the 'index' page.
	 * @param array $ids
	 * @return mixed
	 * @throws NotFoundHttpException if the model cannot be found
	 */
	public function actionDeletePeriod($start_date, $end_date, $record_id = null, $email  = null)
	{
		$this->setMemoryLimit();
		$ids = MailLog::find()->select('id')->andFilterWhere(['between', 'created_at', $start_date . ' 00:00:01', $end_date . ' 23:59:59'])->andFilterWhere([
			'record_id' => $record_id,
		])->andFilterWhere(['like', 'email', $email])->column();

		$count = count($ids);
		if ($ids){
			$ids = implode(',', $ids);
			MailLog::deleteAll('id in ('  . $ids . ')');
		}

		if (!Yii::$app->request->isAjax) {
			$this->successAlert = Yii::t('app', '{count} Log Records were successfully removed', ['count' => $count]);
			return $this->redirect(['index']);
		}
	}
}

<?php

class CompraCartaoController extends Controller
{
    public $_model = null;

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'actions'=>array('novo','alterar','index', 'delete'),
                'users'=>array('@'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
    }

    /**
     * Creates a new model.
     * If creation is successful, the browser will be redirected to the 'view' page.
     */
    public function actionNovo()
    {
        $model=new CompraCartao;

        if(isset($_POST['CompraCartao']))
        {
            $model->attributes=$_POST['CompraCartao'];
            if ($model -> save())
            {
                $criteriaFatura = new CDbCriteria(array(
                    'condition'=>':dataCompra between abertura and prevFechamento',
                    'params'=>array(':dataCompra'=>$model->dataCompra)
                ));

                $modelFatura = Fatura::model()->find($criteriaFatura);
                $idfatura = (count($modelFatura) > 0) ? $modelFatura->idFatura : null;

                $valorParcela = $model->valorTotal/$model->quantParcelas;

                if(!is_null($idfatura))
                {
                    $modelFatura->formataData = false;
                    $modelFatura->totalPagar = $modelFatura->totalPagar+$valorParcela;
                    $modelFatura->save();
                }

                unset($criteriaFatura,$modelFatura);

                for($i=0; $i < $model->quantParcelas; $i++)
                {
                    $vencimento = ($i==0) ? $model->dataCompra : new CDbExpression("DATE_ADD('".$model->dataCompra."', INTERVAL ".$i. " MONTH)");
                    $modelParcela = new Parcela;
                    $modelParcela->attributes = array(
                        'idCompraCartao'=>$model->idCompraCartao,
                        'parcela'=>$i+1,
                        'valor'=>$valorParcela,
                        'dataVenc'=>$vencimento,
                        'idfatura'=>$idfatura
                    );
                    $modelParcela->save();
                    $idfatura = null;
                }
                Yii::app()->user->setFlash('success', 'Dados Salvos.');
            }
            else
            {
                $this->_model = $model;
                $this->actionIndex();
                exit;
            }
        }

        $this->redirect($this->createUrl('compracartao/index'));
    }

    /**
     * Updates a particular model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id the ID of the model to be updated
     */
    public function actionAlterar($id)
    {
        $model=$this->loadModel($id);

        if(isset($_POST['CompraCartao']))
        {
            $model->attributes=$_POST['CompraCartao'];
            if ($model -> save())
            {
                Yii::app()->user->setFlash('success', 'Dados Alterados.');
                $this->redirect($this->createUrl('compracartao/index'));
            }
        }

        $this->_model = $model;
        $this->actionIndex();
    }

    /**
     * Deletes a particular model.
     * If deletion is successful, the browser will be redirected to the 'admin' page.
     * @param integer $id the ID of the model to be deleted
     */
    public function actionDelete($id)
    {
        if(Yii::app()->request->isPostRequest)
        {
            // we only allow deletion via POST request
            $this->loadModel($id)->delete();

            // if AJAX request (triggered by deletion via admin grid view), we should not redirect the browser
            if(!isset($_GET['ajax']))
                $this->redirect(isset($_POST['returnUrl']) ? $_POST['returnUrl'] : array('admin'));
        }
        else
            throw new CHttpException(400,'Invalid request. Please do not repeat this request again.');
    }

    /**
     * Lists all models.
     */
    public function actionIndex()
    {
        $model=(is_null($this->_model)) ? new CompraCartao : $this->_model;
        $model->valorTotal = Formatacao::formatMoeda($model->valorTotal);
        $model->dataCompra = Formatacao::formatData($model->dataCompra);

        $dataProvider=new CActiveDataProvider('CompraCartao');
        $this->render('index',array(
            'dataProvider'=>$dataProvider,
            'model'=>$model,
            'dataCartao'=>CHtml::listData(CartaoCredito::model()->findAll(),'idCartaoCredito','nome')
        ));
    }


    /**
     * Returns the data model based on the primary key given in the GET variable.
     * If the data model is not found, an HTTP exception will be raised.
     * @param integer the ID of the model to be loaded
     */
    public function loadModel($id)
    {
        $model=CompraCartao::model()->findByPk($id);
        if($model===null)
            throw new CHttpException(404,'The requested page does not exist.');
        return $model;
    }

    /**
     * Performs the AJAX validation.
     * @param CModel the model to be validated
     */
    protected function performAjaxValidation($model)
    {
        if(isset($_POST['ajax']) && $_POST['ajax']==='compra-cartao-form')
        {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }
    }
}
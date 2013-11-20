<?php

/**
 * This is the model class for table "conta".
 *
 * The followings are the available columns in table 'conta':
 * @property integer $idConta
 * @property integer $idUsuario
 * @property integer $idTipoMovimentacao
 * @property string $descricao
 *
 * The followings are the available model relations:
 * @property Tipomovimentacao $idTipoMovimentacao0
 * @property Usuario $idUsuario0
 * @property Movimentacao[] $movimentacaos
 */
class Conta extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'conta';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('idUsuario, idTipoMovimentacao, descricao', 'required'),
			array('idUsuario, idTipoMovimentacao', 'numerical', 'integerOnly'=>true),
			array('descricao', 'length', 'max'=>45),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('idConta, idUsuario, idTipoMovimentacao, descricao', 'safe', 'on'=>'search'),
		);
	}

    public function beforeValidate()
    {
        parent::beforeValidate();
        $this->idUsuario = Yii::app()->user->idUsuario;
        return true;
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'idTipoMovimentacao0' => array(self::BELONGS_TO, 'Tipomovimentacao', 'idTipoMovimentacao'),
			'idUsuario0' => array(self::BELONGS_TO, 'Usuario', 'idUsuario'),
			'movimentacaos' => array(self::HAS_MANY, 'Movimentacao', 'idConta'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'idConta' => 'Id Conta',
			'idUsuario' => 'Id Usuario',
			'idTipoMovimentacao' => 'Tipo Movimentação',
			'descricao' => 'Descrição',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('idConta',$this->idConta);
		$criteria->compare('idUsuario',$this->idUsuario);
		$criteria->compare('idTipoMovimentacao',$this->idTipoMovimentacao);
		$criteria->compare('descricao',$this->descricao,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Conta the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}

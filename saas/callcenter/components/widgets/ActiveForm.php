<?php

namespace callcenter\components\widgets;

class ActiveForm extends \yii\widgets\ActiveForm
{
	public function init()
	{
		parent::init();
		$this->fieldConfig = array(
			'class' => 'callcenter\components\widgets\ActiveField',
			'template' => "{input}\n{error}",
			'inputOptions' => array(
				'class' => 'txt',
			)
		);
	}
}
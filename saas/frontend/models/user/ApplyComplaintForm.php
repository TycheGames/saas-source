<?php

namespace frontend\models\user;

use frontend\models\BaseForm;


/**
 * ContactForm is the model behind the contact form.
 */
class ApplyComplaintForm extends BaseForm
{
    public $userId;
    public $problemId;
    public $description;
    public $fileList;
    public $contact;


    public function maps(): array
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['problemId', 'description'], 'required'],
            [['contact','fileList'], 'safe'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'userId'      => 'user id',
            'problemId'   => 'issue option',
            'description' => 'issue description',
            'fileList'    => 'image list',
            'contact'     => 'contact information',
        ];
    }
}

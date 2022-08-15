<?php

namespace frontend\models\risk;

use yii\base\Model;

/**
 * Class ApplyForm
 * @package frontend\models\risk
 *
 * @property int $user_id
 * @property int $order_id
 * @property string $app_name
 * @property UserBasicForm $user_basic_info_model
 * @property OrderInfoForm $order_info_model
 * @property ClientInfoForm $client_info_model
 * @property PictureMetadataForm $picture_metadata_model
 *
 */
class ApplyForm extends Model
{
    public $user_id;
    public $order_id;
    public $app_name;

    public $user_basic_info;
    public $order_info;
    public $client_info;
    public $picture_metadata;

    public $user_basic_info_model;
    public $order_info_model;
    public $client_info_model;
    public $picture_metadata_model;


    public function validateUserBasicInfo()
    {
        $form = new UserBasicForm();
        if($form->load($this->user_basic_info, '') && $form->validate())
        {
            $this->user_basic_info_model = $form;
            return true;

        }else{
            $this->addError('user_basic_info', implode(',', $form->getErrorSummary(true)));
            return false;
        }
    }

    public function validateOrderInfo()
    {
        $form = new OrderInfoForm();
        if($form->load($this->order_info, '') && $form->validate())
        {
            $this->order_info_model = $form;
            return true;

        }else{
            $this->addError('order_info', implode(',', $form->getErrorSummary(true)));
            return false;
        }
    }


    public function validateClientInfo()
    {
        $form = new ClientInfoForm();
        if($form->load($this->client_info, '') && $form->validate())
        {
            $this->client_info_model = $form;
            return true;

        }else{
            $this->addError('client_info', implode(',', $form->getErrorSummary(true)));
            return false;
        }
    }

    public function validatePictureMetadata()
    {
        $form = new PictureMetadataForm();
        if($form->load($this->picture_metadata, '') && $form->validate())
        {
            $this->picture_metadata_model = $form;
            return true;

        }else{
            $this->addError('picture_metadata', implode(',', $form->getErrorSummary(true)));
            return false;
        }
    }



    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['user_basic_info', 'order_info', 'client_info', 'picture_metadata', 'user_id', 'order_id', 'app_name'], 'required'],
            ['user_basic_info', 'validateUserBasicInfo'],
            ['order_info', 'validateOrderInfo'],
            ['client_info', 'validateClientInfo'],
            ['picture_metadata', 'validatePictureMetadata'],
        ];
    }

}

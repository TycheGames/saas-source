<?php

namespace frontend\models\user;

use common\helpers\Util;
use frontend\models\BaseForm;

/**
 * ContactForm is the model behind the contact form.
 */
class RegisterForm extends BaseForm
{
    public $phone, $code, $password;
    public
        $packageName,
        $clientInfo,
        $afStatus, // 是自然量还是非自然量：(Organic, Non-organic)
        $mediaSource, //渠道名：若为自然量，则为null
        $clickTime, // 点击时间：若为自然量，则为null；若为预装包与 install_time 一致，格式：2019-09-11 08:37:46.797
        $installTime, // 安装时间：若为自然量，则为null；若为预装包与 click_time 一致，格式：2019-09-11 08:37:46.797
        $appsFlyerUID, // appsflyer的uid，每次APP重装都会变更
        $conversionData;


    public function maps() : array
    {
        return [
            'phone' => 'phone',
            'password' => 'password',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['phone','password','code'], 'required'],
            [['phone','password','code'], 'trim'],
            ['phone', 'validatePhone'],
            ['password', 'string', 'min' => 6, 'max' => 20],
            ['code', 'string', 'length' => 6],
            [['afStatus', 'mediaSource', 'clickTime', 'installTim', 'appsFlyerUID', 'clientInfo', 'packageName', 'conversionData'],'safe']
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'phone number',
            'password' => 'password',
            'code' => 'OTP'
        ];
    }

    public function validatePhone($attribute, $params)
    {
        if(strlen($this->$attribute) == 12 && substr($this->$attribute,0 , 2) == '91')
        {
            $this->$attribute = substr($this->$attribute, 2, 10);
        }

        if(strlen($this->$attribute) != 10)
        {
            $this->addError($attribute, 'please enter a valid mobile phone number');
        }
    }
}

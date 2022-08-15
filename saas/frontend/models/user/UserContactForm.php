<?php


namespace frontend\models\user;


use common\helpers\Util;
use common\models\enum\Relative;
use frontend\models\BaseForm;

class UserContactForm extends BaseForm
{
    public $relativeContactPerson, $name, $phone;
    public $otherRelativeContactPerson, $otherName, $otherPhone;
    public $facebookAccount, $whatsAppAccount, $skypeAccount;
    public $clientInfo;
    public $userPhone;

    function maps(): array
    {
        return [
            'relativeContactPerson'      => 'relative_contact_person',
            'name'                       => 'name',
            'phone'                      => 'phone',
            'otherRelativeContactPerson' => 'other_relative_contact_person',
            'otherName'                  => 'other_name',
            'otherPhone'                 => 'other_phone',
            'facebookAccount'            => 'facebook_account',
            'whatsAppAccount'            => 'whatsApp_account',
            'skypeAccount'               => 'skype_account',
            'clientInfo'                 => 'client_info',
        ];
    }

    public function attributeLabels(): array
    {
        return [
            'relativeContactPerson'      => 'Emergency contact relationship',
            'name'                       => 'Emergency contact name',
            'phone'                      => 'Emergency contact phone',
            'otherRelativeContactPerson' => 'Other contact relationships',
            'otherName'                  => 'Other contact name',
            'otherPhone'                 => 'Other contact phone',
            'facebookAccount'            => 'Facebook account',
            'whatsAppAccount'            => 'WhatsApp account',
            'skypeAccount'               => 'Skype account',
        ];
    }

    public function rules(): array
    {
        $phoneRegular = Util::getPhoneMatch();
        return [
            //必填项
            [['relativeContactPerson', 'name', 'phone', 'otherRelativeContactPerson', 'otherName', 'otherPhone', 'clientInfo'], 'required'],
            //对表单项进行去掉首尾空格的处理
            [['relativeContactPerson', 'name', 'phone', 'otherRelativeContactPerson', 'otherName', 'otherPhone', 'facebookAccount', 'whatsAppAccount', 'skypeAccount'], 'trim'],
            //范围检测
            ['relativeContactPerson', 'in', 'range' => array_values(Relative::toArray()), 'message' => 'Error emergency relationship'],
            ['otherRelativeContactPerson', 'in', 'range' => array_values(Relative::toArray()), 'message' => 'Error other relationship'],
            //手机号检测
            // ['phone', 'validatePhone', 'skipOnEmpty' => false, 'skipOnError' => true, 'params' => [
            //     'errorMsg1' => 'Do Not Choose Yourself',
            //     'errorMsg2' => 'Emergency contact must include a mobile number',
            // ]],
            // ['otherPhone', 'validatePhone', 'skipOnEmpty' => false, 'skipOnError' => true, 'params' => [
            //     'errorMsg1' => 'Do Not Choose Yourself',
            //     'errorMsg2' => 'Other contact must include a mobile number',
            // ]],
            ['phone', 'validateSame', 'skipOnEmpty' => false, 'skipOnError' => true, 'params' => ['attribute' => 'otherPhone']],
        ];
    }

    public function validateSame($attribute, $params)
    {
        $compareAttr = $params['attribute'];
        if ($this->$attribute == $this->$compareAttr) {
            $this->addError($attribute, 'The emergency contact phone is the same as the other contact phone');
        }
    }

    public function validatePhone($attribute, $params)
    {
        $hasPhone = false;
        $phoneRegular = Util::getPhoneMatch();
        if(strpos($this->$attribute, $this->userPhone) !== false) {
            $this->addError($attribute, $params['errorMsg1']);
        }
        $phoneNumbers = explode(':', $this->$attribute);
        foreach ($phoneNumbers as $number) {
            if(preg_match($phoneRegular, $number)) {
                $hasPhone = true;
                $this->$attribute = substr($number, -10, 10);
                break;
            }
        }
        if(!$hasPhone) {
            $this->addError($attribute, $params['errorMsg2']);
        }
    }

    public function save(): bool
    {
        return true;
    }
}
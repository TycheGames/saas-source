<?php

namespace frontend\models\user;

use common\models\enum\City;
use frontend\models\BaseForm;
use yii\base\Model;
use common\models\enum\Religion;
use common\models\enum\Student;
use common\models\enum\Marital;

/**
 * Class UserBasicInfoValidate
 * @package frontend\models\user
 *
 * @property string fullName 姓名
 * @property string birthday 出生日期
 * @property string studentVal 学生
 * @property int studentId
 * @property string maritalStatusVal 婚姻
 * @property int maritalStatusId
 * @property string emailVal 邮箱
 * @property string zipCodeVal 邮编
 * @property string aadhaarPinCode
 * @property string aadhaarAddressId
 * @property string aadhaarAddressVal
 * @property string aadhaarDetailAddressVal
 * @property string clientInfo 客户端信息
 */
class UserBasicInfoForm extends BaseForm
{
    public $studentId, $maritalStatusId;
    public $emailVal, $zipCodeVal;
    public $bankStatementFileDeleteArr, $bankStatementFileAddArr;
    public $fullName, $birthday;
    public $clientInfo;
    public $aadhaarPinCode, $aadhaarDetailAddressVal;
    public $aadhaarAddressId, $aadhaarAddressVal;
    public $aadhaarAddressId1, $aadhaarAddressId2, $aadhaarAddressVal1, $aadhaarAddressVal2;

    public function maps() : array
    {
        return [
            'studentId'               => 'student',
            'maritalStatusId'         => 'marital_status',
            'emailVal'                => 'email_address',
            'zipCodeVal'              => 'zip_code',
            'fullName'                => 'full_name',
            'birthday'                => 'birthday',
            'aadhaarPinCode'          => 'aadhaar_pin_code',
            'aadhaarAddressId1'       => 'aadhaar_address_code1',
            'aadhaarAddressId2'       => 'aadhaar_address_code2',
            'aadhaarAddressVal1'      => 'aadhaar_address1',
            'aadhaarAddressVal2'      => 'aadhaar_address2',
            'aadhaarDetailAddressVal' => 'aadhaar_detail_address',
            'clientInfo'              => 'client_info',
        ];
    }

    public function attributeLabels()
    {
        return [
            'fullName'                => 'Full Name',
            'birthday'                => 'Birthday',
            'studentId'               => 'Student',
            'maritalStatusId'         => 'Marital',
            'emailVal'                => 'Email ID',
            'zipCodeVal'              => 'Zip Code',
            'aadhaarAddressId'        => 'Aadhaar Address ID',
            'aadhaarAddressVal'       => 'Aadhaar Address Value',
            'aadhaarDetailAddressVal' => 'Aadhaar Detail Address Value',
            'aadhaarPinCode'          => 'Aadhaar Pin Code',
        ];
    }

    public function rules()
    {
        return [
            //必填项
            [['maritalStatusId', 'emailVal', 'zipCodeVal', 'fullName', 'birthday', 'clientInfo', 'aadhaarPinCode', 'aadhaarAddressId', 'aadhaarAddressVal', 'aadhaarDetailAddressVal'], 'required'],
            //对表单项进行去掉首尾空格的处理
            [['studentId', 'maritalStatusId', 'emailVal', 'zipCodeVal', 'fullName', 'aadhaarPinCode', 'aadhaarAddressId', 'aadhaarAddressVal', 'aadhaarDetailAddressVal'], 'trim'],
            //范围检测
            //            ['studentId', 'in', 'range' => array_values(Student::toArray()), 'message' => 'Error student status'],
            ['aadhaarAddressVal', 'checkCity', 'skipOnEmpty' => false, 'skipOnError' => true, 'params' => ['errorMsg' => 'Error aadhaar address']],
            ['maritalStatusId', 'in', 'range' => array_values(Marital::toArray()), 'message' => 'Error marital status'],
            //邮箱检测
            ['emailVal', 'email'],
            //图片检测
            [['zipCodeVal'], 'number', 'min' => 100000, 'max' => 999999],
        ];
    }

    public function validatePic($attribute, $params)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, 'Bank statement data is empty');
        }

        if (count($this->$attribute) > 8) {
            $this->addError($attribute, 'Bank statement data too much');
        }

        foreach ($this->$attribute as $key => $item) {
            if (empty($item['url'])) {
                $this->addError($attribute, 'Bank statement data has errors');
                break;
            }
        }

        return;
    }

    public function checkCity($attribute, $params)
    {
        $cityArr = explode(',', $this->$attribute);
        if (count($cityArr) != 2) {
            $this->addError($attribute, $params['errorMsg']);
        } elseif (!in_array($cityArr[1], City::$city[$cityArr[0]] ?? [])) {
            $this->addError($attribute, $params['errorMsg']);
        }
    }

    public function save()
    {
        return true;
    }
}
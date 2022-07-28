<?php


namespace frontend\models\user;


use common\models\enum\City;
use common\models\enum\Education;
use common\models\enum\Industry;
use common\models\enum\Seniority;
use frontend\models\BaseForm;
use yii\base\Model;

/**
 * Class UserWorkInfoForm
 * @package frontend\models\user
 *
 * @property string $educatedSchoolVal
 * @property string $educationVal
 * @property int $educationId
 * @property string $currPinCode
 * @property string $residentialAddressId
 * @property string $residentialAddressVal
 * @property string $residentialDetailAddressVal
 * @property string $industryVal
 * @property int $industryId
 * @property string $companyNameVal
 * @property string $companyPhoneVal
 * @property string $companyAddressId
 * @property string $companyAddressVal
 * @property string $companyDetailAddressVal
 * @property string $workPositionVal
 * @property int $companySeniorityId
 * @property string $companySeniorityVal
 * @property int $workingSeniorityId
 * @property string $workingSeniorityVal
 * @property int $monthlySalaryVal
 * @property array $companyDocsAddArr
 * @property array $companyDocsDeleteArr
 * @property string clientInfo
 */
class UserWorkInfoForm extends BaseForm
{
    public $educatedSchoolVal;
    public $educationId, $educationVal;
    public $currPinCode, $residentialAddressId, $residentialAddressVal, $residentialDetailAddressVal;
    public $industryId, $industryVal;
    public $companyNameVal, $companyPhoneVal, $companyAddressId, $companyAddressVal, $companyDetailAddressVal;
    public $workPositionVal, $workingSeniorityId, $workingSeniorityVal, $companySeniorityId, $companySeniorityVal, $monthlySalaryVal;
    public $companyDocsAddArr, $companyDocsDeleteArr;
    //后台逻辑处理的字段
    public $residentialAddressId1, $residentialAddressId2, $residentialAddressVal1, $residentialAddressVal2;
    public $companyAddressId1, $companyAddressId2, $companyAddressVal1, $companyAddressVal2;
    public $clientInfo;

    public function maps(): array
    {
        return [
            'educatedSchoolVal'           => 'educated_school',
            'educationId'                 => 'educated',
            'currPinCode'                 => 'residential_pincode',
            'residentialAddressId1'       => 'residential_address_code1',
            'residentialAddressId2'       => 'residential_address_code2',
            'residentialAddressVal1'      => 'residential_address1',
            'residentialAddressVal2'      => 'residential_address2',
            'residentialDetailAddressVal' => 'residential_detail_address',
            'industryId'                  => 'industry',
            'companyNameVal'              => 'company_name',
            'companyPhoneVal'             => 'company_phone',
            'companyAddressId1'           => 'company_address_code1',
            'companyAddressId2'           => 'company_address_code2',
            'companyAddressVal1'          => 'company_address1',
            'companyAddressVal2'          => 'company_address2',
            'companyDetailAddressVal'     => 'company_detail_address',
            'workPositionVal'             => 'work_position',
            'companySeniorityId'          => 'company_seniority',
            'workingSeniorityId'          => 'working_seniority',
            'monthlySalaryVal'            => 'monthly_salary',
            'companyDocsAddArr'           => 'certificate_of_company_docs',
            'clientInfo'                  => 'client_info',
        ];
    }

    public function attributeLabels()
    {
        return [
            'educatedSchoolVal'           => 'Educated School',
            'educationId'                 => 'Education',
            'currPinCode'                 => 'Current PinCode',
            'residentialAddressId'        => 'Residential Address',
            'residentialAddressVal'       => 'Residential Address',
            'residentialDetailAddressVal' => 'Residential Detail Address',
            'industryId'                  => 'Industry',
            'companyNameVal'              => 'Company Name',
            'companyPhoneVal'             => 'Company Phone',
            'companyAddressId'            => 'Company Address',
            'companyAddressVal'           => 'Company Address',
            'companyDetailAddressVal'     => 'Company Detail Address',
            'workPositionVal'             => 'Work Position',
            'companySeniorityId'          => 'Company Seniority',
            'workingSeniorityId'          => 'Working Seniority',
            'monthlySalaryVal'            => 'Monthly Salary',
//            'companyDocsAddArr'           => '公司证明照片-添加',
//            'companyDocsDeleteArr'        => '公司证明照片-删除',
        ];
    }

    public function rules()
    {
        return [
            //必填项
            [['educationId', 'residentialAddressId', 'residentialAddressVal', 'residentialDetailAddressVal', 'industryId', 'monthlySalaryVal', 'companyNameVal', 'clientInfo'], 'required'],
//            [['educatedSchoolVal', 'educationId', 'currPinCode', 'residentialAddressId', 'residentialAddressVal', 'residentialDetailAddressVal', 'industryId', 'companyNameVal', 'companyPhoneVal', 'companyAddressId', 'companyAddressVal', 'companyDetailAddressVal', 'workPositionVal', 'workingSeniorityId', 'companySeniorityId', 'monthlySalaryVal'], 'required'],
            //对表单项进行去掉首尾空格的处理
            [['residentialAddressId', 'residentialAddressVal', 'residentialDetailAddressVal', 'companyNameVal', 'monthlySalaryVal'], 'trim'],
//            [['educatedSchoolVal', 'residentialAddressId', 'residentialAddressVal', 'residentialDetailAddressVal', 'companyNameVal', 'companyPhoneVal', 'companyAddressId', 'companyAddressVal', 'companyDetailAddressVal', 'workPositionVal', 'monthlySalaryVal'], 'trim'],
            ['residentialAddressVal', 'checkCity', 'skipOnEmpty' => false, 'skipOnError' => true, 'params' => ['errorMsg' => 'Error residential address']],
            //范围检测
            ['educationId', 'in', 'range' => array_values(Education::toArray()), 'message' => 'Error education status'],
            ['industryId', 'in', 'range' => array_values(Industry::toArray()), 'message' => 'Error industry status'],
//            ['workingSeniorityId', 'in', 'range' => array_values(Seniority::toArray()), 'message' => 'Error working seniority status'],
//            ['companySeniorityId', 'in', 'range' => array_values(Seniority::toArray()), 'message' => 'Error company seniority status'],
            //图片检测
//            ['companyDocsAddArr', 'validatePic', 'skipOnEmpty' => false],
//            ['companyDocsDeleteArr', 'validatePic', 'skipOnEmpty' => true],
            ['monthlySalaryVal', 'integer'],
        ];
    }

    public function validatePic($attribute, $params)
    {
        if (empty($this->$attribute)) {
            $this->addError($attribute, 'Company document data is empty');
        }

        if (count($this->$attribute) > 1) {
            $this->addError($attribute, 'Company document data too much');
        }

        foreach ($this->$attribute as $key => $item) {
            if (empty($item['url'])) {
                $this->addError($attribute, 'Company document data has errors');
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
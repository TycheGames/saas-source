<?php


namespace frontend\models\user;


use common\models\enum\AddressProofType;
use frontend\models\BaseForm;

/**
 * Class UserAddressProofReportForm
 * @package frontend\models\user
 *
 * @property int $addressProofReportId
 * @property string $addressProofType
 * @property string $params
 */
class UserAddressProofReportForm extends BaseForm
{
    public $addressProofReportId;
    public $addressProofType;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'addressProofReportId' => 'Back Picture',
            'addressProofType'     => 'Address Proof Type',
        ];
    }

    public function rules()
    {
        return [
            [['addressProofReportId', 'addressProofType'], 'required'],
            ['addressProofType', 'in', 'range' => array_values(AddressProofType::toArray()), 'message' => 'Error Address Proof Type'],
        ];
    }
}
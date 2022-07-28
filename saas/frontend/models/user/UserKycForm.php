<?php


namespace frontend\models\user;


use frontend\models\BaseForm;

/**
 * Class UserKycForm
 * @package frontend\models\user
 *
 * @property string $panReportId
 * @property string $panCode
 * @property string $frReportId
 * @property string $aadReportId
 * @property string $crossReportId
 * @property string $aadhaarType
 * @property string $params
 */
class UserKycForm extends BaseForm
{
    public $panReportId;
    public $panCode;
    public $frReportId;
    public $aadReportId;
    public $crossReportId;
    public $aadhaarType;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['panReportId', 'panCode', 'frReportId', 'params'], 'required'],
            [['panReportId', 'panCode', 'frReportId', 'aadReportId', 'crossReportId', 'aadhaarType'], 'trim'],
        ];
    }
}
<?php


namespace frontend\models\user;


use frontend\models\BaseForm;

/**
 * Class UserOcrFrForm
 * @package frontend\models\user
 *
 * @property string $reportId
 * @property string $params
 */
class UserFrSecForm extends BaseForm
{
    public $reportId;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['reportId', 'params'], 'required'],
            [['reportId'], 'trim'],
        ];
    }
}
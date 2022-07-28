<?php


namespace frontend\models\notify;


use Carbon\Carbon;
use frontend\models\BaseForm;

/**
 * Class KudosOfflineTransactionForm
 * @package frontend\models\notify
 *
 * @property string $phone
 * @property string $aadhaar
 * @property string $encryptedAadhaar
 * @property string $pan
 * @property string $packageName
 * @property string $token
 * @property string $data
 * @property array $decodeData
 */
class PushOrderUserInfoForm extends BaseForm
{
    public $phone;
    public $aadhaar;
    public $encryptedAadhaar;
    public $pan;
    public $packageName;
    public $token;
    public $data;
    public $decodeData;

    function maps(): array
    {
        return [];
    }

    public function rules()
    {
        return [
            [['phone', 'aadhaar', 'encryptedAadhaar', 'pan', 'packageName', 'token', 'data'], 'required'],
            ['token', 'required', 'requiredValue' => 'S9PCByTd7XNcljj1'],
        ];
    }
}
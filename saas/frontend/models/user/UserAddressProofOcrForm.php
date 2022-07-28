<?php


namespace frontend\models\user;


use common\models\enum\AddressProofType;
use frontend\models\BaseForm;
use yii\web\UploadedFile;

/**
 * Class UserAddressProofForm
 * @package frontend\models\user
 *
 * @property null|UploadedFile $picFront
 * @property null|UploadedFile $picBack
 * @property int $addressProofType
 * @property string params
 */
class UserAddressProofOcrForm extends BaseForm
{
    public $picFront, $picBack;
    public $addressProofType;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'picFront'         => 'Front Picture ',
            'picBack'          => 'Back Picture',
            'addressProofType' => 'Address Proof Type',
        ];
    }

    public function rules()
    {
        return [
            [['picFront', 'picBack', 'addressProofType'], 'required'],
            [['picFront', 'picBack'], 'image', 'maxSize' => 5 * 1024 * 1024, 'skipOnEmpty' => true],
            ['addressProofType', 'in', 'range' => array_values(AddressProofType::toArray()), 'message' => 'Error Address Proof Type'],
        ];
    }
}
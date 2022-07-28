<?php


namespace frontend\models\user;


use frontend\models\BaseForm;
use yii\web\UploadedFile;

/**
 * Class UserPanFrom
 * @package frontend\models\user
 *
 * @property null|UploadedFile $aadhaarPicF
 * @property null|UploadedFile $aadhaarPicB
 * @property string $params
 */
class UserAadhaarForm extends BaseForm
{
    public $aadhaarPicF, $aadhaarPicB;
    public $params;

    function maps(): array
    {
        return [];
    }

    public function attributeLabels()
    {
        return [
            'panPic' => 'Pan Picture',
        ];
    }

    public function rules()
    {
        return [
            [['aadhaarPicF', 'aadhaarPicB'], 'required'],
            [['aadhaarPicF', 'aadhaarPicB'], 'image', 'maxSize' => 5 * 1024 * 1024, 'skipOnEmpty' => true],
        ];
    }
}
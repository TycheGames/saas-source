<?php


namespace frontend\models\user;


use frontend\models\BaseForm;
use yii\web\UploadedFile;

/**
 * Class UserOcrFrForm
 * @package frontend\models\user
 *
 * @property null|UploadedFile $frPic
 * @property null|UploadedFile $frData
 * @property string sign
 * @property string $params
 */
class UserFrForm extends BaseForm
{
    public $frPic, $frData;
    public $sign;
    public $params;

    function maps(): array
    {
        return [
            'frPic'  => 'frPic',
            'frData' => 'frData',
            'sign'   => 'sign',
            'params' => 'params',
        ];
    }

    public function rules()
    {
        return [
            [['frPic', 'frData', 'sign'], 'required'],
            ['frPic', 'image', 'maxSize' => 5 * 1024 * 1024, 'skipOnEmpty' => true],
            ['frData', 'file', 'maxSize' => 5 * 1024 * 1024, 'skipOnEmpty' => true],
//            ['sign', 'validateSign', 'skipOnEmpty' => true, 'params' => ['str' => 'loan', 'field' => ['frPic', 'frData']]],
        ];
    }

    public function validateSign($attribute, $params)
    {
        $file1 = $params['field'][0];
        $file2 = $params['field'][1];
        $str1 = md5_file($this->$file1->tempName);
        $str2 = md5_file($this->$file2->tempName);
        $calcSign = md5($str1. $params['str'] . $str2);
        if ($calcSign !== $this->$attribute) {
            $this->addError($attribute, 'sign has errors');
        }
    }
}
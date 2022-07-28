<?php


namespace frontend\models\user;


use frontend\models\BaseForm;
use yii\web\UploadedFile;

/**
 * Class UserPanFrom
 * @package frontend\models\user
 *
 * @property null|UploadedFile $panPic
 * @property string $params
 */
class UserPanForm extends BaseForm
{
    public $panPic;
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
            ['panPic', 'required'],
            ['panPic', 'image', 'maxSize' => 5 * 1024 * 1024, 'skipOnEmpty' => true],
        ];
    }
}
<?php

namespace frontend\models;

use yii\base\Model;

abstract class BaseForm extends Model
{
    /**
     * 表单提交和数据库字段的映射表，如 ['uid' => 'user_id']
     * @return array
     */
    abstract function maps(): array;

}

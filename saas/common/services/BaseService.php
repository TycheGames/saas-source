<?php

namespace common\services;

use yii\base\BaseObject;


class BaseService extends BaseObject
{
    protected $result, $error = '';

    public function getResult()
    {
        return $this->result;
    }

    protected function setResult($result)
    {
        $this->result = $result;
    }

    public function getError()
    {
        return $this->error;
    }

    public function setError($message)
    {
        $this->error = $message;
    }

    public function checkRepeat(array $map, $model1, $model2): bool
    {
        if(empty($map)) {
            return true;
        }

        foreach ($map as $item) {
            if($model1->$item != $model2->$item) {
                return false;
            }
        }

        return true;
    }
}

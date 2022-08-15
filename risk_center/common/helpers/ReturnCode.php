<?php

namespace common\helpers;


use yii\base\BaseObject;
use common\models\enum\ErrorCode;

class ReturnCode extends BaseObject
{
    /**
     * @var ErrorCode $code
     */
    protected $code;

    /**
     * @var array $data
     */
    protected $data = [];

    /**
     * @var $return array
     */
    protected $return = [];

    /**
     * @param string $message
     */
    protected function setReturn(string $message = '')
    {
        $this->return = [
            "code"          => $this->code->getValue(),
            "message"       => empty($message) ? $this->code->getKey() : $message,
            "data"          => $this->data,
        ];
    }

    /**
     * @param array $data
     * @return ReturnCode
     */
    public function setData(array $data): ReturnCode
    {
        $this->data = $data;
        return $this;
    }


    /**
     * @param ErrorCode $code
     * @param array $data
     * @return ReturnCode
     */
    public function setR(ErrorCode $code, array $data): ReturnCode
    {
        $this->code = $code;
        $this->data = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function returnOK(): array
    {
        $this->code = ErrorCode::SUCCESS();
        $this->setReturn();
        return $this->return;
    }

    /**
     * @param ErrorCode $code
     * @param string $message
     * @return array
     */
    public function returnFailed(ErrorCode $code, string $message = ''): array
    {
        $this->code = $code;
        $this->setReturn($message);
        return $this->return;
    }

    /**
     * @return array
     */
    public function return(): array
    {
        $this->setReturn();
        return $this->return;
    }

}
<?php


namespace common\services\order;

use common\models\RiskOrder;

class OrderExtraService
{
    /**
     * @var RiskOrder $order
     */
    private $order;

    public function __construct(RiskOrder $order)
    {
        $this->order = $order;
    }

    public function getInfoOrder()
    {
        return $this->order->infoOrder;
    }

    public function getInfoUser()
    {
        return $this->order->infoUser;
    }

    public function getInfoDevice()
    {
        return $this->order->infoDevice;
    }

    public function getInfoPictureMetadata()
    {
        return $this->order->infoPictureMetadata;
    }
}
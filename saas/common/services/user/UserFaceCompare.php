<?php


namespace common\services\user;


use Carbon\Carbon;
use common\services\BaseService;

class UserFaceCompare extends BaseService implements IThirdDataService
{
    public function checkDataExpired(Carbon $updateTime): bool
    {
        return $updateTime->floatDiffInMinutes(Carbon::now()) > 60;
    }
}
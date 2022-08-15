<?php


namespace common\services\user;


use Carbon\Carbon;

interface IThirdDataService
{
    public function checkDataExpired(Carbon $updateTime):bool;
}
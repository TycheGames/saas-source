<?php

namespace common\services\personal_center;

use common\models\package\PackageSetting;
use common\models\personal_center\PersonalCenter;
use common\services\BaseService;
use common\services\FileStorageService;
use common\services\package\PackageService;
use Yii;

class PersonalCenterService extends BaseService
{
    public static $sPackageName;

    public function __construct(array $data)
    {
        self::$sPackageName = $data['packageName'];
    }


    /**
     * 获取个人中心列表
     * @param bool $isLogin
     * @return array
     */
    public function getPersonalCenterList(bool $isLogin = false) : array
    {
        $oPersonalCenter = PersonalCenter::find();

        if (!empty(self::$sPackageName)) {
            $oPackage = new PackageService(self::$sPackageName);
            $oPackage = $oPackage->getPackageSetting();
            if (!empty($oPackage)) {
                //取消根据审核开关判断逻辑
//                if ($oPackage->is_google_review == PackageSetting::GOOGLE_REVIEW_OPEN) {
                if (false) {
                    $oPersonalCenter->where(['is_google_review' => PersonalCenter::GOOGLE_REVIEW_OPEN]);
                } else {
                    $oPersonalCenter->where(['is_google_review' => PersonalCenter::GOOGLE_REVIEW_CLOSE]);
                }
                $oPersonalCenter = $oPersonalCenter->andWhere(['package_setting_id'=>$oPackage->id]);
            }
        } else {
            return [];
        }

        $fileService = new FileStorageService();
        if (!empty($isLogin)) {
            $result = $oPersonalCenter->select('icon, title, path, is_finish_page, jump_page')->orderBy('sorting desc')->asArray()->all();
            foreach ($result as &$item) {
                $item['icon'] = $fileService->getSignedUrl($item['icon']);
                $item['jump'] = [
                    'path'         => $item['path'],
                    'isFinishPage' => $item['is_finish_page'] == 0 ? false : true,
                    'url'          => $item['jump_page'],
                ];
                unset($item['path'], $item['is_finish_page'], $item['jump_page']);
            }
        } else {
            $result = $oPersonalCenter->select('icon, title')->orderBy('sorting desc')->asArray()->all();
            foreach ($result as &$item) {
                $item['icon'] = $fileService->getSignedUrl($item['icon']);
                $item['jump'] = [
                    'path' => '/user/login'
                ];
            }
        }

        return $result;

    }// END getPersonalCenterList

}// END CLASS
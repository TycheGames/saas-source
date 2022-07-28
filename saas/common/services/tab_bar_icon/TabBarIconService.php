<?php

namespace common\services\tab_bar_icon;

use common\models\package\PackageSetting;
use common\models\tab_bar_icon\TabBarIcon;
use common\services\BaseService;
use common\services\FileStorageService;
use common\services\package\PackageService;
use Yii;

class TabBarIconService extends BaseService
{
    /**
     * @param null $sPackage
     * @return array
     * @throws \OSS\Core\OssException
     */
    public static function getTabBarIconList($sPackage = null) : array
    {
        if (empty($sPackage)) {
            return [];
        }

        $oPackage = new PackageService($sPackage);
        $oPackage = $oPackage->getPackageSetting();
        if (!empty($oPackage)) {
            //取消根据审核开关判断逻辑
//            if ($oPackage->is_google_review == PackageSetting::GOOGLE_REVIEW_OPEN) {
            if (false) {
                $query = TabBarIcon::find()
                    ->where(['package_setting_id'=>$oPackage->id])
                    ->andWhere(['is_google_review' => TabBarIcon::GOOGLE_REVIEW_OPEN]);
            } else {
                $query = TabBarIcon::find()
                    ->where(['package_setting_id'=>$oPackage->id])
                    ->andWhere(['is_google_review' => TabBarIcon::GOOGLE_REVIEW_CLOSE]);
            }
            $arrTabBarIcon = $query->asArray()->all();
            $fileService = new FileStorageService();

            foreach ($arrTabBarIcon as &$item) {
                $item['normal_img'] = $fileService->getSignedUrl($item['normal_img']);
                $item['select_img'] = $fileService->getSignedUrl($item['select_img']);
            }

            return array_column($arrTabBarIcon, null, 'type');

        } else {
            return [];
        }

    }// END getTabBarIconList

}// END CLASS
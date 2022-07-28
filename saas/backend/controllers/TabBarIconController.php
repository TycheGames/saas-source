<?php

namespace backend\controllers;

use common\models\package\PackageSetting;
use common\models\tab_bar_icon\TabBarIcon;
use yii\data\ActiveDataProvider;
use yii;
use yii\web\UploadedFile;
use common\services\FileStorageService;

class TabBarIconController extends BaseController
{

    public function actionIndex()
    {
        $oTabBarIcon = TabBarIcon::find();
        $arrPackage  = PackageSetting::getPackageMap();

        if (!empty($_GET['package_setting_id'])) {
            $oTabBarIcon = $oTabBarIcon->where(['package_setting_id'=>$_GET['package_setting_id']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $oTabBarIcon,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'package'      => $arrPackage
        ]);

    }// END actionIndex


    /**
     * add
     * @return string|yii\web\Response
     */
    public function actionAdd()
    {
        $oTabBarIcon = new TabBarIcon();

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            // 上传Icon图片，因为在编辑的时候不上传图片会报错，所以未将此字段添加到模型规则里
            $oNormalImg = UploadedFile::getInstance($oTabBarIcon,'normal_img');
            $oSelectImg = UploadedFile::getInstance($oTabBarIcon,'select_img');
            if ($oNormalImg && $oSelectImg) {
                $oService = new FileStorageService();
                $arrParams['TabBarIcon']['normal_img'] = $oService->uploadFile(
                    'india/backend',
                    $oNormalImg->tempName,
                    $oNormalImg->getExtension()
                );
                $arrParams['TabBarIcon']['select_img'] = $oService->uploadFile(
                    'india/backend',
                    $oSelectImg->tempName,
                    $oSelectImg->getExtension()
                );
            } else {
                // 自定义未上传图片错误
                if (empty($oNormalImg)) $oTabBarIcon->addError('normal_img',Yii::T('common', 'Please upload Normal img first'));
                if (empty($oSelectImg)) $oTabBarIcon->addError('select_img',Yii::T('common', 'Please upload Select img first'));

                return $this->render('add', [ 'model' => $oTabBarIcon ]);
            }

            if ($oTabBarIcon->load($arrParams) && $oTabBarIcon->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('add', [ 'model' => $oTabBarIcon ]);
            }

        } else {
            return $this->render('add', [ 'model' => $oTabBarIcon ]);
        }

    }// END actionAdd


    /**
     * 编辑
     * @param $id
     * @return string
     */
    public function actionEdit( $id )
    {
        $oTabBarIcon = TabBarIcon::findOne($id);
        $arrPackage  = PackageSetting::getPackageMap();

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            $oNormalImg = UploadedFile::getInstance($oTabBarIcon,'normal_img');
            $oSelectImg = UploadedFile::getInstance($oTabBarIcon,'select_img');

            if ($oNormalImg) {
                $oService = new FileStorageService();
                $arrParams['TabBarIcon']['normal_img'] = $oService->uploadFile(
                    'india/backend',
                    $oNormalImg->tempName,
                    $oNormalImg->getExtension()
                );
            } else {
                $arrParams['TabBarIcon']['normal_img'] = $oTabBarIcon->oldAttributes['normal_img'] ?? '';
            }
            if ($oSelectImg) {
                $oService = new FileStorageService();
                $arrParams['TabBarIcon']['select_img'] = $oService->uploadFile(
                    'india/backend',
                    $oSelectImg->tempName,
                    $oSelectImg->getExtension()
                );
            } else {
                $arrParams['TabBarIcon']['select_img'] = $oTabBarIcon->oldAttributes['select_img'] ?? '';
            }

            if ($oTabBarIcon->load($arrParams) && $oTabBarIcon->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('edit', [
                    'model'   => $oTabBarIcon,
                    'package' => $arrPackage
                ]);
            }
        }

        return $this->render('edit', [
            'model'   => $oTabBarIcon,
            'package' => $arrPackage
        ]);

    }// END actionEdit


    /**
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete($id)
    {
        $oTabBarIcon = TabBarIcon::findOne($id);

        $oTabBarIcon->delete();

        return $this->redirect(['index']);

    }// END actionDelete

}// END CLASS
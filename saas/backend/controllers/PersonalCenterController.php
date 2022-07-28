<?php

namespace backend\controllers;

use common\models\package\PackageSetting;
use common\models\personal_center\PersonalCenter;
use common\services\FileStorageService;
use Yii;
use yii\data\ActiveDataProvider;
use yii\web\UploadedFile;

class PersonalCenterController extends BaseController
{

    /**
     * 列表
     * @return string
     */
    public function actionIndex()
    {
        $oPersonalCenter = PersonalCenter::find();

        if (!empty($_GET['package_setting_id'])) {
//            $oPackage     = PackageSetting::find();
//            $arrPackageId = $oPackage->select('id')->where(['like', 'name', $_GET['package_name']])->asArray()->all();
//            $arrPackageId = array_column($arrPackageId, 'id');

            $oPersonalCenter = $oPersonalCenter->where(['package_setting_id'=>$_GET['package_setting_id']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $oPersonalCenter,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'package'      => PackageSetting::getPackageMap()
        ]);


    }// END actionIndex


    /**
     * 新增
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $oModel     = new PersonalCenter();
        $arrPackage = PackageSetting::getPackageMap();

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            // 上传Icon图片，因为在编辑的时候不上传图片会报错，所以未将此字段添加到模型规则里
            $oImage = UploadedFile::getInstance($oModel,'icon');
            if ($oImage) {
                $oService = new FileStorageService();
                $arrParams['PersonalCenter']['icon'] = $oService->uploadFile(
                    'india/backend',
                    $oImage->tempName,
                    $oImage->getExtension()
                );
            } else {
                // 自定义未上传图片错误
                $oModel->addError('icon',Yii::T('common', 'Please upload Icon first'));
                return $this->render('add', [
                    'model'   => $oModel,
                    'package' => $arrPackage
                ]);
            }
            $arrParams['PersonalCenter']['user_id']    = Yii::$app->user->id;
            $arrParams['PersonalCenter']['created_at'] = date('Y-m-d H:i:s');

            if ($oModel->load($arrParams) && $oModel->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('add', [
                    'model'   => $oModel,
                    'package' => $arrPackage
                ]);
            }
        } else {
            return $this->render('add', [
                'model'   => $oModel,
                'package' => $arrPackage
            ]);
        }
    }// END actionAdd


    /**
     * 编辑
     * @param $id
     * @return string
     */
    public function actionEdit( $id )
    {
        $oModel     = PersonalCenter::findOne($id);
        $arrPackage = PackageSetting::getPackageMap();

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            $oImage = UploadedFile::getInstance($oModel,'icon');
            if ($oImage) {
                $oService = new FileStorageService();
                $arrParams['PersonalCenter']['icon'] = $oService->uploadFile(
                    'india/backend',
                    $oImage->tempName,
                    $oImage->getExtension()
                );
            } else {
                $arrParams['PersonalCenter']['icon'] = $oModel->oldAttributes['icon'] ?? '';
            }
            $arrParams['PersonalCenter']['user_id'] = Yii::$app->user->id;

            if ($oModel->load($arrParams) && $oModel->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('edit', [
                    'model'   => $oModel,
                    'package' => $arrPackage
                ]);
            }
        }

        return $this->render('edit', [
            'model'   => $oModel,
            'package' => $arrPackage
        ]);

    }// END actionEdit


    /**
     * 删除
     * @param $id
     * @return \yii\web\Response
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionDelete( $id )
    {
        $oTabBarIcon = PersonalCenter::findOne($id);

        $oTabBarIcon->delete();

        return $this->redirect(['index']);

    }// END actionDelete


}// END CLASS
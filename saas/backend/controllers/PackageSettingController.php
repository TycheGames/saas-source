<?php

namespace backend\controllers;

use backend\models\Merchant;
use common\models\package\PackageSetting;
use common\models\pay\PayAccountSetting;
use yii\data\ActiveDataProvider;
use Yii;

class PackageSettingController extends BaseController
{

    /**
     * 包列表
     * @return string
     */
    public function actionIndex()
    {
        $oPackageSetting = PackageSetting::find();

        if (!empty($_GET['package_name'])) {
            $oPackageSetting = $oPackageSetting->where(['like', 'package_name', $_GET['package_name']]);
        }

        $dataProvider = new ActiveDataProvider([
            'query'      => $oPackageSetting,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('index', [
            'dataProvider'       => $dataProvider,
            'arrCreditAccountId' => self::getCreditAccountId()
        ]);

    }// END actionIndex


    /**
     * 新增
     * @return string|\yii\web\Response
     */
    public function actionAdd()
    {
        $oModel = new PackageSetting();

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            if ($oModel->load($arrParams) && $oModel->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('add', [
                    'model'              => $oModel,
                    'arrCreditAccountId' => self::getCreditAccountId(),
                    'arrMerchantId'      => self::getMerchantId()
                ]);
            }

        } else {
            return $this->render('add', [
                'model'              => $oModel,
                'arrCreditAccountId' => self::getCreditAccountId(),
                'arrMerchantId'      => self::getMerchantId()
            ]);
        }
    }// END actionAdd


    /**
     * 编辑
     * @param $id
     * @return string|\yii\web\Response
     */
    public function actionEdit( $id )
    {
        $oModel = PackageSetting::findOne($id);

        if (Yii::$app->request->isPost)
        {
            $arrParams = Yii::$app->request->post();

            if ($oModel->load($arrParams) && $oModel->save()) {
                return $this->redirect(['index']);
            } else {
                return $this->render('edit', [
                    'model'              => $oModel,
                    'arrCreditAccountId' => self::getCreditAccountId(),
                    'arrMerchantId'      => self::getMerchantId()
                ]);
            }
        }

        return $this->render('edit', [
            'model'              => $oModel,
            'arrCreditAccountId' => self::getCreditAccountId(),
            'arrMerchantId'      => self::getMerchantId()
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
        $oPackageSetting = PackageSetting::findOne($id);

        $oPackageSetting->delete();

        return $this->redirect(['index']);

    }// END actionDelete


    /**
     * getMerchantId
     * @return array
     */
    private function getMerchantId() : array
    {
        $arrMerchantId = Merchant::getMerchantId();

        if (isset($arrMerchantId)) {
            unset($arrMerchantId[0]);
            return $arrMerchantId;
        } else {
            return [];
        }

    }// END getMerchantId


    /**
     * 获取CreditAccountId
     * @return array
     */
    private function getCreditAccountId() : array
    {
        // $oPayAccountSetting = new PayAccountSetting();
        $arrCreditAccountId = PayAccountSetting::getAccountList($this->merchantIds, PayAccountSetting::SERVICE_TYPE_KUDOS_CREDIT);

        if (!empty($arrCreditAccountId)) {
            return $arrCreditAccountId;
        } else {
            return [];
        }

    }// END getCreditAccountId

}// END CLASS
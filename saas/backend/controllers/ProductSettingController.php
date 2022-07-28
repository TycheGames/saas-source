<?php
/**
 * Created by PhpStorm.
 * User: fiver
 * Date: 2017/11/6
 * Time: 15:50
 */
namespace backend\controllers;
use common\models\package\PackageSetting;
use common\models\product\ProductPeriodSetting;
use common\models\product\ProductSetting;
use yii\data\Pagination;
use yii\helpers\Url;
use Yii;
use yii\web\ForbiddenHttpException;

/**
 * Banner controller
 */
class ProductSettingController extends BaseController
{

//    public function beforeAction($action)
//    {
//        if(parent::beforeAction($action))
//        {
//            if(Yii::$app->user->identity->merchant_id){
//                throw new ForbiddenHttpException(Yii::T('common', 'Insufficient permissions'));
//            }
//            return true;
//        }else{
//            return false;
//        }
//    }

    protected $product_is_top = 'index-product-setting';
    private $line = 15; //每页行数
    /**
     *首页产品设置列表页
     * @name  Product list
     * @name-CN  系统管理-系统配置-产品要素综合设置
     */
    public function actionSettingList(){

        $key = $this->product_is_top;
        $redis = Yii::$app->redis;

        $is_top = 1;
        if($redis->EXISTS($key)) {
            $is_top = $redis->GET($key);
        }


        $query = ProductSetting::find()->where(['merchant_id' => $this->merchantIds]);
        if (!$this->isNotMerchantAdmin) {
            $query->andWhere(['is_internal' => ProductSetting::IS_EXTERNAL_NO]);
        }
        $count = $query->count();
        $pages = new Pagination(['totalCount' => $count]);
        $pages->pageSize = $this->request->get('per-page', 15);

        $product_setting = $query->offset($pages->offset)
            ->limit($pages->limit)
            ->orderBy(['id' => SORT_DESC])->all();
        return $this->render('setting-list',
            [
                'product_setting' => $product_setting,
                'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
                'count' => $count,
                'pages' => $pages,
                'is_top' => $is_top,
            ]
        );
    }

    /**
     * @name 首页产品新增
     * @return string
     */
    public function actionSettingAdd()
    {
        $oProduct = new ProductSetting();

        if (yii::$app->request->isPost)
        {
            if ($oProduct->load($this->request->post()))
            {
                if (!$this->isNotMerchantAdmin) {
                    $oProduct->merchant_id = $this->merchantIds;
                    $oProduct->is_internal = ProductSetting::IS_EXTERNAL_NO;
                    $oProduct->delay_status = ProductSetting::STATUS_DELAY_CLOSE;
                }

                if ($oProduct->validate()) {
                    $oProduct->opreate_name = \Yii::$app->user->identity->username;
                    if($oProduct->save()){
                        return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS, Url::toRoute(['product-setting/setting-list']));
                    }
                }
            }
        }
        return $this->render('setting-add',[
            'model'         => $oProduct,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'periodList' => ProductPeriodSetting::findPeriodSetting($this->merchantIds, $this->isNotMerchantAdmin),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }// END actionSettingAdd


    private function settingAddCommon($isInternal){
        $model = new ProductSetting();
        if($this->request->isPost){
            $model->load($this->request->post());
            if(!$this->isNotMerchantAdmin)
            {
                $model->merchant_id = $this->merchantIds;
            }
            $model->is_internal = $isInternal;
            if($model->validate()){
                $model->opreate_name = \Yii::$app->user->identity->username;
                if($model->save()){
                    return $this->redirectMessage(Yii::T('common', 'Add success'), self::MSG_SUCCESS, Url::toRoute(['product-setting/setting-list']));
                }
            }
        }

        return $this->render('setting-edit',[
            'model'         => $model,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'periodList' => ProductPeriodSetting::findPeriodSetting($this->merchantIds, $this->isNotMerchantAdmin),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name 产品编辑
     * @param $id
     * @return string
     */
    public function actionSettingEdit($id)
    {
        $oProduct = ProductSetting::findOne($id);


        if (yii::$app->request->isPost)
        {
            if ($oProduct->load($this->request->post()))
            {
                if (!$this->isNotMerchantAdmin) {
                    $oProduct->merchant_id = $this->merchantIds;
                    $oProduct->is_internal = ProductSetting::IS_EXTERNAL_NO;
                }

                if ($oProduct->validate()) {
                    $oProduct->opreate_name = \Yii::$app->user->identity->username;
                    if ($oProduct->save()) {
                        return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute(['product-setting/setting-list']));
                    }
                }
            }
        }

        return $this->render('setting-add',[
            'model'         => $oProduct,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'periodList' => ProductPeriodSetting::findPeriodSetting($this->merchantIds, $this->isNotMerchantAdmin),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }// END actionSettingEdit


    private function settingEditCommon($isInternal){
        $id = $this->request->get('id');
        $model = ProductSetting::findOne(['id' => $id, 'merchant_id' => $this->merchantIds]);

        if($this->request->isPost){
            $model->load($this->request->post());
            if(!$this->isNotMerchantAdmin)
            {
                $model->merchant_id = $this->merchantIds;
            }
            $model->is_internal = $isInternal;
            $model->opreate_name = \Yii::$app->user->identity->username;

            if( $model->validate() && $model->save()){
                return $this->redirectMessage(Yii::T('common', 'Update success'), self::MSG_SUCCESS, Url::toRoute(['product-setting/setting-list']));
            }
        }

        return $this->render('setting-edit',[
            'model'           => $model,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'periodList' => ProductPeriodSetting::findPeriodSetting($this->merchantIds, $this->isNotMerchantAdmin),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }





    /**
     * @name Type list
     * @name-CN 产品类型
     * @return string
     */
    public function actionPeriodSettingList()
    {
        $query = ProductPeriodSetting::find()->where(['merchant_id' => $this->merchantIds]);
        if (!$this->isNotMerchantAdmin) {
            $query->andWhere(['is_internal' => ProductPeriodSetting::IS_EXTERNAL_NO]);
        }

        $createAtBegin = strtotime(Yii::$app->request->get('create_at_begin'));
        $createAtEnd   = strtotime(Yii::$app->request->get('create_at_end'));
        if ($createAtBegin && $createAtEnd) {
            $query->andWhere(['>=', 'created_at', $createAtBegin])->andWhere(['<=', 'created_at', $createAtEnd]);
        }

        $pages = new Pagination(['totalCount' => $query->count()]);
        $pages->pageSize = $this->request->get('per-page', $this->line);
        $list = $query->offset($pages->offset)->limit($pages->limit)->all();

        return $this->render('period-setting-list', [
            'list' => $list,
            'pages' => $pages,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);
    }

    /**
     * @name Adding type
     * @name-CN 添加产品类型
     * @return string
     */
    public function actionPeriodSettingAdd()
    {
        $model = new ProductPeriodSetting();

        if($this->request->isPost)
        {
            $model->load($this->request->post());
            if(!$this->isNotMerchantAdmin)
            {
                $model->merchant_id = $this->merchantIds;
                $model->is_internal = ProductPeriodSetting::IS_EXTERNAL_NO;
            }

            $model->periods = 1;
            $model->operator_name = \Yii::$app->user->identity->username;
            if ($model->validate()) {
                if($model->save()) {
                    return $this->redirectMessage('Add success',self::MSG_SUCCESS, Url::toRoute('period-setting-list'));
                } else {
                    return $this->redirectMessage('Add fail',self::MSG_ERROR);
                }
            }
        }


        return $this->render('period-setting-add', [
            'data' => [],
            'model' => $model,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ]);
    }

    /**
     * @name Editing type
     * @name-CN 编辑产品类型
     * @return string
     */
    public function actionPeriodSettingEdit()
    {
        $data = [];
        $id = intval($this->request->get('id', 0));
        /** @var ProductPeriodSetting $model */
        $model = ProductPeriodSetting::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds])->one();
        if (empty($model)) {
            return $this->redirectMessage('Product does not exist',self::MSG_ERROR);
        }
        if($this->request->isPost)
        {
            $model->load($this->request->post());
            $model->periods = 1;
            $model->operator_name = \Yii::$app->user->identity->username;
            if(!$this->isNotMerchantAdmin)
            {
                $model->merchant_id = $this->merchantIds;
                $model->is_internal = ProductPeriodSetting::IS_EXTERNAL_NO;
            }
            if ( $model->validate()) {
                if($model->save()) {
                    return $this->redirectMessage('Update success',self::MSG_SUCCESS, Url::toRoute('period-setting-list'));
                } else {
                    return $this->redirectMessage('Add fail',self::MSG_ERROR);
                }
            }
        }


        return $this->render('period-setting-edit', [
            'data' => $data,
            'model' => $model,
            'packageSetting' => PackageSetting::findPackageSetting($this->merchantIds),
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ]);
    }



    /**
     * @name 产品管理-删除产品类型
     * @param int $id
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionPeriodSettingDel($id)
    {

        $model = ProductPeriodSetting::findOne(['id' => intval($id), 'merchant_id' => $this->merchantIds]);
        if ($model->delete()) {
            return $this->redirectMessage('Delete successful', self::MSG_SUCCESS, Url::toRoute('period-setting-list'));
        } else {
            return $this->redirectMessage('Delete failed', self::MSG_ERROR);
        }
    }


    /**
     * @name 产品管理-删除产品设置
     * @param int $id
     * @return string
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    public function actionProductSettingDel($id)
    {
        $model = ProductSetting::findOne(['id' => intval($id), 'merchant_id' => $this->merchantIds]);
        if ($model->delete()) {
            return $this->redirectMessage('Delete successful', self::MSG_SUCCESS, Url::toRoute('setting-list'));
        } else {
            return $this->redirectMessage('Delete failed', self::MSG_ERROR);
        }
    }
}
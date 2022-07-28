<?php

namespace backend\controllers;

use common\helpers\CommonHelper;
use common\helpers\RedisQueue;
use common\models\fund\LoanFund;
use common\models\fund\LoanFundDayQuota;
use common\models\fund\LoanFundOperateLog;
use common\models\package\PackageSetting;
use common\models\pay\PayAccountSetting;
use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\web\NotFoundHttpException;
use yii\web\Response;


/**
 * 借款资金管理
 */
class LoanFundController extends BaseController
{


    /**
     * @name  LoanFundController 资方管理-资方管理-资方列表
     * Lists all LoanFund models.
     * @ name 资方管理-资方列表
     * @return mixed
     */
    public function actionIndex()
    {

        $conditions = [
            'merchant_id' => $this->merchantIds
        ];

        $query = LoanFund::find()->where($conditions)->andWhere(['show' => LoanFund::SHOW_YES])->orderBy(['score'=>SORT_DESC]);


        $merchantID = intval(yii::$app->request->get('merchant_id'));
        if(!empty($merchantID))
        {
            $query->andWhere(['merchant_id' =>$merchantID ]);
        }
        $query = [ 'query' => $query];
        $dataProvider = new ActiveDataProvider($query);

        $payAccountList = PayAccountSetting::getAccountList($this->merchantIds, PayAccountSetting::SERVICE_TYPE_RAZORPAY);
        $loanAccountList = PayAccountSetting::getAccountList($this->merchantIds, [PayAccountSetting::SERVICE_TYPE_KUDOS, PayAccountSetting::SERVICE_TYPE_AGLOW]);

        return $this->render('index', [
            'dataProvider' => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'payAccountList' => $payAccountList,
            'loanAccountList' => $loanAccountList
        ]);
    }

    /**
     * @name LoanFundController 每日限额列表
     * @return string
     * @throws \yii\db\Exception
     */
    public function actionDayQuotaList() {
        $loanFund = LoanFund::find()->select(['id'])->where(['show' => LoanFund::SHOW_YES])->all();
        $ids = ArrayHelper::getColumn($loanFund, 'id');
        if(empty($ids))
        {
            $ids = [];
        }
        $dayQuotaQuery = LoanFundDayQuota::find()->where(['merchant_id' => $this->merchantIds, 'fund_id' => $ids]);
        $merchantID = intval(yii::$app->request->get('merchant_id'));
        if(!empty($merchantID))
        {
            $dayQuotaQuery->andWhere(['merchant_id' =>$merchantID ]);
        }
        $cloneDayQuotaQuery = clone $dayQuotaQuery;
        $pagination = new \yii\data\Pagination([
            'totalCount'=> $cloneDayQuotaQuery->count()
        ]);

        $rows = $dayQuotaQuery->orderBy(['id' => SORT_DESC])->limit($pagination->getLimit())->offset($pagination->getOffset())->all();
        $fundList = LoanFund::getAllFundArray($this->merchantIds);

        return $this->render('day-quota-list', [
            'rows' => $rows,
            'pagination'=>$pagination,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'fundList' => $fundList
        ]);
    }


    /**
     * @name LoanFundController 更新每日配额
     * @param $id
     * @param null $return_url
     * @return string|\yii\web\Response
     */
    public function actionUpdateDayQuota($id, $return_url=null) {
        /** @var LoanFundDayQuota $model */
        $model = LoanFundDayQuota::find()->where(['id' => $id, 'merchant_id' => $this->merchantIds ])->one();
        if(is_null($model))
        {
            return false;
        }
        $model->quota = intval(CommonHelper::CentsToUnit($model->quota));
        $model->remaining_quota = intval(CommonHelper::CentsToUnit($model->remaining_quota));
        if(yii::$app->request->isPost)
        {
            $params = Yii::$app->getRequest()->post();
            $params['LoanFundDayQuota']['remaining_quota'] = CommonHelper::UnitToCents($params['LoanFundDayQuota']['remaining_quota']);
            $params['LoanFundDayQuota']['quota'] = CommonHelper::UnitToCents($params['LoanFundDayQuota']['quota']);

            if(!$this->isNotMerchantAdmin)
            {
                $params['LoanFundDayQuota']['merchant_id'] = $this->merchantIds;
            }

            if($model->load($params) && $model->validate() && $model->save()) {
                if($return_url) {
                    return $this->redirect($return_url);
                } else {
                    return $this->redirect(['day-quota-list']);
                }
            }
        }

        $fund_options = LoanFund::getAllFundArray($this->merchantIds);
        return $this->render('update-day-quota',[
            'model'=>$model,
            'fund_options'=>$fund_options
        ]);
    }

    /**
     * @name LoanFundController 更新每日配额
     * @param null $return_url
     * @return string|\yii\web\Response
     */
    public function actionAddDayQuota($return_url=null) {
        $model = new LoanFundDayQuota();

        if(yii::$app->request->isPost)
        {
            $model->load(Yii::$app->getRequest()->post());
            if(!$this->isNotMerchantAdmin)
            {
                $model->merchant_id = $this->merchantIds;
            }
            if($model->validate() && $model->save()) {

                $arrParams = Yii::$app->getRequest()->post('LoanFundDayQuota');

                // 新增资方操作日志
                LoanFundOperateLog::addOperateLog($arrParams['fund_id'], $arrParams, 1, LoanFundOperateLog::ACTION_ADD);

                if($return_url) {
                    return $this->redirect($return_url);
                } else {
                    return $this->redirect(['day-quota-list']);
                }
            }
        }


        $fund_options = LoanFund::getAllFundArray($this->merchantIds);
        return $this->render('update-day-quota',[
            'model'=>$model,
            'fund_options'=>$fund_options,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ]);
    }



    /**
     * @name LoanFundController 创建资方
     * If creation is successful, the browser will be redirected to the 'view' page.
     * @return mixed
     */
    public function actionCreate()
    {
        // 只有非商户系统管理员才可以创建资方
        if (empty($this->isNotMerchantAdmin)) {
            return $this->redirect(['index']);
        }

        $model = new LoanFund();

        $payAccountList = PayAccountSetting::getAccountList($this->merchantIds, PayAccountSetting::SERVICE_TYPE_RAZORPAY);
        $loanAccountList = PayAccountSetting::getAccountList($this->merchantIds, [PayAccountSetting::SERVICE_TYPE_KUDOS, PayAccountSetting::SERVICE_TYPE_AGLOW]);

        if (yii::$app->request->isPost)
        {
            $params = Yii::$app->request->post();
            $params['LoanFund']['day_quota_default'] = CommonHelper::UnitToCents($params['LoanFund']['day_quota_default']);
            //如果是商户管理员，强制写入当前管理员的商户id，以防止权限问题
            if(!$this->isNotMerchantAdmin)
            {
                $params['LoanFund']['merchant_id'] = Yii::$app->user->identity->merchant_id;
            }

            if ($model->load($params) && $model->save()) {

                // 新增资方操作日志
                LoanFundOperateLog::addOperateLog(0, $params['LoanFund'], 0, LoanFundOperateLog::ACTION_ADD);

                /** @var PackageSetting $oPackage */
//                $oPackage = PackageSetting::find()->select('package_name')->where(['merchant_id'=>$model->merchant_id])->one();
//                $fundStatusKey = 'fund_status_' . $oPackage->package_name;
//                RedisQueue::set(['key'=>$fundStatusKey,'value'=>$model->status]);
                return $this->redirect(['index']);
            }
        }

        return $this->render('create', [
            'model' => $model,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'payAccountList' => $payAccountList,
            'loanAccountList' => $loanAccountList
        ]);



    }

    /**
     * @name LoanFundController 更新资方
     * @param $id
     * @return string|\yii\web\Response
     * @throws NotFoundHttpException
     */
    public function actionUpdate($id)
    {
        /** @var LoanFund $model */
        $model = LoanFund::find()->where(['id' => $id , 'merchant_id' => $this->merchantIds])->one();
        if(is_null($model)){
            return $this->redirectMessage('Non-existent or non-editable', self::MSG_ERROR);
        }
        $payAccountList = PayAccountSetting::getAccountList($this->merchantIds, PayAccountSetting::SERVICE_TYPE_RAZORPAY);
        $loanAccountList = PayAccountSetting::getAccountList($this->merchantIds, [PayAccountSetting::SERVICE_TYPE_KUDOS, PayAccountSetting::SERVICE_TYPE_AGLOW]);

        if(yii::$app->request->isPost)
        {
            $params = Yii::$app->request->post();
            $params['LoanFund']['day_quota_default'] = CommonHelper::UnitToCents($params['LoanFund']['day_quota_default']);
            //如果是商户管理员，强制写入当前管理员的商户id，以防止权限问题
            if(!$this->isNotMerchantAdmin)
            {
                $params['LoanFund']['merchant_id'] = Yii::$app->user->identity->merchant_id;
            }

            if ($model->load($params) && $model->save()) {

                // 新增资方操作日志
                LoanFundOperateLog::addOperateLog($id, $params['LoanFund'], 0, LoanFundOperateLog::ACTION_UPDATE);
                return $this->redirectMessage('success', self::MSG_SUCCESS, Url::toRoute('loan-fund/index', 'https'));

            }
        }

        $model->day_quota_default = intval(CommonHelper::CentsToUnit($model->day_quota_default));
        return $this->render('update', [
            'model' => $model,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'payAccountList' => $payAccountList,
            'loanAccountList' => $loanAccountList
        ]);


    }



    /**
     * @name - 总资方每日配额
     */
    public function actionTotalFundDayList()
    {
        $arrConditions = [
            'merchant_id' => $this->merchantIds
        ];

        $oLoanFund = LoanFund::find()->where($arrConditions)->andWhere(['show' => LoanFund::SHOW_YES]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $oLoanFund,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('total-fund-day-list', [
            'dataProvider' => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }// END actionTotalFundDayList


    /**
     * 更新资方每日配额
     * @return string|\yii\web\Response
     */
    public function actionTotalFundDayEdit($id)
    {
        $oLoanFund = LoanFund::findOne($id);

        $list = LoanFundDayQuota::find()->where([
            'fund_id' => $id,
            'date' => date('Y-m-d'),
            'type' => [LoanFundDayQuota::TYPE_OLD,LoanFundDayQuota::TYPE_REAL_OLD]
        ])->all();
        foreach ($list as $item)
        {
            switch ($item->type){
                case LoanFundDayQuota::TYPE_OLD:
                    $oLoanFund->all_old_customer_proportion = $item->pr;
                    break;
                case LoanFundDayQuota::TYPE_REAL_OLD:
                    $oLoanFund->old_customer_proportion = $item->pr;
                    break;
            }
        }

        if (Yii::$app->request->isPost)
        {
            // 更改每日配额资方必须为禁用状态
            if (LoanFund::STATUS_ENABLE == $oLoanFund->status) {
                return $this->redirectMessage(Yii::T('common', 'please disable using fund'), self::MSG_ERROR, Url::toRoute(['loan-fund/total-fund-day-list']));
            }
            $arrParams = Yii::$app->request->post();

            $arrParams['LoanFund']['day_quota_default'] =  CommonHelper::UnitToCents($arrParams['LoanFund']['day_quota_default']);

            if ($oLoanFund->load($arrParams) && $oLoanFund->validate()) {

                // 调整资方额度分配
                if ($oLoanFund->adjustFundDayQuota()) {
                    return $this->redirect(Url::to('loan-fund/index', CommonHelper::getScheme()));
                } else {
                    return $this->redirectMessage(Yii::T('common', 'Update fail'), self::MSG_ERROR, Url::toRoute(['loan-fund/total-fund-day-list']));
                }

            } else {
                return $this->render('total-fund-day-edit', [ 'model' => $oLoanFund, 'isNotMerchantAdmin' => $this->isNotMerchantAdmin ]);
            }
        } else {
            return $this->render('total-fund-day-edit', [ 'model' => $oLoanFund, 'isNotMerchantAdmin' => $this->isNotMerchantAdmin ]);
        }

    }// END actionTotalFundDayEdit


    /**
     * 资方操作日志
     */
    public function actionFundLog()
    {
        $oLoanFundOperateLog = LoanFundOperateLog::find()->where(['status'=>LoanFundOperateLog::STATUS_FUND_QUOTA])->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $oLoanFundOperateLog,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $payAccountList = PayAccountSetting::getAccountList($this->merchantIds, PayAccountSetting::SERVICE_TYPE_RAZORPAY);
        $loanAccountList = PayAccountSetting::getAccountList($this->merchantIds, [PayAccountSetting::SERVICE_TYPE_KUDOS, PayAccountSetting::SERVICE_TYPE_AGLOW]);

        return $this->render('fund-log', [
            'dataProvider'       => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
            'payAccountList'     => $payAccountList,
            'loanAccountList'    => $loanAccountList,
        ]);

    }// END actionFundOperateLog

    /**
     * 资方指定日期配额操作日志
     */
    public function actionDateSpecifiedQuotasLog()
    {
        $oLoanFundOperateLog = LoanFundOperateLog::find()->where(['status'=>LoanFundOperateLog::STATUS_FUND_DAY_QUOTA])->orderBy('created_at desc');

        $dataProvider = new ActiveDataProvider([
            'query'      => $oLoanFundOperateLog,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('date-specified-quotas-log', [
            'dataProvider'       => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin
        ]);

    }// END actionDateSpecifiedQuotasLog



    /**
     * 资方当日配额操作日志
     */
    public function actionDayQuotaLog()
    {
        $oLoanFundOperateLog = LoanFundOperateLog::find()->where(['status'=>LoanFundOperateLog::STATUS_FUND_TOTAL_DAY_QUOTA])->orderBy('created_at desc');

        $dataProvider = new ActiveDataProvider([
            'query'      => $oLoanFundOperateLog,
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('day-quota-log', [
            'dataProvider'       => $dataProvider,
            'isNotMerchantAdmin' => $this->isNotMerchantAdmin,
        ]);

    }// END actionDayQuotaLog


    public function actionHidden($id)
    {
        yii::$app->response->format = Response::FORMAT_JSON;

        /** @var LoanFund $loanFund */
        $loanFund = LoanFund::find()
            ->where(['id' => intval($id)])
            ->andWhere(['merchant_id' => $this->merchantIds])
            ->one();

        if(is_null($loanFund))
        {
            return [
                'code' => -1,
                'msg' => '资方不存在'
            ];
        }

        if(LoanFund::STATUS_DISABLE != $loanFund->status)
        {
            return [
                'code' => -1,
                'msg' => '请先禁用资方'
            ];
        }

        $loanFund->show = LoanFund::SHOW_NO;
        $loanFund->save();

        return [
            'code' => 0,
            'msg' => 'success'
        ];

    }
}

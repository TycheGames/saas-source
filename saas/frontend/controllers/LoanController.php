<?php

namespace frontend\controllers;

use common\exceptions\UserExceptionExt;
use common\helpers\RedisQueue;
use common\models\enum\ErrorCode;
use common\models\financial\FinancialPaymentOrder;
use common\models\pay\PayAccountSetting;
use common\models\pay\RazorpayAccountForm;
use common\models\user\UserRegisterInfo;
use common\services\loan\LoanService;
use common\services\order\OrderService;
use common\services\product\ProductService;
use common\services\repayment\CustomerReductionService;
use common\services\repayment\RepaymentService;
use common\services\user\UserCouponService;
use common\services\user\UserCreditLimitService;
use common\services\user\UserService;
use frontend\models\loan\ApplyDrawExportForm;
use frontend\models\loan\ApplyDrawForm;
use frontend\models\loan\ApplyLoanForm;
use frontend\models\loan\ApplyReductionForm;
use frontend\models\loan\ConfirmLoanV2Form;
use frontend\models\loan\GetTransferDataForm;
use frontend\models\loan\LoanDetailForm;
use frontend\models\loan\LoanOrderListForm;
use frontend\models\loan\OrderBindCardExportForm;
use frontend\models\loan\OrderBindCardForm;
use frontend\models\loan\OrderStatusForm;
use frontend\models\loan\PushOrderUserCheckForm;
use frontend\models\loan\RepayByBankTransfer;
use frontend\models\loan\RepaymentApplyExportForm;
use frontend\models\loan\RepaymentApplyForm;
use frontend\models\loan\RepaymentResultForm;
use frontend\models\loan\UserCreditLimitForm;
use GuzzleHttp\Exception\GuzzleException;
use Razorpay\Api\Api;
use Yii;
use yii\filters\AccessControl;


class LoanController extends BaseController
{

    public function behaviors()
    {
        return [
            'access' => [
                'class'  => AccessControl::class,
                // 除了下面的action其他都需要登录
                'except' => [
                    'repayment-apply-test',
                    'repay-auth',
                    'push-order-user-check',
                    'apply-draw-export',
                    'repayment-apply-export',
                    'order-bind-card-export',
                    'user-credit-limit-export',
                ],
                'rules'  => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }



    /**
     * @name LoanController 下单接口 [loan/apply-loan]
     * @method post
     * @param int amount 借款金额
     * @param int productId 产品ID
     * @param int bankCardId 银行卡ID
     * @param string blackbox 同盾标识
     * @return array
     */
    public function actionApplyLoan()
    {
        $form = new ApplyLoanForm();
        $data = array_merge(Yii::$app->request->post(),[
            'userId' => Yii::$app->user->identity->getId(),
            'clientInfo' => $this->getClientInfo(),
            'packageName' => $this->packageName()
        ]);
        if ($form->load($data, '') && $form->validate()) {
            $service = new LoanService();
            $ret = $service->applyLoan($form);
            if ($ret) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }

        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

    }

    /**
     * @name LoanController 申请提现接口 [loan/apply-draw]
     * @method post
     * @param int amount 借款金额
     * @param int productId 产品ID
     * @param int orderId 订单ID
     * @return array
     */
    public function actionApplyDraw()
    {
        $applyForm = new ApplyDrawForm();
        $userID = Yii::$app->user->identity->getId();
        $clientInfo = $this->getClientInfo();
        $postData = array_merge(Yii::$app->request->post(), [
            'userId'     => $userID,
            'clientInfo' => $clientInfo,
        ]);
        if ($applyForm->load($postData, '') && $applyForm->validate()) {
            $service = new LoanService();
            $ret = $service->applyDraw($applyForm);
            if ($ret) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($applyForm->getErrorSummary(false)));
        }
    }

    public function actionApplyDrawExport()
    {
        $applyForm = new ApplyDrawExportForm();
        $postData = array_merge(Yii::$app->request->post());
        if ($applyForm->load($postData, '') && $applyForm->validate()) {
            $service = new LoanService();
            $ret = $service->applyDrawExport($applyForm);
            if ($ret) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($applyForm->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 借款订单列表 [loan/loan-order-list]
     * @method post
     * @param int page 当前页数
     * @return array
     */
    public function actionLoanOrderList()
    {
        $userId = Yii::$app->user->getId();
        $pageSize = 10;
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => $userId ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'loan_order_list_enter');
        if($userId){
            $key = 'lock_user_access_app_'.$userId;
            if(RedisQueue::lock($key,300)){
                RedisQueue::push([RedisQueue::PUSH_COLLECTION_LAST_ACCESS_USER, $userId]);
            }
        }
        $form = new LoanOrderListForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $list = OrderService::getUserLoanOrderList($userId, $form->page - 1, $pageSize);
            return $this->return->setData($list)->returnOK();
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

    }

    /**
     * @name LoanController 订单详情 [loan/loan-detail]
     * @method post
     * @param integer id 订单ID
     * @return array
     */
    public function actionLoanDetail()
    {
        $userId = Yii::$app->user->getId();
        Yii::info($userId, 'loan_detail_enter');
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => $userId ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'loan_detail_enter_v2');
        if($userId){
            $key = 'lock_user_access_app_'.$userId;
            if(RedisQueue::lock($key,300)){
                RedisQueue::push([RedisQueue::PUSH_COLLECTION_LAST_ACCESS_USER, $userId]);
            }
        }
        $form = new LoanDetailForm();
        if(!$form->load(Yii::$app->request->post(), ''))
        {
            Yii::warning('userId:' . $userId . ', params:' . json_encode($form->toArray(), JSON_UNESCAPED_UNICODE), 'loan_detail_enter');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

        $form->userId = $userId;
        $form->hostInfo  = yii::$app->request->hostInfo;
        if ($form->validate()) {
            $service = new LoanService();
            if ($service->getLoanDetail($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            Yii::warning('userId:' . $userId . ', params:' . json_encode($form->toArray(), JSON_UNESCAPED_UNICODE), 'loan_detail_enter');
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }



    /**
     * @name LoanController 用户还款申请
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param string customerEmail
     * @param string customerPhone
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionRepaymentApply()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'repayment_apply_enter');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    /**
     * @name LoanController 用户还款申请
     * @param integer orderId 订单号
     * @param integer amount 还款金额
     * @param string customerEmail
     * @param string customerPhone
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @param string orderType saas internal
     * @return array
     * @throws GuzzleException
     * @throws UserExceptionExt
     */
    public function actionRepaymentApplyPaymentLink()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'repayment_apply_enter');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_RAZORPAY_PAYMENT_LINK;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-mpurse
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyMpurse()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'mpurse_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_MPURSE;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-mpurse upi
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param string customerName
     * @param string customerPhone
     * @param string customerUpiAccount
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyMpurseUpi()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'mpurse_upi_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_MPURSE_UPI;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-sifang
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @param integer paymentChannel
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplySifang()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'sifang_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_SIFANG;
        $form->amount = max(100, $form->amount);
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-qiming
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyQiming()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'qiming_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_QIMING;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-qiming
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyQuanqiupay()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'quanqiupay_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_QUANQIUPAY;
        $form->amount = max(100, ceil($form->amount));
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-rpay
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyRpay()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'rpay_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_RPAY;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-mojo
     * @method post
     * @param integer orderId 订单号
     * @param float amount 支付金额 单位元
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyMojo()
    {
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => Yii::$app->user->identity->getId() ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'mojo_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_MOJO;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 用户还款申请-Jpay
     * @method post
     * @param integer orderId 订单号
     * @param integer amount 还款金额
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws GuzzleException
     * @throws UserExceptionExt
     */
    public function actionRepaymentApplyJpay()
    {
        yii::info([
            'user_id' =>  Yii::$app->user->identity->getId(),
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'jpay_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_JPAY;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @return array
     */
    public function actionGetTransferData()
    {
        $form = new GetTransferDataForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->getTransferData($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    /**
     * @name LoanController 用户还款申请-cashfree
     * @method post
     * @param integer orderId 订单号
     * @param integer amount 支付金额 单位元
     * @param string customerEmail
     * @param string customerPhone
     * @param integer paymentType 支付类型 0正常 1延期部分还款 2延期部分还款并减免滞纳金 3展期
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \common\exceptions\UserExceptionExt
     */
    public function actionRepaymentApplyCashFree()
    {
        yii::info([
            'user_id' =>  Yii::$app->user->identity->getId(),
            'post' => Yii::$app->request->post()
        ], 'cashfree_payment');
        $form = new RepaymentApplyForm();
        $form->load(Yii::$app->request->post(), '');
        $form->userID = Yii::$app->user->identity->getId();
        $form->serviceType = FinancialPaymentOrder::SERVICE_TYPE_CASHFREE;
        $form->host = yii::$app->request->hostInfo . yii::$app->request->baseUrl;
        if ($form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyNew($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name LoanController 导流订单还款申请
     * @method post
     * @param float amount 支付金额 单位元
     * @param string orderUuid
     * @param integer paymentType
     * @param string token
     * @param integer serviceType
     * @param string customerEmail
     * @param string customerPhone
     * @return array
     */
    public function actionRepaymentApplyExport()
    {
        $form = new RepaymentApplyExportForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentApplyFromExport($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name string 前端还款回调
     * @method post
     * @param string razorpayPaymentId
     * @param string razorpayOrderId
     * @param string razorpaySignature
     * @return array
     */
    public function actionRepaymentResult()
    {
        $form = new RepaymentResultForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new RepaymentService();
            if ($service->repaymentResult($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    /**
     * @name string 新借款确认接口-h5使用
     * @method post
     * @param int disbursalAmount
     * @return array
     */
    public function actionLoanConfirmV2()
    {
        $user_register = UserRegisterInfo::findOne(['user_id' => Yii::$app->user->id]);
        Yii::info(['user_id' => Yii::$app->user->id,'appMarket' => $user_register['appMarket'],'type' => 'loan_confirm_info','status' => 'success','msg' => 'success'],'auth_info');

        $data = array_merge(Yii::$app->request->post(), [
            'userId' => Yii::$app->user->id,
            'hostInfo' => Yii::$app->request->hostInfo,
            'packageName' => $this->packageName(),
            'clientInfo' => $this->getClientInfo(),
        ]);
        $validateModel = new ConfirmLoanV2Form();
        if($validateModel->load($data, '') && $validateModel->validate())
        {
            $service = new ProductService();
            $result = $service->getConfirmLoanV2($validateModel);
            if ($result) {
                return $this->return->setData( $service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
            }
        }else{
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($validateModel->getErrorSummary(false)));
        }



    }

    public function actionUserCreditLimitExport()
    {
        $form = new UserCreditLimitForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new UserCreditLimitService();
            if ($service->getUserLimitForExport($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }


    /**
     * @name string 订单绑卡接口
     * @method post
     * @param integer orderId 订单号
     * @param integer bankCardId 银行卡号
     * @return array
     */
    public function actionOrderBindCard()
    {
        $form = new OrderBindCardForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new LoanService();
            if ($service->orderBindCard($form, Yii::$app->user->identity->getId())) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }

    /**
     * @name string 订单绑卡接口
     * @method post
     * @param integer orderId 订单号
     * @param integer bankCardId 银行卡号
     * @return array
     */
    public function actionOrderBindCardExport()
    {
        $form = new OrderBindCardExportForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $service = new LoanService();
            if ($service->orderBindCardFromExport($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }



    /**
     * @name LoanController 用户优惠券列表
     * @method post
     * @return array
     */
    public function actionCouponList(){
        $service = new UserCouponService();
        $service->getUserCouponList(Yii::$app->user->id);
        return $this->return->setData($service->getResult())->returnOK();
    }



    /**
     * @name LoanController 用户线下支付帐号
     * @method get
     * @param integer id 订单号
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionRepayByBankTransfer()
    {

        $form = new RepayByBankTransfer();
        $params = Yii::$app->request->get();
        $params['userId'] = Yii::$app->user->id;
        if ($form->load($params, '') && $form->validate()) {
            $service = new RepaymentService();
            if ($service->actionGetUserVa($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

    }




    /**
     * @name string 订单状态变更
     * @method post
     * @param int id 订单号
     * @return array
     */
    public function actionOrderStatus()
    {
        $form = new OrderStatusForm();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $form->userId = Yii::$app->user->getId();
            $service = new LoanService();
            if ($service->orderChangeStatus($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

    }

    public function actionPushOrderUserCheck()
    {
        $validateModel = new PushOrderUserCheckForm();
        if (!$validateModel->load(Yii::$app->request->post(), '')) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if (!$validateModel->validate()) {
            return $this->return
                ->setData([])
                ->returnFailed(ErrorCode::ERROR_COMMON(), $validateModel->getErrorSummary(false)[0]);
        }

        $service = new UserService();
        if ($service->checkPushOrderUserInfo($validateModel)) {
            $result = $service->getResult();
//            if ($result['isNewUser'] == false) {
//                $loanService = new LoanService();
//                $hasOrder = $loanService->haveOpeningOrder($result['userId']);
//                if ($hasOrder) {
//                    return $this->return
//                        ->returnFailed(ErrorCode::ERROR_COMMON(), json_encode([
//                            'canPush' => false,
//                            'message' => 'User has order',
//                        ]));
//                }
//            }
            return $this->return
                ->setData([
                    'canPush' => true,
                    'message' => $result['msg'],
                ])
                ->returnOK();
        } else {
            return $this->return
                ->returnFailed(ErrorCode::ERROR_COMMON(), $service->getError());
        }
    }

    /**
     * @name LoanController 用户还款申请
     * @method post
     * @param integer orderId 订单号
     * @param integer amount 还款金额
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function actionRepaymentApplyTest()
    {
        $payAccount = PayAccountSetting::findOne(9);
        $account = new RazorpayAccountForm();
        $account->load(json_decode($payAccount->account_info, true), '');
        $key = $account->paymentKeyId;
        $secert = $account->paymentSecret;
        $api = new Api($key,$secert);
        $orderId = mt_rand(100000,999999);
        $orderUuid = uniqid("order_{$orderId}_");
        $order  = $api->order->create([
            'receipt'         => $orderUuid,
            'amount'          => 100,
            'currency'        => 'INR',
            'payment_capture' =>  '1'
        ]);
        if('created' != $order->status){
            return [
                'code' => -1,
                'message' => 'err'

            ];
        }
        $payOrderId = $order->id;

        yii::error([
            'amount' => 100,
            'image' => Yii::$app->request->hostInfo . Yii::$app->request->baseUrl . '/logo.png',
            'orderId' => $payOrderId,
            'key' => $key
        ], 'payment_test');
        return [
            'code' => 0,
            'data' => [
                'amount' => 100,
                'image' => Yii::$app->request->hostInfo . Yii::$app->request->baseUrl . '/logo.png',
                'orderId' => $payOrderId,
                'key' => $key
            ]
        ];

    }

    /**
     * @name LoanController 用户upi addreess还款
     * @method get
     * @param int id 订单号
     * @param string orderType 订单类型 saas,internal
     * @return array
     * @throws \yii\db\Exception
     */
    public function actionRepayByUpiAddress()
    {

        $form = new RepayByBankTransfer();
        $params = Yii::$app->request->get();
        $params['userId'] = Yii::$app->user->id;
        if ($form->load($params, '') && $form->validate()) {
            $service = new RepaymentService();
            if ($service->actionGetUserUPIAddress($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }

    }



    /**
     * @name LoanController 用户提交申请减免信息
     * @method post
     * @param integer orderId 订单号
     * @param integer reductionFee 减免金额
     * @param string repaymentDate 还款日期
     * @param string reasons 原因
     * @param string contact 联系方式
     * @return array
     */
    public function actionSubmitApplyReduction()
    {
        $form = new ApplyReductionForm();
        $form->userId = Yii::$app->user->identity->getId();
        if ($form->load(Yii::$app->request->post(), '') && $form->validate()) {
            $form->userId = Yii::$app->user->identity->getId();
            $service = new CustomerReductionService();
            if ($service->applyReduction($form)) {
                return $this->return->setData($service->getResult())->returnOK();
            } else {
                return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                    $service->getError());
            }
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON(),
                $this->getError($form->getErrorSummary(false)));
        }
    }
}

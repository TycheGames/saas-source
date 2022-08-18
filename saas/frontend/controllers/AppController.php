<?php

namespace frontend\controllers;


use common\models\enum\ErrorCode;
use common\models\enum\VerificationItem;
use common\models\order\UserLoanOrder;
use common\models\order\UserLoanOrderRepayment;
use common\models\CheckVersion;
use common\models\package\PackageSetting;
use common\models\user\LoanPerson;
use common\services\loan\LoanService;
use common\services\package\PackageService;
use common\services\tab_bar_icon\TabBarIconService;
use common\services\user\UserCanLoanCheckService;
use common\services\user\UserCreditLimitService;
use common\services\user\UserVerificationService;
use yii\helpers\Url;
use yii;

class  AppController extends BaseController
{
    public function behaviors()
    {
        return [
            'access' => [
                'class'  => yii\filters\AccessControl::class,
                // 除了下面的action其他都需要登录
                'except' => [
                    'config',
                    'tar-bar',
                    'home',
                    'index-v2',
                    'center-info',
                    'settings',
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
     * @name AppController 默认页 [home]
     * @method get
     *
     */
    public function actionHome(){
        $res=[
            'page' => "home"
        ];

        return $this->return->setData($res)->returnOK();
    }

    /**
     * 下发配置
     * @name AppController 下发配置 [getConfig]
     * @method get
     * @date 2017-10-26
     * @param string configVersion 配置版本号
     * @return array
     * @uses 用于客户端获取url配置
     */
    public function actionConfig()
    {

        $updateMsg = null;
//        $updateMsg = $this->getUpdateMsg();

        $scheme = true;
        $secure = yii::$app->request->getIsSecureConnection();
        if($secure)
        {
            $scheme = 'https';
        }

        $packageName = $this->packageName();

        $searchList = ['https://', 'http://'];
        $domain = Yii::$app->request->hostInfo;
        foreach($searchList as $value)
        {
            $domain = str_replace($value, '', $domain);
        }

        $shareCookieDomain = [$domain];
//        if(in_array($domain, array_keys(yii::$app->params['domainMap'])))
//        {
//            $shareCookieDomain[] = yii::$app->params['domainMap'][$domain];
//        }

        //用于做马甲包混淆，确保每个包的后端请求地址是不同的
        $prefixCode = $this->packageName();


        $config = [
            'name'               => $this->packageName(),
            'configVersion'      => $this->appVersion(),
            'iosVersion'         => strval(time()),
            'androidVersion'     => $this->appVersion(),
            'user_agreement_url' => Yii::$app->request->hostInfo . "/h5/#/agreement/{$packageName}/user", //用户协议
            'privacyPolicyUrl'   => Yii::$app->request->hostInfo . "/h5/#/agreement/{$packageName}/privacy", //隐私协议
            'termsOfUseUrl'      => Yii::$app->request->hostInfo . "/h5/#/agreement/{$packageName}/use", //使用协议
            'updateMsg'          => $updateMsg,
            'shareCookieDomain'  => $shareCookieDomain, //共享cookie的域名
            'isHideMain'         => false,
            'dataUrl'            => [
                $prefixCode . 'creditAppIndex'               => Url::toRoute('app/index-v2', $scheme),
                $prefixCode . 'creditTarBar'                 => Url::toRoute('app/tar-bar', $scheme),
                $prefixCode . 'creditCenterInfo'             => Url::toRoute('app/center-info', $scheme),
                $prefixCode . 'creditSettings'               => Url::toRoute('app/settings', $scheme),
                $prefixCode . 'creditConfirmLoan'            => Url::toRoute('loan/confirm-loan', $scheme),
                $prefixCode . 'creditApplyGetOtp'            => Url::toRoute('loan/apply-get-code', $scheme),
                $prefixCode . 'creditApplyLoan'              => Url::toRoute('loan/apply-loan', $scheme),
                $prefixCode . 'creditPhoneIsRegistered'      => Url::toRoute('user/phone-is-registered', $scheme),
                $prefixCode . 'creditGetLoginOtp'            => Url::toRoute('user/get-login-captcha', $scheme),
                $prefixCode . 'creditLoginByOtp'             => Url::toRoute('user/captcha-login', $scheme),
                $prefixCode . 'creditResetPassword'          => Url::toRoute('user/reset-password', $scheme),
                $prefixCode . 'creditGetResetPasswordOtp'    => Url::toRoute('user/get-reset-pass-otp', $scheme),
                $prefixCode . 'creditUploadContents'         => Url::toRoute('user/upload-contents', $scheme),
                $prefixCode . 'creditUploadMetadata'         => Url::toRoute('user/upload-metadata', $scheme),
                $prefixCode . 'creditSaveUserContact'        => Url::toRoute('user/save-user-contact', $scheme),
                $prefixCode . 'creditGetUserContract'        => Url::toRoute('user/get-user-contact', $scheme),
                $prefixCode . 'creditUserRegGetCode'         => Url::toRoute('user/reg-get-code', $scheme),
                $prefixCode . 'creditUserLogout'             => Url::toRoute('user/logout', $scheme),
                $prefixCode . 'creditUserLogin'              => Url::toRoute('user/login', $scheme),
                $prefixCode . 'creditUserRegister'           => Url::toRoute('user/register', $scheme),
                $prefixCode . 'creditTdSnsCallback'          => '',
                //地址认证开始
                $prefixCode . 'creditGetAddressProofConfig'  => Url::toRoute('user/get-address-proof-config', $scheme),
                $prefixCode . 'creditUploadAddressProofOcr'  => Url::toRoute('user/upload-address-proof-ocr', $scheme),
                $prefixCode . 'creditSaveAddressProofReport' => Url::toRoute('user/save-address-proof-report', $scheme),
                //地址认证结束
                //实名认证开始
                $prefixCode . 'creditGetUserKycConfig'       => Url::toRoute('user/get-user-kyc-config', $scheme),
                $prefixCode . 'creditSaveUserPan'            => Url::toRoute('user/save-user-pan', $scheme),
                $prefixCode . 'creditSaveUserFr'             => Url::toRoute('user/save-user-fr', $scheme),
                $prefixCode . 'creditSaveUserFrSecond'       => Url::toRoute('user/save-user-fr-second', $scheme),
                $prefixCode . 'creditSaveUserAadhaarOcr'     => Url::toRoute('user/save-user-aadhaar-ocr', $scheme),
                $prefixCode . 'creditSaveUserKyc'            => Url::toRoute('user/save-user-kyc', $scheme),
                //实名认证结束
            ],
        ];

        $packageService = new PackageService($this->packageName());
        $currentPackage = $packageService->getPackageSetting();
        if (Yii::$app->user->isGuest) {
            if($currentPackage)
            {
                $reviewFlag = $currentPackage->is_google_review == PackageSetting::GOOGLE_REVIEW_OPEN;
                $appMarketFlag = $this->packageName() . '_google' == $this->appMarket();
                if ($reviewFlag && $appMarketFlag) {
                    $config['isHideMain'] = true;
                }
            }

        }

        return $this->return->setData($config)->returnOK();

    }

    /**
     * 获取强制更新的配置
     */
    public function getUpdateMsg(){
        $appVersion = $this->appVersion();
        $appMarket = $this->appMarket();

        $redis = Yii::$app ->redis;
        $ret = $redis->executeCommand('GET', [$appMarket.'_'.$appVersion.'_cache']);
        if($ret && YII_ENV_PROD && false){
            $update_msg = $ret;
        }else{
            $update_msg = null;
            $data = CheckVersion::find()
                ->where(['status'=>1, 'app_market' => $appMarket])
                ->asArray()->orderBy(['id' => SORT_DESC])->all();
            $isUpdate = false;
            foreach ($data as $key =>$val){
                $rules = explode('~',$val['rules']);
                if(count($rules) == 1){
                    version_compare($appVersion,$rules[0],'==') && $isUpdate = true;
                }
                if(count($rules) > 1){
                    version_compare($appVersion,$rules[0],'>=') && version_compare($appVersion,$rules[1],'<=') && $isUpdate = true;
                }
                if($isUpdate){
                    $update_msg = json_encode([
                        'has_upgrade' => $val['has_upgrade'],
                        'is_force_upgrade' => $val['is_force_upgrade'],
                        'new_version' => $val['new_version'],
                        'new_features' =>  $val['new_features'],
                        'ard_url' => $val['ard_url'],
                        'ard_size' => $val['ard_size'],
                    ]);
                    $redis->executeCommand('SET', [$appMarket.'_'.$appVersion.'_cache', $update_msg]);
                    $redis->executeCommand('EXPIRE', [$appMarket.'_'.$appVersion.'_cache', 600]);
                    break;
                }
            }
        }
        return $update_msg;
    }

    /**
     * @name AppController 底部tab下发
     * @method get
     * @return array
     */
    public function actionTarBar()
    {
        $arrTabBarIcon = TabBarIconService::getTabBarIconList($this->packageName());
        if (!empty($arrTabBarIcon)) {
            $loanNormalImage    = $arrTabBarIcon['index']['normal_img'];
            $loanSelectImage    = $arrTabBarIcon['index']['select_img'];
            $loanTitle = $arrTabBarIcon['index']['title'] ?? 'Loan';
            $profileNormalImage = $arrTabBarIcon['my']['normal_img'];
            $profileSelectImage = $arrTabBarIcon['my']['select_img'];
            $profileTitle = $arrTabBarIcon['my']['title'] ?? 'My Profile';
            $selSpanColor       = $arrTabBarIcon['index']['normal_color'];
            $spanColor          = $arrTabBarIcon['index']['select_color'];
        } else {
            return $this->return->returnFailed(ErrorCode::ERROR_COMMON());
        }

        if(Yii::$app->user->isGuest)
        {
            $loan = [
                'title'          => $loanTitle,
                'tag'            => 1,
                'normalImage'    => $loanNormalImage,
                'selectImage'    => $loanSelectImage,
                'url'            => '',
                'span_color'     => $spanColor,
                'sel_span_color' => $selSpanColor,
            ];
        }else{
            if(Yii::$app->user->identity->can_loan_time > time())
            {
                $loan = [
                    'title'          => $loanTitle,
                    'tag'            => 4,
                    'normalImage'    => $loanNormalImage,
                    'selectImage'    => $loanSelectImage,
                    'url'            => Yii::$app->request->hostInfo . '/h5/#/loanRejected',
                    'span_color'     => $spanColor,
                    'sel_span_color' => $selSpanColor,
                ];
            }
            else{
                /**
                 * @var UserLoanOrder $order
                 */

                $order = UserLoanOrder::userLastOrder(Yii::$app->user->id);
                if($order
                    && UserLoanOrder::STATUS_WAIT_DEPOSIT == $order->status
                    && UserLoanOrder::LOAN_STATUS_WAIT_BIND_CARD == $order->loan_status)
                {
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 5,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => Yii::$app->request->hostInfo . '/h5/#/audit/1?id=' . $order->id,
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }elseif($order
                    && UserLoanOrder::STATUS_WAIT_DRAW_MONEY == $order->status
                    && UserLoanOrder::LOAN_STATUS_DRAW_MONEY == $order->loan_status){
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 9,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => Yii::$app->request->hostInfo . '/h5/#/withdrawals?orderId='. $order->id,
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }elseif($order &&
                        ((UserLoanOrder::STATUS_CHECK == $order->status) ||
                            (UserLoanOrder::STATUS_WAIT_DEPOSIT == $order->status && UserLoanOrder::LOAN_STATUS_BIND_CARD_CHECK == $order->loan_status)
                        ))
                {
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 6,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => Yii::$app->request->hostInfo . '/h5/#/audit/0?id='. $order->id,
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }elseif($order && UserLoanOrder::STATUS_LOANING == $order->status)
                {
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 7,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => Yii::$app->request->hostInfo . '/h5/#/audit/2?id='. $order->id,
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }elseif($order && UserLoanOrder::STATUS_LOAN_COMPLETE == $order->status){
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 8,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => Yii::$app->request->hostInfo . '/h5/#/orderDetail/'. $order->id . '?title=' . $this->packageName(),
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }
                else{
                    $loan = [
                        'title'          => $loanTitle,
                        'tag'            => 1,
                        'normalImage'    => $loanNormalImage,
                        'selectImage'    => $loanSelectImage,
                        'url'            => '',
                        'span_color'     => $spanColor,
                        'sel_span_color' => $selSpanColor,
                    ];
                }

            }
        }

        $data = [
            $loan,
            [
                'title'          => $profileTitle,
                'tag'            => 3,
                'normalImage'    => $profileNormalImage,
                'selectImage'    => $profileSelectImage,
                'url'            => Yii::$app->request->hostInfo . '/h5/#/PersonalCenter',
                'span_color'     => $spanColor,
                'sel_span_color' => $selSpanColor,
            ],
        ];

        $repaymentNormal = UserLoanOrderRepayment::userRepaymentNormal(Yii::$app->user->id);

        return $this->return
            ->setCommand([
                "path"       => "/app/shortcutbadger",
                "badgeCount" => count($repaymentNormal),
            ])
            ->setData($data)
            ->returnOK();

    }


    /**
     * @name AppController 被拒页接口
     * @method get
     * @return array
     */
    public function actionRejectPage()
    {
        /** @var LoanPerson $loanPerson */
        $loanPerson = Yii::$app->user->identity;
        $service = new UserCanLoanCheckService($loanPerson);
        return $this->return->setData([
            'time' => $service->getCanLoanDate()
        ])->returnOK();
    }

    /**
     * @name AppController app新首页
     * @method get
     * @return array
     */
    public function actionIndexV2()
    {
        $userId = Yii::$app->user->getId();
        $clientInfo = $this->getClientInfo();
        Yii::info([
            'user_id' => $userId ?? 0,
            'package_name' => $clientInfo['packageName'] ?? '',
        ], 'app_index_enter');
        $title = 'Loan';

        $moneyAmount = 100000;  //借款额度
        $moneyTip = 'Your maximum credit to borrow';
        $actionTip = 'Boost your credit by repaying your loan on time';
        $actionText = 'APPLY';

        $service = new LoanService();
        if (Yii::$app->user->isGuest) {
            $jump = json_encode([
                'path'         => '/user/login',
                'isFinishPage' => false,
            ], JSON_UNESCAPED_UNICODE);
        } else {
            $verificationService = new UserVerificationService(Yii::$app->user->id);
            $userCreditLimitService = new UserCreditLimitService();
            //用户完成认证项则显示真实的额度
            if($verificationService->getAllVerificationStatus() || Yii::$app->user->identity->customer_type == LoanPerson::CUSTOMER_TYPE_OLD){
                $moneyAmount = 4000;  //借款额度
                $moneyTip = 'Disbursal Amount';
            }
            $clientInfo = $this->getClientInfo();
            if (!$service->haveOpeningOrder($userId)) {
                $jump = $verificationService->getNextVerificationItemPath(
                    VerificationItem::START_ITEM(),
                    Yii::$app->request->hostInfo,
                    $clientInfo
                )['jump'];
            }else {
                $jump = json_encode([
                    'path'         => '/tip/toast',
                    'text'         => 'You have an order in progress',
                    'isFinishPage' => false,
                ], JSON_UNESCAPED_UNICODE);
            }
        }


        $data = array_merge([
            'moneyAmount'         => $moneyAmount,
            'title'         => $title,
            'moneyTip'         => $moneyTip,
            'actionTip'         => $actionTip,
            'actionText'         => $actionText,
            'jump'               => $jump,
            'isRefreshTabList'   => true,
        ]);

        return $this->return->setData($data)->returnOK();

    }


    /**
     * @name AppController 个人中心
     * @method get
     * @return array
     */
    public function actionCenterInfo()
    {
        $jumpLogin = json_encode([
            'path'         => '/user/login',
            'isFinishPage' => false,
        ], JSON_UNESCAPED_UNICODE);
        //借款记录
        $url = Yii::$app->request->hostInfo . '/h5/#/paymentOrder/loanRecords';
        $loan_record = [
            'logo' => 'http://res.i-credit.in/picture/ic-ecommerce-money.png',
            'title' => 'Loan Records',
            'jump' => Yii::$app->user->isGuest ? $jumpLogin : json_encode([
                'path'=> '/h5/webview',
                'url' => $url,
            ], JSON_UNESCAPED_UNICODE),
        ];
        $list[] = $loan_record;

        //客户服务
        $customer_service = [
            'logo' => 'http://res.i-credit.in/picture/ic-emoji-pretty-good.png',
            'title' => 'Customer Service',
            'jump' => json_encode([
                'path'=> '/h5/webview',
                'url' => Yii::$app->request->hostInfo . '/h5/#/helpCenter',
            ], JSON_UNESCAPED_UNICODE),
        ];
        $list[] = $customer_service;

        //设置
        $settings = [
            'logo' => 'http://res.i-credit.in/picture/ic-actions-settings.png',
            'title' => 'Settings',
            'jump' => json_encode([
                'path'=> '/user/settings'
            ], JSON_UNESCAPED_UNICODE),
        ];
        $list[] = $settings;
        $data = ['list' => $list, 'isLogin' => Yii::$app->user->isGuest ? false : true];
        return $this->return->setData($data)->returnOK();
    }

    /**
     * @name AppController 设置
     * @method get
     * @return array
     */
    public function actionSettings()
    {
        //关于我们
        $about_us = [
            'logo' => 'http://res.i-credit.in/picture/ic-security-secured-profile.png',
            'title' => 'About Us',
            'jump' => json_encode([
                'path'=> '/h5/webview',
                'url' => Yii::$app->request->hostInfo . '/h5/#/aboutUs',
            ], JSON_UNESCAPED_UNICODE),
        ];
        $list[] = $about_us;
        $data = ['list' => $list, 'isLogin' => Yii::$app->user->isGuest ? false : true];
        return $this->return->setData($data)->returnOK();
    }
}
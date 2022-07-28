package com.bigshark.android.utils;

import com.bigshark.android.BuildConfig;

public class StringConstant {


    //<editor-fold desc="jump_code">

    private static final String JUMP_HOME_BASE = "/home";
    /**
     * 匿名举报
     */
    public static final String JUMP_HOME_ANONYMOUS__REPORT = JUMP_HOME_BASE + "/report";
    /**
     * 搜索 activity
     */
    public static final String JUMP_HOME_SEARCH = JUMP_HOME_BASE + "/search";
    /**
     * 发红包页面
     */
    public static final String JUMP_HOME_SEND_RED_ENVELOPE = JUMP_HOME_BASE + "/send_red_envelope";
    /**
     * 用户的个人主页 男性
     */
    public static final String JUMP_HOME_USER_MEN_HOMEPAGE = JUMP_HOME_BASE + "/user_men";
    /**
     * 用户的个人主页 女性
     */
    public static final String JUMP_HOME_USER_WOMEN_HOMEPAGE = JUMP_HOME_BASE + "/user_women";
    /**
     * 查看用户图片
     */
    public static final String JUMP_HOME_VIEW_USER_PHOTO = JUMP_HOME_BASE + "/view_photo";

    private static final String JUMP_MESSAGE_CENTER_BASE = "/message_center";
    /**
     * 收益提醒
     */
    public static final String JUMP_MESSAGE_CENTER_EARNING_REMIND = JUMP_MESSAGE_CENTER_BASE + "/earning_remind";
    /**
     * 评价通知
     */
    public static final String JUMP_MESSAGE_CENTER_EVALUATE_NOTIFY = JUMP_MESSAGE_CENTER_BASE + "/evaluate_notify";
    /**
     * 消息推送设置
     */
    public static final String JUMP_MESSAGE_CENTER_PUSH_SETTING = JUMP_MESSAGE_CENTER_BASE + "/push_setting";
    /**
     * 电台广播
     */
    public static final String JUMP_MESSAGE_CENTER_RADIO_NOTICE = JUMP_MESSAGE_CENTER_BASE + "/radio_notice";
    /**
     * 系统通知
     */
    public static final String JUMP_MESSAGE_CENTER_SYSTEM_NOTIFY = JUMP_MESSAGE_CENTER_BASE + "/system_notify";

    private static final String JUMP_MINE_BASE = "/mine";
    /**
     * 修改密码
     */
    public static final String JUMP_MINE_CHANGE_PASSWORD = JUMP_MINE_BASE + "/change_password";
    /**
     * 找回密码
     */
    public static final String JUMP_MINE_FIND_PASSWORD = JUMP_MINE_BASE + "/find_password";
    /**
     * 问题反馈
     */
    public static final String JUMP_MINE_PROBLEM_FEEDBACK = JUMP_MINE_BASE + "/problem_feedback";
    /**
     * 更换手机号
     */
    public static final String JUMP_MINE_REPLACE_PHONE = JUMP_MINE_BASE + "/replace_phone";

    private static final String JUMP_RADIO_HALL_BASE = "/radio_hall";
    /**
     * 查看报名
     */
    public static final String JUMP_RADIO_HALL_CHECK_REGISTRATION = JUMP_RADIO_HALL_BASE + "/check_registration";
    /**
     * 我的广播中心
     */
    public static final String JUMP_RADIO_HALL_MY_RADIO_CENTER = JUMP_RADIO_HALL_BASE + "/my_radio_center";
    /**
     * 广播详情
     */
    public static final String JUMP_RADIO_HALL_RADIO_DETAILS = JUMP_RADIO_HALL_BASE + "/radio_details";
    /**
     * 发布广播
     */
    public static final String JUMP_RADIO_HALL_RELEASE_RADIO = JUMP_RADIO_HALL_BASE + "/release_radio";
    /**
     * 查看广播图片
     */
    public static final String JUMP_RADIO_HALL_VIEW_RADIO_PHOTO = JUMP_RADIO_HALL_BASE + "/view_radio_photo";

    //</editor-fold>

    //<editor-fold desc="jump">

    private static final String JUMP_APP_BASE = "/app";
    /**
     * 上报数据
     */
    public static final String JUMP_APP_UPLOAD_DATA = JUMP_APP_BASE + "/upload_data";

    public static final String JUMP_APP_SHORTCUT_BADGER = JUMP_APP_BASE + "/shortcutbadger";
    /**
     * 关闭当前页
     */
    public static final String JUMP_APP_BACK = JUMP_APP_BASE + "/back";
    /**
     * 打开浏览器
     */
    public static final String JUMP_APP_OPEN_BROWSER = JUMP_APP_BASE + "/open_browser";
    /**
     * 弹出拨打电话的dialog
     */
    public static final String JUMP_APP_CALL_PHONE = JUMP_APP_BASE + "/call_phone";

    private static final String JUMP_AUTHENTICATE_BASE = "/auth";
    /**
     * kyc认证：ocr认证中心
     */
    public static final String JUMP_AUTHENTICATE_OCR_AUTH_CENTER = JUMP_AUTHENTICATE_BASE + "/ocr_auth_center";
    /**
     * 地址证明
     */
    public static final String JUMP_AUTHENTICATE_ADDRESS_PROOF = JUMP_AUTHENTICATE_BASE + "/address_proof";
    /**
     * 紧急联系人认证
     */
    public static final String JUMP_AUTHENTICATE_CONTACT = JUMP_AUTHENTICATE_BASE + "/contact";
    /**
     * 活体认证：复借时使用
     */
    public static final String JUMP_AUTHENTICATE_LIVENESS = JUMP_AUTHENTICATE_BASE + "/liveness";

    /**
     * 跳转到认证流程的url
     */
    public static final String JUMP_AUTHENTICATE_URL = JUMP_AUTHENTICATE_BASE + "/h5/webview";

    private static final String JUMP_H5_BASE = "/h5";
    /**
     * 跳转到APP内部的网页界面，打开URL：使用原生的webview
     */
    public static final String JUMP_H5_URL = JUMP_H5_BASE + "/webview";
    /**
     * webview页面中，左上角的点击功能
     */
    public static final String JUMP_H5_TOP_LEFT_BUTTON = JUMP_H5_BASE + "/webview/top_left_button";

    private static final String JUMP_MAIN_VIEW_TAB_BASE = "/main";
    /**
     * 跳转指定tab
     */
    public static final String JUMP_MAIN_VIEW_TAB = JUMP_MAIN_VIEW_TAB_BASE + "/tab";
    /**
     * 刷新tabBar
     */
    public static final String JUMP_MAIN_VIEW_REFRESH_TABLIST = JUMP_MAIN_VIEW_TAB_BASE + "/refresh_tablist";
    /**
     * 返回首頁
     */
    public static final String JUMP_MAIN_VIEW_HOME = JUMP_MAIN_VIEW_TAB_BASE + "/home";


    private static final String JUMP_TIP_BASE = "/tip";
    /**
     * Toast提示
     */
    public static final String JUMP_TIP_TOAST = JUMP_TIP_BASE + "/toast";
    /**
     * 图片dialog
     */
    public static final String JUMP_TIP_DIALOG_IMAGE = JUMP_TIP_BASE + "/dialog_image";
    /**
     * Dialog提示
     */
    public static final String JUMP_TIP_DIALOG = JUMP_TIP_BASE + "/dialog";


    private static final String JUMP_USER_BASE = "/user";
    /**
     * 用户登陆
     */
    public static final String JUMP_USER_LOGIN = JUMP_USER_BASE + "/login";
    /**
     * 退出登录
     */
    public static final String JUMP_USER_LOGOUT = JUMP_USER_BASE + "/logout";
//    // 跳转到设置页
//    public static final String SETTINGS = JUMP_USER_BASE + "/settings";

    private static final String JUMP_USER_FORGET_BASE = JUMP_USER_BASE + "/password";
    /**
     * 忘记密码
     */
    public static final String JUMP_USER_FORGET_PASSWORD = JUMP_USER_FORGET_BASE + "/forget";


    //</editor-fold>

    //<editor-fold desc="code_http">
    public static final String SERVICE_URL_CONFIG_RELEASE = "https://app.3gengby.com/app/config";
    //    public static final String SERVICE_URL_CONFIG_DEV     = "http://dev-app.3gengby.com/app/config";

    //登录
    public static final String SERVICE_URL_USERLOGIN_KEY            = BuildConfig.PACKAGE_NAME + "userLogin";
    //注册
    public static final String SERVICE_URL_USERREGISTER_KEY         = BuildConfig.PACKAGE_NAME + "userRegister";
    //修改手机
    public static final String SERVICE_URL_UPDATEPHONE_KEY          = BuildConfig.PACKAGE_NAME + "updatePhone";
    //找回密码
    public static final String SERVICE_URL_FINDPASSWORD_KEY         = BuildConfig.PACKAGE_NAME + "findPassword";
    //修改密码
    public static final String SERVICE_URL_UPDATEPASSWORD_KEY       = BuildConfig.PACKAGE_NAME + "updatePassword";
    //获取验证码
    public static final String SERVICE_URL_GETCODE_KEY              = BuildConfig.PACKAGE_NAME + "getCode";
    //保存用户性别
    public static final String SERVICE_URL_SAVEUSERSEX_KEY          = BuildConfig.PACKAGE_NAME + "saveUserSex";
    //修改坐标
    public static final String SERVICE_URL_UPDATECOORDINATE_KEY     = BuildConfig.PACKAGE_NAME + "UpdateCoordinate";
    //获取用户资料
    public static final String SERVICE_URL_GETUSERPROFILE_KEY       = BuildConfig.PACKAGE_NAME + "getUserProfile";
    //查看用户资料
    public static final String SERVICE_URL_PUBINFO_KEY              = BuildConfig.PACKAGE_NAME + "pubInfo";
    //首页推荐 list
    public static final String SERVICE_URL_NEARBYLIST_KEY           = BuildConfig.PACKAGE_NAME + "getNearbyList";
    //添加或取消收藏
    public static final String SERVICE_URL_SWITCHCOLLECTION_KEY     = BuildConfig.PACKAGE_NAME + "switchCollection";
    //获取广播列表
    public static final String SERVICE_URL_GETBROADCASTLIST_KEY     = BuildConfig.PACKAGE_NAME + "getBroadcastList";
    //点赞
    public static final String SERVICE_URL_CLICKGOODBROADCAST_KEY   = BuildConfig.PACKAGE_NAME + "clickGoodBroadcast";
    //地区信息
    public static final String SERVICE_URL_GETREGION_KEY            = BuildConfig.PACKAGE_NAME + "getRegion";
    //多图片上传（表单形式）
    public static final String SERVICE_URL_UPLOADIMAGEBATCH_KEY     = BuildConfig.PACKAGE_NAME + "uploadImageBatch";
    //图片上传（表单形式）s
    public static final String SERVICE_URL_UPLOADIMAGE_KEY          = BuildConfig.PACKAGE_NAME + "uploadImage";
    //发布广播
    public static final String SERVICE_URL_PUBLISHBROADCAST_KEY     = BuildConfig.PACKAGE_NAME + "publishBroadcast";
    //查看广播报名
    public static final String SERVICE_URL_GETENROLLLIST_KEY        = BuildConfig.PACKAGE_NAME + "getEnrollList";
    //确认聊天
    public static final String SERVICE_URL_CONFIRMYUE_KEY           = BuildConfig.PACKAGE_NAME + "ConfirmYue";
    //广播详情
    public static final String SERVICE_URL_GETBROADCASTDETAIL_KEY   = BuildConfig.PACKAGE_NAME + "getBroadcastDetail";
    //结束广播
    public static final String SERVICE_URL_ENDBROADCAST_KEY         = BuildConfig.PACKAGE_NAME + "EndBroadcast";
    //获取评价
    public static final String SERVICE_URL_GETCOMMENT_KEY           = BuildConfig.PACKAGE_NAME + "getComment";
    //发送评价
    public static final String SERVICE_URL_SENDCOMMENT_KEY          = BuildConfig.PACKAGE_NAME + "sendComment";
    //个人中心首页
    public static final String SERVICE_URL_USERCENTERINDEX_KEY      = BuildConfig.PACKAGE_NAME + "userCenterIndex";
    //上传照片
    public static final String SERVICE_URL_UPLOADUSERPIC_KEY        = BuildConfig.PACKAGE_NAME + "uploadUserPic";
    //我的收藏
    public static final String SERVICE_URL_MYCOLLECTION_KEY         = BuildConfig.PACKAGE_NAME + "myCollection";
    //拉黑或取消拉黑
    public static final String SERVICE_URL_ADDORCANCELBLACK_KEY     = BuildConfig.PACKAGE_NAME + "addOrCancelBlack";
    //解锁用户相关权限
    public static final String SERVICE_URL_UNLOCKPRI_KEY            = BuildConfig.PACKAGE_NAME + "unlockPri";
    //向对方发送社交账号
    public static final String SERVICE_URL_SENDSOCIALACCOUNT_KEY    = BuildConfig.PACKAGE_NAME + "sendSocialAccount";
    //我的广播中心
    public static final String SERVICE_URL_GETMYBROADCAST_KEY       = BuildConfig.PACKAGE_NAME + "getMyBroadcast";
    //隐私设置
    public static final String SERVICE_URL_SETPRIVACY_KEY           = BuildConfig.PACKAGE_NAME + "SetPrivacy";
    //用户反馈
    public static final String SERVICE_URL_USERFEEDBACK_KEY         = BuildConfig.PACKAGE_NAME + "userFeedback";
    //焚毁照片
    public static final String SERVICE_URL_BURNPIC_KEY              = BuildConfig.PACKAGE_NAME + "burnPic";
    //付费照片
    public static final String SERVICE_URL_PAYPIC_KEY               = BuildConfig.PACKAGE_NAME + "payPic";
    //报名
    public static final String SERVICE_URL_ENROLLBROADCAST_KEY      = BuildConfig.PACKAGE_NAME + "enrollBroadcast";
    //评论
    public static final String SERVICE_URL_COMMENTBROADCAST_KEY     = BuildConfig.PACKAGE_NAME + "commentBroadcast";
    //获取所有消息
    public static final String SERVICE_URL_GETNEWSALL_KEY           = BuildConfig.PACKAGE_NAME + "getNewsALL";
    //广播消息列表
    public static final String SERVICE_URL_GETNEWSBROADCASTLIST_KEY = BuildConfig.PACKAGE_NAME + "getNewsBroadcastList";
    //系统消息列表
    public static final String SERVICE_URL_GETNEWSSYSTEMLIST_KEY    = BuildConfig.PACKAGE_NAME + "getNewsSystemList";
    //评价通知
    public static final String SERVICE_URL_GETNEWSCOMMENTLIST_KEY   = BuildConfig.PACKAGE_NAME + "getNewsCommentList";
    //收益提醒
    public static final String SERVICE_URL_GETNEWSPROFITLIST_KEY    = BuildConfig.PACKAGE_NAME + "getNewsProfitList";
    //支付宝支付
    public static final String SERVICE_URL_PAYALIPAY_KEY            = BuildConfig.PACKAGE_NAME + "payAlipay";
    //支付宝 支付确认
    public static final String SERVICE_URL_GETALIPAYRESULT_KEY      = BuildConfig.PACKAGE_NAME + "getAlipayResult";
    //微信支付
    public static final String SERVICE_URL_PAYWECHAT_KEY            = BuildConfig.PACKAGE_NAME + "payWeChat";
    //微信支付结果确认
    public static final String SERVICE_URL_GETWECHATRESULT_KEY      = BuildConfig.PACKAGE_NAME + "getWechatResult";
    //余额支付
    public static final String SERVICE_URL_PAYBALANCE_KEY           = BuildConfig.PACKAGE_NAME + "payBalance";
    //提交RegistertionId
    public static final String SERVICE_URL_SETPUSHREGISTERID_KEY    = BuildConfig.PACKAGE_NAME + "setPushRegisterId";
    //获取推送状态
    public static final String SERVICE_URL_GETPUSHSTATUS_KEY        = BuildConfig.PACKAGE_NAME + "getPushStatus";
    //设置推送状态
    public static final String SERVICE_URL_SETPUSHSTATUS_KEY        = BuildConfig.PACKAGE_NAME + "setPushStatus";
    //接收红包
    public static final String SERVICE_URL_REDPACKETRECEIVE_KEY     = BuildConfig.PACKAGE_NAME + "redPacketReceive";
    //红包信息
    public static final String SERVICE_URL_REDPACKETINFO_KEY        = BuildConfig.PACKAGE_NAME + "redPacketInfo";
    //刪除照片
    public static final String SERVICE_URL_DELETEUSERPIC_KEY        = BuildConfig.PACKAGE_NAME + "deleteUserPic";
    //设置或取消照片 阅后即焚
    public static final String SERVICE_URL_BURNAFTERREAD_KEY        = BuildConfig.PACKAGE_NAME + "burnAfterRead";
    //设置或取消 红包照片
    public static final String SERVICE_URL_REDPACKETPIC_KEY         = BuildConfig.PACKAGE_NAME + "redPacketPic";
    //获取举报选项
    public static final String SERVICE_URL_USERREPORTOPTIONS_KEY    = BuildConfig.PACKAGE_NAME + "userReportOptions";
    //举报
    public static final String SERVICE_URL_USERREPORT_KEY           = BuildConfig.PACKAGE_NAME + "userReport";
    //系统检查（APP 唤醒调用）
    public static final String SERVICE_URL_SYSTEMCHECK_KEY          = BuildConfig.PACKAGE_NAME + "SystemCheck";
    //判断用户资料是否完整
    public static final String SERVICE_URL_ISFULL_KEY               = BuildConfig.PACKAGE_NAME + "isFull";
    //获取消息小红点
    public static final String SERVICE_URL_GETNEWSREDDOT_KEY        = BuildConfig.PACKAGE_NAME + "getNewsRedDot";

    //</editor-fold>

    //<editor-fold desc="http">

    // 主页tab接口
    public static final String HTTP_APP_GET_MAIN_TABBAR_LIST = BuildConfig.PACKAGE_NAME + "creditTarBar";
    // 首页接口
    public static final String HTTP_APP_GET_HOME_DATA = BuildConfig.PACKAGE_NAME + "creditAppIndex";
    // 我的页面接口
    public static final String HTTP_APP_GET_MAIN_ME = BuildConfig.PACKAGE_NAME + "creditCenterInfo";
    // 设置页面接口
    public static final String HTTP_APP_GET_SETTINGS = BuildConfig.PACKAGE_NAME + "creditSettings";

    // 用户协议
    public static final String HTTP_PERSONAL_PROTOCO_AGREEMENT = BuildConfig.PACKAGE_NAME + "user_agreement_url";
    // 隐私协议 privacyPolicyUrl
    public static final String HTTP_PERSONAL_PROTOCO_PRIVACY_POLICY = BuildConfig.PACKAGE_NAME + "privacyPolicyUrl";
    // 使用协议
    public static final String HTTP_PERSONAL_PROTOCO_TERMS_OF_USER = BuildConfig.PACKAGE_NAME + "termsOfUseUrl";

    // 手机号注册 还是 登录
    public static final String HTTP_USER_ENTER_GET_PHONE_STATUS = BuildConfig.PACKAGE_NAME + "creditPhoneIsRegistered";
    // 使用truecaller进行登录、注册
    public static final String HTTP_USER_ENTER_POST_TRUECALLER_LOGIN = BuildConfig.PACKAGE_NAME + "creditTruecallerLogin";

    // 获取注册验证码
    public static final String HTTP_USER_REGISTER_GET_CODE = BuildConfig.PACKAGE_NAME + "creditUserRegGetCode";
    // 注册
    public static final String HTTP_USER_REGISTER = BuildConfig.PACKAGE_NAME + "creditUserRegister";

    // 密码登录
    public static final String HTTP_USER_PASSWORD_LOGIN = BuildConfig.PACKAGE_NAME + "creditUserLogin";

    // 获取登录验证码
    public static final String HTTP_USER_OTP_LOGIN_GET_CODE = BuildConfig.PACKAGE_NAME + "creditGetLoginOtp";
    // 验证码登录
    public static final String HTTP_USER_OTP_LOGIN_BY_VERFILY_CODE = BuildConfig.PACKAGE_NAME + "creditLoginByOtp";

    // 登出
    public static final String HTTP_USER_LOGOUT = BuildConfig.PACKAGE_NAME + "creditUserLogout";

    // 密码重置验证码
    public static final String HTTP_USER_GET_CODE_FOR_RESET_PASSWORD = BuildConfig.PACKAGE_NAME + "creditGetResetPasswordOtp";
    // 密码重置
    public static final String HTTP_USER_RESET_PASSWORD = BuildConfig.PACKAGE_NAME + "creditResetPassword";

    //        // 获取用户KYC配置
//        public static final String GET_USER_KYC_CONFIG = BuildConfig.PACKAGE_NAME + "creditGetUserKycConfig";
    // 保存用户Pan卡数据
    public static final String HTTP_AUTHENTICATE_UPLOAD_USER_PAN_CARD = BuildConfig.PACKAGE_NAME + "creditSaveUserPan";
    // 保存用户证件人脸数据
    public static final String HTTP_AUTHENTICATE_UPLOAD_USER_LIVENESS = BuildConfig.PACKAGE_NAME + "creditSaveUserFr";
    // 保存用户KYC
    public static final String HTTP_AUTHENTICATE_UPLOAD_USER_KYC_REPORT = BuildConfig.PACKAGE_NAME + "creditSaveUserKyc";

    // 获取地址证明的配置信息
    public static final String HTTP_AUTHENTICATE_GET_ADDRESS_CARD_CONFIG = BuildConfig.PACKAGE_NAME + "creditGetAddressProofConfig";
    // 保存用户Aadhaar卡数据-OCR
    public static final String HTTP_AUTHENTICATE_SAVE_AADHAAR_OCR = BuildConfig.PACKAGE_NAME + "creditUploadAddressProofOcr";
    // 提交地址证明报告信息
    public static final String HTTP_AUTHENTICATE_SAVE_ADDRESS_CARD_REPORT = BuildConfig.PACKAGE_NAME + "creditSaveAddressProofReport";

    // 复借时，重做活体认证的reportid上报
    public static final String HTTP_AUTHENTICATE_LIVENESS_REDO = BuildConfig.PACKAGE_NAME + "creditSaveUserFrSecond";

    //获取紧急联系人
    public static final String HTTP_AUTHENTICATE_GET_CONTACT_INFO = BuildConfig.PACKAGE_NAME + "creditGetUserContract";
    //保存紧急联系人
    public static final String HTTP_AUTHENTICATE_SAVE_CONTACT_INFO = BuildConfig.PACKAGE_NAME + "creditSaveUserContact";

    // 通讯录、APP列表、短信上传
    public static final String HTTP_DATA_UPDATE_INFO = BuildConfig.PACKAGE_NAME + "creditUploadContents";
    // 上传相册中图片的meta数据
    public static final String HTTP_DATA_UPLOAD_META = BuildConfig.PACKAGE_NAME + "creditUploadMetadata";

    //同盾
    @Deprecated
    public static final String HTTP_DATA_TONGDUN_TASKID_INFO = BuildConfig.PACKAGE_NAME + "creditTdSnsCallback";
    //同盾设备信息
    @Deprecated
    public static final String HTTP_DATA_TONGDUN_EQUIPMENT_INFO = BuildConfig.PACKAGE_NAME + "creditTdEquipmentInfo";

    //</editor-fold>


    //<editor-fold desc="mmkv">

    public static final String MMKV_GROUP_APP = "mmkv_group_app";
    public static final String MMKV_GROUP_GLOBAL = "mmkv_group_global";
    public static final String MMKV_GROUP_DATAS = "mmkv_group_datas";
    public static final String MMKV_GROUP_LOGIN_INFO = "mmkv_group_login_info";
    public static final String MMKV_GROUP_SZLMENG = "mmkv_group_szlmeng";

    public static final String MMKV_API_AF_AF_STATUS = BuildConfig.PACKAGE_NAME + "afappsfflyer_status";
    public static final String MMKV_API_AF_MEDIA_SOURCE = BuildConfig.PACKAGE_NAME + "afmedia_source";
    public static final String MMKV_API_AF_CLICK_TIME = BuildConfig.PACKAGE_NAME + "afclicked_time";
    public static final String MMKV_API_AF_INSTALL_TIME = BuildConfig.PACKAGE_NAME + "afinstalled_time";

    public static final String MMKV_API_APP_KEY_URL_CONFI = BuildConfig.PACKAGE_NAME + "dev_url";// 后台开发人员的本机分支名称
    public static final String MMKV_API_APP_KEY_URL_BROWSER = BuildConfig.PACKAGE_NAME + "browser_url";// 直接进入webview的
    //判断是不是第一次进app 是的话暂时引导页
    public static final String MMKV_API_APP_KEY_IS_FIRST_LOGIN = BuildConfig.PACKAGE_NAME + "first_in";

    public static final String MMKV_API_GLOBAL_KEY_UPDATE_CONTENT = BuildConfig.PACKAGE_NAME + "update_content";
    public static final String MMKV_API_GLOBAL_KEY_COOKISE_DOMAINS = BuildConfig.PACKAGE_NAME + "cookies_domains";

    /**
     * 用户名:phone
     */
    public static final String MMKV_API_LOGIN_INFO_KEY_USER_NAME = BuildConfig.PACKAGE_NAME + "login_info_user_name";
    /**
     * sessionId
     */
    public static final String MMKV_API_LOGIN_INFO_KEY_SESSION_ID = BuildConfig.PACKAGE_NAME + "login_info_session_id";
    /**
     * 用户信息
     */
    public static final String MMKV_API_LOGIN_INFO_KEY_USER_DATA = BuildConfig.PACKAGE_NAME + "login_info_user_data";
    /**
     * 保存手机号登录的手机号
     */
    public static final String MMKV_API_LOGIN_INFO_KEY_PHONE = BuildConfig.PACKAGE_NAME + "login_info_phone";

    // CallLog的最后上传时间
    public static final String MMKV_API_DATA_CALLLOG_UPLOAD_TIME = BuildConfig.PACKAGE_NAME + "data_calllog_uploadtime";
    // SMS的最后上传时间
    public static final String MMKV_API_DATA_SMS_UPLOAD_TIME = BuildConfig.PACKAGE_NAME + "data_sms_uploadtime";

    public static final String MMKV_API_SHUZILM = BuildConfig.APPLICATION_ID;
    public static final String MMKV_API_SHUZILM_KEY_QUERY_ID = BuildConfig.PACKAGE_NAME + "szlm_query_id";
    public static final String SHUZILM_HANDLER_THREAD_NAME = "szlm_hander";

    //</editor-fold>

    //<editor-fold desc="mmkv_code">

    public static final String MMKV_API_INDEX_ACTIVITY_HINT = BuildConfig.PACKAGE_NAME + "indexActivityHint";//首页活动弹框 上次id
    /**
     * 首页弹窗 信息:存储了弹出过的弹窗数据；对应的java bean是HomePopStorage
     */
    public static final String MMKV_API_HOME_HOME_POP_INFOS = "home_pop_infos";
    /**
     * 记录用户日历提醒的唯一标识
     */
    public static final String MMKV_API_CALENDAR_UNIQUE = BuildConfig.PACKAGE_NAME + "calendar_remind";
    public static final String MMKV_API_FIRST_CC_PROCESS = BuildConfig.PACKAGE_NAME + "first_certification_process";//第一次认证流程
    public static final String MMKV_API_NOW_LEND_BTN = "nowLendBtn";//"马上借款"按钮跳转到申请借款主页时，屏蔽非点击事件埋点
    public static final String MMKV_API_FIRST_CC_MOBILE = BuildConfig.PACKAGE_NAME + "first_certification_mobile_url";//第一次认证流程,手机运营商url

    public static final String MMKV_API_LOGIN_INFO_KEY_UID = "uid";//uid
    public static final String MMKV_API_LOGIN_INFO_KEY_STATUS = "status";
    public static final String MMKV_API_LOGIN_INFO_KEY_JPUSH_REGISTRATION_ID = BuildConfig.PACKAGE_NAME + "jpush_registration_id";//
    public static final String MMKV_API_LOGIN_INFO_KEY_LOGININFO = "logininfo";//
    //性别
    public static final String MMKV_API_LOGIN_INFO_KEY_GENDER = "gender";
    public static final String MMKV_API_LOGIN_INFO_KEY_NICKNAME = BuildConfig.PACKAGE_NAME + "nickname";//昵称
    public static final String MMKV_API_LOGIN_INFO_KEY_IDENTIFY_STATE = "identify_state";//认证状态
    public static final String MMKV_API_LOGIN_INFO_KEY_VIP_STATE = BuildConfig.PACKAGE_NAME + "vip_state";//vip状态
    //accid
    public static final String MMKV_API_LOGIN_INFO_KEY_ACCID = "accid";
    //token
    public static final String MMKV_API_LOGIN_INFO_KEY_TOKEN = BuildConfig.PACKAGE_NAME + "token";

    public static final String MMKV_API_GLOBAL_KEY_APP_NAME = BuildConfig.PACKAGE_NAME + "name";
    public static final String MMKV_API_GLOBAL_KEY_APP_TEXT = BuildConfig.PACKAGE_NAME + "app_text";
    public static final String MMKV_API_GLOBAL_KEY_CITY = "city";
    public static final String MMKV_API_GLOBAL_KEY_URL_ALREADYVIPLINK = BuildConfig.PACKAGE_NAME + "alreadyviplink";//已经是会员 我的会员地址
    public static final String MMKV_API_GLOBAL_KEY_URL_ARGUMENTSLINK = "argumentslink";//用户协议地址
    public static final String MMKV_API_GLOBAL_KEY_URL_AUTHCENTERLINK = BuildConfig.PACKAGE_NAME + "authcenterlink";//认证中心地址
    public static final String MMKV_API_GLOBAL_KEY_URL_COMPLETEINFOLINK = "completeinfolink";//完善資料地址
    public static final String MMKV_API_GLOBAL_KEY_URL_FEMALEINVITELINK = "femaleinvitelink";//女性分享红包邀请
    public static final String MMKV_API_GLOBAL_KEY_URL_MYWALLETLINK = "mywalletlink";//我的钱包地址
    public static final String MMKV_API_GLOBAL_KEY_URL_VIPLINK = BuildConfig.PACKAGE_NAME + "viplink";//非会员 我的会员地址

    public static final String MMKV_API_GLOBAL_KEY_PRICE_BROADCAST = "price_broadcast";//发广播 价格
    public static final String MMKV_API_GLOBAL_KEY_PRICE_REDPACK_PHOTO = "price_redpack_photo";//红包照片 价格
    public static final String MMKV_API_GLOBAL_KEY_PRICE_PRIVATECHAT = "price_privatechat";//私聊 价格

    public static final String MMKV_API_GLOBAL_KEY_SUPPORT_PHONE = "support_phone";//客服电话

    public static final String CLIENT_TYPE = "android";

    //</editor-fold>

    //<editor-fold desc="database">
    public static final String APPLICATION_RECORD_MODEL_TABLE_NAME = "android_application_record";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_ID = "ID";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_USER_ID = "user_id";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_APP_NAME = "app_name";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_PACKAGE_NAME = "package_name";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_VERSION_NAME = "version_name";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_VERSION_CODE = "version_code";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_FIRST_INSTALL_TIME = "first_install_time";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_LAST_UPDATE_TIME = "last_update_time";
    public static final String APPLICATION_RECORD_MODEL_TABLE_COLUMN_KEY_IS_SYSMTEM_APP = "is_sysmtem_app";

    public static final String IMAGE_DIALOG_RECORD_MODEL_TABLE_NAME = "image_dialog_history";
    public static final String IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_ID = "id";
    public static final String IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_UNIQUE_ID = "uni_id";
    public static final String IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_TOTAL_SIZE = "total_size";
    public static final String IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_CURRENT_SHOW_SIZE = "current_show_size";
    //</editor-fold>

    //<editor-fold desc="CommonResponseCallback">
    public static final String COMMON_RESPONSE_CALLBACK_JAVA_SUFFIX = ".java";
    //</editor-fold>

    //<editor-fold desc="JumpOperationBinder">
    /**
     * 错误的跳转类型，不执行跳转或其他操作
     */
    public static final String JUMP_OPERATION_BINDER_UNKNOWN = "";
    //</editor-fold>

    //<editor-fold desc="AppsFlyerUtils">
    public static final String APPSFLYER_UTILS_TAG = "AppsFlyerUtils";
    //</editor-fold>

    //<editor-fold desc="EventName">
    public static final String EVENT_TRUECALLER_CLICK = "truecaller_click";// 点击truecaller进行登录
    public static final String EVENT_TRUECALLER_SDK_SUCCESS = "truecaller_sdk_success";// truecaller sdk返回正确，获取到了用户信息
    public static final String EVENT_TRUECALLER_SUCCESS = "truecaller_success";// 请求成功
    public static final String EVENT_TRUECALLER_FAILED = "truecaller_failed";// 请求失败


    public static final String EVENT_REGISTER_CLICK = "register_click";// 注册点击
    // 注册成功：不能删除，这个在af中已与facebook关联，除非以后取消或替换这个关联
    public static final String EVENT_REGISTER = "register";
    public static final String EVENT_REGISTER_SUCCESS = "register_success";// 注册成功
    public static final String EVENT_REGISTER_FAILED = "register_failed";// 注册失败

    public static final String EVENT_LOGIN_CLICK = "login_click";// 登录点击
    @Deprecated
    public static final String EVENT_LOGIN = "login";// 登录成功
    public static final String EVENT_LOGIN_SUCCESS = "login_success";// 登录成功
    public static final String EVENT_LOGIN_SUCCESS_OTP = "login_success_otp";// 登录--验证码登录
    public static final String EVENT_LOGIN_SUCCESS_PASSWORD = "login_success_password";// 登录--密码登录
    public static final String EVENT_LOGIN_FAILED = "login_failed";// 登录失败


    public static final String EVENT_HOME = "home";// 点击首页按钮
    public static final String EVENT_HOME_ENTER = "home_enter";// 进入首页


    public static final String EVENT_AUTH_PAN_CLICK = "auth_pan_click";// pan卡认证点击
    public static final String EVENT_AUTH_PAN_UPLOAD = "auth_pan_upload";// pan卡上传
    @Deprecated
    public static final String EVENT_AUTH_EKYC_PAN = "authekyc_pan";// pan卡上传成功
    public static final String EVENT_AUTH_PAN_UPLOAD_SUCCESS = "auth_pan_upload_success";// pan卡上传成功
    public static final String EVENT_AUTH_PAN_UPLOAD_FAILED = "auth_pan_upload_failed";// pan卡上传失败

    public static final String EVENT_AUTH_LIVENESS_CLICK = "auth_liveness_click";// 活体识别：点击，开始识别
    public static final String EVENT_AUTH_LIVENESS_SDK_SUCCESS = "auth_liveness_sdk_success";// 活体识别：SDK识别成功
    public static final String EVENT_AUTH_LIVENESS_UPLOAD_SUCCESS = "auth_liveness_upload_success";// 活体识别：上传成功
    public static final String EVENT_AUTH_LIVENESS_UPLOAD_FAILED = "auth_liveness_upload_failed";// 活体识别：上传失败

    @Deprecated
    public static final String EVENT_AUTH_EKYC = "authekyc";// kyc页面点击
    public static final String EVENT_AUTH_KYC_CLICK = "auth_kyc_click";// kyc页面点击
    public static final String EVENT_AUTH_KYC_SUCCESS = "auth_kyc_success";// kyc认证成功
    public static final String EVENT_AUTH_KYC_FAILED = "auth_kyc_failed";// kyc认证失败


    public static final String EVENT_AUTH_AADHAAR_SUBMIT = "auth_aadhaar_submit";// aadhaar认证：成功
    public static final String EVENT_AUTH_AADHAAR_SUBMIT_SUCCESS = "auth_aadhaar_sumit_success";// aadhaar认证：成功

    public static final String EVENT_AUTH_ADDRESS_CLICK = "auth_address_click";// 地址证明认证：点击提交
    public static final String EVENT_AUTH_ADDRESS_SUCCES = "auth_address_success";// 地址证明认证：点击成功

    public static final String EVENT_AUTH_CONTACT_SUCCESS = "auth_contact";// 紧急联系人点击保存按钮
    public static final String EVENT_AUTH_CONTACT_SAVE_SUCCESS = "auth_contact_save_success";//// 紧急联系人保存成功


    public static final String EVENT_LOAN = "loan";// 点击借款 --> h5使用了
    //</editor-fold>

    //<editor-fold desc="DLocationUtils">
    public static final String D_LOCATION_UTILS_TAG = "DLocationUtils";
    //</editor-fold>

    //<editor-fold desc="AddressCardAuthTypeChooseVh">
    public static final String SHOW_TEXT_AADHAAR = "Aadhaar";
    public static final String SHOW_TEXT_VOTER_ID = "Voter ID";
    public static final String SHOW_TEXT_PASSPORT = "Passport";
    public static final String SHOW_TEXT_DRIVER_LICENSE = "Driving Licence";
    //</editor-fold>

    //<editor-fold desc="MainFragmentUtils">
    public static final int MAIN_FRAGMENT_TAB_TYPE_MAIN = 1;
    //code>home
    public static final int MAIN_FRAGMENT_TAB_HOME_MEN_USER = -1;
    public static final int MAIN_FRAGMENT_TAB_HOME_RECOMMENDLIST = -2;
    public static final int MAIN_FRAGMENT_TAB_HOME_WOMEN_USER = -3;
    //code>messgaecenter
    public static final int MAIN_FRAGMENT_TAB_MESSAGE_CENTER = -4;
    public static final int MAIN_FRAGMENT_TAB_MESSAGE_LIST = -5;
    //code>mine
    public static final int MAIN_FRAGMENT_TAB_MINE_HOME = -6;
    public static final int MAIN_FRAGMENT_TAB_MINE_MINE = -7;
    //code>radio_hall
    public static final int MAIN_FRAGMENT_TAB_RADIO_DETAIL_COMMENTS = -8;
    public static final int MAIN_FRAGMENT_TAB_RADIO_DETAIL_PRAISE = -9;
    public static final int MAIN_FRAGMENT_TAB_RADIO_HALL = -10;
    //</editor-fold>

    //<editor-fold desc="AddressCardAuthConfigResponseModel">
    public static final int ADDRESS_CARD_AUTH_RESPONSE_UNKNOWN = 0;
    public static final int ADDRESS_CARD_AUTH_RESPONSE_VOTERID = 1;
    public static final int ADDRESS_CARD_AUTH_RESPONSE_PASSPORT = 2;
    public static final int ADDRESS_CARD_AUTH_RESPONSE_DRIVER = 3;
    public static final int ADDRESS_CARD_AUTH_RESPONSE_AADHAAR = 4;
    //</editor-fold>

    //<editor-fold desc="AddressProofFileType">
    public static final int ADDRESS_PROOF_FILE_TYPE_VOTER = 1;
    public static final int ADDRESS_PROOF_FILE_TYPE_PASSPORT = 2;
    public static final int ADDRESS_PROOF_FILE_TYPE_DRIVER = 3;
    public static final int ADDRESS_PROOF_FILE_TYPE_AADHAAR = 4;
    //</editor-fold>

}

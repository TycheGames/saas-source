package com.bigshark.android.core.component.browser;

/**
 * webview中的绑定对象
 *
 * @author Administrator
 */
public interface INativeJavascriptInterfaceObj {

    /**
     * 复制文本
     */
    void copyTextMethod(String text, String tip);

    /**
     * 拨打电话
     */
    void callPhoneMethod(final String tele);

    /**
     * 执行指令
     */
    void returnNativeMethod(String jumpJsonText);


    // 页面显示状态回调

    /**
     * 设置 H5页面显示在前台的回调方法的名称
     */
    void onShow(String callback);

    /**
     * 获取 H5页面显示在前台的回调方法的名称
     */
    String getOnShowCallback();

    /**
     * 设置 H5页面隐藏在前台的回调方法的名称
     */
    void onHide(String callback);

    /**
     * 获取 H5页面隐藏在前台的回调方法的名称
     */
    String getOnHideCallback();


    // APP信息

    @Deprecated
    String getHeaders();

    /**
     * 给h5获取APP配置信息的json字符串
     */
    String getAppAttributes();

    /**
     * 放到请求头上的json，要转为对象，设置到请求头上
     * 其中appInfo为加密字符串
     */
    String getHeadersContent();

    String getDeviceId();


    void reportAppsFlyerTrackEvent(String eventName, String eventValueText);


    // 借款申请页的方法

    /**
     * 必须的权限是否都已申请
     */
    boolean mustPermissionsHaveBeenApplied();

    /**
     * 申请必须权限
     */
    void applyMustPermissions(final String callbackFunctionName);

    /**
     * 上传数据
     */
    void uploadDataAfterApplyMustPermissions();


}
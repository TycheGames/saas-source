package com.bigshark.android.common.source.sharedpreferences;

import com.bigshark.android.core.component.BaseApplication;

/**
 * Created by Administrator on 2017/3/31.
 * APP全局相关的
 * TODO 编写python脚本，之后添加对应的key前缀（包括：key名称、key值...）
 * temp:/*{Build.appname}*\/
 */
public class SharedPreferencesApiApp {

    private static final String NAME = "app";

    private final SharedPreferencesUtils spHelper;

    private SharedPreferencesApiApp() {
        this.spHelper = new SharedPreferencesUtils(BaseApplication.app, NAME);
    }

    private static final class Helper {
        private static final SharedPreferencesApiApp INSTANCE = new SharedPreferencesApiApp();
    }

    public static SharedPreferencesApiApp instance() {
        return Helper.INSTANCE;
    }


    private static final String KEY_URL_CONFI = /*BuildConfig.PACKAGE_NAME +*/ "url_config";// 后台开发人员的本机分支名称
    private static final String KEY_URL_WEBVIEW = /*BuildConfig.PACKAGE_NAME +*/ "url_webview";// 直接进入webview的
//    private static final String KEY_MAIN_TABS_CONTENT = /*BuildConfig.PACKAGE_NAME +*/ "main_tabs_content";// 主页tab的数据


    public String getConfigUrl() {
        return spHelper.sp().getString(KEY_URL_CONFI, "http://192.168.8.101/developer/dev");
    }

    public void setConfigUrl(String devConfigUrl) {
        spHelper.edit().putString(KEY_URL_CONFI, devConfigUrl).apply();
    }

    public void setWebviewUrl(String webviewUrl) {
        spHelper.edit().putString(KEY_URL_WEBVIEW, webviewUrl).apply();
    }

    public String getWebviewUrl() {
        return spHelper.sp().getString(KEY_URL_WEBVIEW, "");
    }

//    /**
//     * @return 是否更新了tab，相同则不更新
//     */
//    public boolean updateMainTabs(List<MainTabItemResData> tabs) {
//        String oldMainTabContent = spHelper.sp().getString(KEY_MAIN_TABS_CONTENT, null);
//        KLog.d("oldMainTabContent :" + oldMainTabContent);
//        String currMainTabContent = ConvertUtils.toString(tabs);
//        KLog.d("currMainTabContent:" + currMainTabContent);
//        boolean noNeedUpdate = oldMainTabContent != null && oldMainTabContent.equals(currMainTabContent);
//        if (noNeedUpdate) {
//            return false;
//        }
//
//        edit()
//                .putString(KEY_MAIN_TABS_CONTENT, currMainTabContent)
//                .apply();
//        return true;
//    }

//    public List<MainTabItemResData> getMainTabs() {
//        String mainTabContent = spHelper.sp().getString(KEY_MAIN_TABS_CONTENT, null);
//        List<MainTabItemResData> tabs = ConvertUtils.toList(mainTabContent, MainTabItemResData.class);
//        return tabs == null ? Collections.<MainTabItemResData>emptyList() : tabs;
//    }


    private static final String APPSFLYER_AF_STATUS = /*BuildConfig.PACKAGE_NAME +*/ "appsflyer_af_status";
    private static final String APPSFLYER_MEDIA_SOURCE = /*BuildConfig.PACKAGE_NAME +*/ "appsflyer_media_source";
    private static final String APPSFLYER_CLICK_TIME = /*BuildConfig.PACKAGE_NAME +*/ "appsflyer_click_time";
    private static final String APPSFLYER_INSTALL_TIME = /*BuildConfig.PACKAGE_NAME +*/ "appsflyer_install_time";

    /**
     * 设置appsflyer的渠道参数
     *
     * @param afStatus    是自然量还是非自然量：(Organic, Non-organic)
     * @param mediaSource 渠道名：若为自然量，则为null
     * @param clickTime   点击时间：若为自然量，则为null；若为预装包与 install_time 一致，格式：2019-09-11 08:37:46.797
     * @param installTime 安装时间：若为自然量，则为null；若为预装包与 click_time 一致，格式：2019-09-11 08:37:46.797
     */
    public void setAppsFlyerInfo(String afStatus, String mediaSource, String clickTime, String installTime) {
        spHelper.edit()
                .putString(APPSFLYER_AF_STATUS, afStatus)
                .putString(APPSFLYER_MEDIA_SOURCE, mediaSource)
                .putString(APPSFLYER_CLICK_TIME, clickTime)
                .putString(APPSFLYER_INSTALL_TIME, installTime)
                .apply();
    }

    public String getAppsflyerAfStatus() {
        return spHelper.sp().getString(APPSFLYER_AF_STATUS, "");
    }

    public String getAppsflyerMediaSource() {
        return spHelper.sp().getString(APPSFLYER_MEDIA_SOURCE, "");
    }

    public String getAppsflyerClickTime() {
        return spHelper.sp().getString(APPSFLYER_CLICK_TIME, "");
    }

    public String getAppsflyerInstallTime() {
        return spHelper.sp().getString(APPSFLYER_INSTALL_TIME, "");
    }


    //判断是不是第一次进app 是的话暂时引导页
    private static final String KEY_FIRST_IN =  /*BuildConfig.PACKAGE_NAME +*/  "first_login";

    public boolean isFirstIn() {
        return spHelper.sp().getBoolean(KEY_FIRST_IN, true);
    }

    public void setFirstIn() {
        spHelper.edit().putBoolean(KEY_FIRST_IN, false).apply();
    }


}

package com.bigshark.android.mmkv;

import com.bigshark.android.utils.StringConstant;

import com.tencent.mmkv.MMKV;

/**
 * Created by Administrator on 2017/3/31.
 * APP全局相关的
 * TODO 编写python脚本，之后添加对应的key前缀（包括：key名称、key值...）
 * temp:/*{Build.appname}*\/
 */
public class MmkvApp {

    private final MMKV mmkv;

    private MmkvApp() {
        mmkv = MMKV.mmkvWithID(StringConstant.MMKV_GROUP_APP);
    }

    private static final class Helper {
        private static final MmkvApp INSTANCE = new MmkvApp();
    }

    public static MmkvApp instance() {
        return Helper.INSTANCE;
    }

//    private static final String KEY_MAIN_TABS_CONTENT = BuildConfig.PACKAGE_NAME +"main_tabs_content";// 主页tab的数据


    public String getConfigUrl() {
        return mmkv.decodeString(StringConstant.MMKV_API_APP_KEY_URL_CONFI, "http://192.168.11.21/dev");
    }

    public void setConfigUrl(String devConfigUrl) {
        mmkv.encode(StringConstant.MMKV_API_APP_KEY_URL_CONFI, devConfigUrl);
    }

    public void setBrowserUrl(String browserUrl) {
        mmkv.encode(StringConstant.MMKV_API_APP_KEY_URL_BROWSER, browserUrl);
    }

    public String getWebviewUrl() {
        return mmkv.decodeString(StringConstant.MMKV_API_APP_KEY_URL_BROWSER, "");
    }

    /**
     * 设置appsflyer的渠道参数
     *
     * @param afStatus    是自然量还是非自然量：(Organic, Non-organic)
     * @param mediaSource 渠道名：若为自然量，则为null
     * @param clickTime   点击时间：若为自然量，则为null；若为预装包与 install_time 一致，格式：2019-09-11 08:37:46.797
     * @param installTime 安装时间：若为自然量，则为null；若为预装包与 click_time 一致，格式：2019-09-11 08:37:46.797
     */
    public void encodeAfInfo(String afStatus, String mediaSource, String clickTime, String installTime) {
        mmkv.encode(StringConstant.MMKV_API_AF_AF_STATUS, afStatus);
        mmkv.encode(StringConstant.MMKV_API_AF_MEDIA_SOURCE, mediaSource);
        mmkv.encode(StringConstant.MMKV_API_AF_CLICK_TIME, clickTime);
        mmkv.encode(StringConstant.MMKV_API_AF_INSTALL_TIME, installTime);
    }

    public String getAppsflyerAfStatus() {
        return mmkv.decodeString(StringConstant.MMKV_API_AF_AF_STATUS, "");
    }

    public String getAppsflyerMediaSource() {
        return mmkv.decodeString(StringConstant.MMKV_API_AF_MEDIA_SOURCE, "");
    }

    public String getAppsflyerClickTime() {
        return mmkv.decodeString(StringConstant.MMKV_API_AF_CLICK_TIME, "");
    }

    public String getAppsflyerInstallTime() {
        return mmkv.decodeString(StringConstant.MMKV_API_AF_INSTALL_TIME, "");
    }

    public boolean isFirstIn() {
        return mmkv.getBoolean(StringConstant.MMKV_API_APP_KEY_IS_FIRST_LOGIN, true);
    }

    public void setFirstIn() {
        mmkv.encode(StringConstant.MMKV_API_APP_KEY_IS_FIRST_LOGIN, false);
    }


    //<editor-fold desc="code">

    public int getIndexActivityHint() {
        return mmkv.decodeInt(StringConstant.MMKV_API_INDEX_ACTIVITY_HINT, -1);
    }

    public void setIndexActivityHint(int dev) {
        mmkv.encode(StringConstant.MMKV_API_INDEX_ACTIVITY_HINT, dev);
    }

    public String getHomePopInfos() {
        return mmkv.decodeString(StringConstant.MMKV_API_HOME_HOME_POP_INFOS, "");
    }

    public void setHomePopInfos(String json) {
        mmkv.encode(StringConstant.MMKV_API_HOME_HOME_POP_INFOS, json);
    }

    public boolean getNowLendBtnPoint() {
        return mmkv.decodeBool(StringConstant.MMKV_API_NOW_LEND_BTN, false);
    }

    public void setNowLendBtnPoint(boolean isNowLend) {
        mmkv.encode(StringConstant.MMKV_API_NOW_LEND_BTN, isNowLend);
    }

    public void setCalendarRemindUnique(String uniqueid) {
        mmkv.encode(StringConstant.MMKV_API_CALENDAR_UNIQUE + getUid(), uniqueid);
    }

    public String getCalendarRemindUnique() {
        return mmkv.decodeString(StringConstant.MMKV_API_CALENDAR_UNIQUE + getUid(), null);
    }

    private String getUid() {
        return MmkvGroup.loginInfo().getUID();
    }

    public int getFisrtCCProcess() {
        return mmkv.decodeInt(StringConstant.MMKV_API_FIRST_CC_PROCESS, 1);
    }

    public void setFirstCCProcess(int status) {
        mmkv.encode(StringConstant.MMKV_API_FIRST_CC_PROCESS, status);
    }


    public String getFisrtCCMobileUrl() {
        return mmkv.decodeString(StringConstant.MMKV_API_FIRST_CC_MOBILE, "");
    }

    public void setFirstCCMobileUrl(String url) {
        mmkv.encode(StringConstant.MMKV_API_FIRST_CC_MOBILE, url);
    }

    //</editor-fold>

}

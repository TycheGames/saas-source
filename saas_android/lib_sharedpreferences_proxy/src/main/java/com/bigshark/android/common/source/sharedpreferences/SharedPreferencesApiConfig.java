package com.bigshark.android.common.source.sharedpreferences;

import android.content.SharedPreferences;

import com.bigshark.android.core.component.BaseApplication;
import com.bigshark.android.core.utils.ConvertUtils;

import java.util.HashMap;
import java.util.List;
import java.util.Map;


/**
 * Created by Administrator on 2017/3/31.
 * 下发的配置
 */
public class SharedPreferencesApiConfig {

    private static final String NAME = "config";

    private final SharedPreferencesUtils spHelper;

    private SharedPreferencesApiConfig() {
        this.spHelper = new SharedPreferencesUtils(BaseApplication.app, NAME);
    }

    private static final class Helper {
        private static final SharedPreferencesApiConfig INSTANCE = new SharedPreferencesApiConfig();
    }

    public static SharedPreferencesApiConfig instance() {
        return Helper.INSTANCE;
    }


    private static final String KEY_UPDATE_MSG = /*BuildConfig.PACKAGE_NAME +*/ "update_msg";
    private static final String KEY_SHARE_COOKIE_DOMAIN = /*BuildConfig.PACKAGE_NAME +*/ "share_cookie_domain";


    public void setConfigInfos(String updateMsg, List<String> shareCookieDomain, Map<String, String> dataUrls) {
        SharedPreferences.Editor edit = spHelper.edit();

        edit.putString(KEY_UPDATE_MSG, updateMsg);
        edit.putString(KEY_SHARE_COOKIE_DOMAIN, ConvertUtils.toString(shareCookieDomain));
        for (HashMap.Entry<String, String> urlEntry : dataUrls.entrySet()) {
            edit.putString(getCacheUrlRealKey(urlEntry.getKey()), urlEntry.getValue());
        }

        edit.apply();
    }


    public String getUpdateMsg() {
        return spHelper.sp().getString(KEY_UPDATE_MSG, "");
    }

    public void clearUpdateMsg() {
        spHelper.edit().putString(KEY_UPDATE_MSG, null).apply();
    }

    public List<String> getShareCookieDomains() {
        String cookieDomainText = spHelper.sp().getString(KEY_SHARE_COOKIE_DOMAIN, "");
        return ConvertUtils.toList(cookieDomainText, String.class);
    }

    /**
     * 缓存的URL，历史的URL也会存在在里面
     */
    public String getCacheUrl(String urlKey) {
        return spHelper.sp().getString(getCacheUrlRealKey(urlKey), "");
    }

    /**
     * 缓存URL key的实际名称
     */
    private static String getCacheUrlRealKey(String urlKey) {
        return /*BuildConfig.PACKAGE_NAME +*/ "url_" + urlKey;
    }

}

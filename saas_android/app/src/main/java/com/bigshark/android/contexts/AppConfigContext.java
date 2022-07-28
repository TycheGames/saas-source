package com.bigshark.android.contexts;

import android.webkit.URLUtil;

import com.bigshark.android.http.model.app.ConfigResponseModel;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.tencent.bugly.crashreport.CrashReport;

import java.util.Collections;
import java.util.Map;

/**
 * 存储：config接口的数据
 */
public class AppConfigContext {

    // 保存一些常用的配置
    private static final AppConfigContext INSTANCE = new AppConfigContext();
    private String homeImageDialogCommand;
    private boolean canUseTruecaller;// 输入手机号页面，是否有truecaller登录的功能

    public static AppConfigContext instance() {
        return INSTANCE;
    }


    private Map<String, String> urlCache = Collections.emptyMap();//服务地址配置

    private AppConfigContext() {
        PersonalContext.instance().iniLoginInfo();
    }

    /**
     * 初始化url配置
     */
    public void initServiceConfig(ConfigResponseModel configData) {
        if (configData == null) {
            return;
        }

        canUseTruecaller = configData.isOpenTruecaller();
        homeImageDialogCommand = configData.getHomeImageDialogCommand();
        urlCache = configData.getDataUrl();
        urlCache.put(StringConstant.HTTP_PERSONAL_PROTOCO_AGREEMENT, configData.getUser_agreement_url());
        urlCache.put(StringConstant.HTTP_PERSONAL_PROTOCO_PRIVACY_POLICY, configData.getPrivacyPolicyUrl());
        urlCache.put(StringConstant.HTTP_PERSONAL_PROTOCO_TERMS_OF_USER, configData.getTermsOfUseUrl());

        MmkvGroup.global().setConfigInfos(configData, configData.getUpdateMsg(), configData.getShareCookieDomain(), configData.getDataUrl());
    }

    /**
     * 根据key获取url
     */
    public String getRealUrl(String key) {
        if (urlCache.containsKey(key)) {
            String url = urlCache.get(key);
            if (URLUtil.isNetworkUrl(url)) {
                return url;
            }
            CrashReport.postCatchedException(new Throwable("url-->url is not network url, key:" + key + ", url:" + url));
        } else {
            CrashReport.postCatchedException(new Throwable("url-->unknown key:" + key));
        }

        String cacheUrl = MmkvGroup.global().getCacheUrl(key);
        if (!URLUtil.isNetworkUrl(cacheUrl)) {
            CrashReport.postCatchedException(new Throwable("url-->cache url is not network url, key:" + key + ", cacheUrl:" + cacheUrl));
        }
        return cacheUrl;
    }

    public String getHomeImageDialogCommand() {
        return homeImageDialogCommand;
    }

    public boolean isCanUseTruecaller() {
        return canUseTruecaller;
    }
}

package com.bigshark.android.utils.thirdsdk;

import android.app.Activity;
import android.app.Application;
import android.content.Context;

import com.appsflyer.AppsFlyerConversionListener;
import com.appsflyer.AppsFlyerLib;
import com.bigshark.android.BuildConfig;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;

import java.util.HashMap;
import java.util.Map;

/**
 * appsflyer的封装
 */
public class AppsFlyerUtils {

    public static void initConfig(Application app) {
        AppsFlyerLib.getInstance().setDebugLog(true);
        // 获取转化数据
        AppsFlyerConversionListener conversionDataListener = new AppsFlyerConversionListener() {
            @Override
            public void onInstallConversionDataLoaded(Map<String, String> conversionData) {
                for (String attrName : conversionData.keySet()) {
                    KLog.d(StringConstant.APPSFLYER_UTILS_TAG, "onInstallConversionDataLoaded: " + attrName + " = " + conversionData.get(attrName));
                }

                // 第一次安装的时候，获取并保存渠道信息
                String isFirstLaunch = conversionData.get("is_first_launch");
                if (Boolean.parseBoolean(isFirstLaunch)) {
                    String afStatus = conversionData.get("af_status");// 两个值：Non-organic，Organic
                    String mediaSource = conversionData.get("media_source");
                    String installTime = conversionData.get("install_time");
                    String clickTime = conversionData.get("click_time");
                    KLog.d(StringConstant.APPSFLYER_UTILS_TAG, ", clickTime:" + clickTime + ", installTime:" + installTime + ", afStatus:" + afStatus + ", mediaSource:" + mediaSource);
                    MmkvGroup.app().encodeAfInfo(afStatus, mediaSource, clickTime, installTime);
                }
            }

            @Override
            public void onInstallConversionFailure(String errorMessage) {
                KLog.d(StringConstant.APPSFLYER_UTILS_TAG, "error getting conversion data: " + errorMessage);
            }

            @Override
            public void onAppOpenAttribution(Map<String, String> conversionData) {
                for (String attrName : conversionData.keySet()) {
                    KLog.d(StringConstant.APPSFLYER_UTILS_TAG, "onAppOpenAttribution: " + attrName + " = " + conversionData.get(attrName));
                }
            }

            @Override
            public void onAttributionFailure(String errorMessage) {
                KLog.d(StringConstant.APPSFLYER_UTILS_TAG, "error onAttributionFailure : " + errorMessage);
            }
        };
//        AppsFlyerLib.getInstance().setPreinstallAttribution("<MEDIA_SOURCE_NAME>", "Campaign Name", "123");
        AppsFlyerLib.getInstance().init(BuildConfig.AF_DEV_KEY, conversionDataListener, app.getApplicationContext());
        AppsFlyerLib.getInstance().setImeiData(ViewUtil.getDeviceId(app.getApplicationContext()));
        AppsFlyerLib.getInstance().startTracking(app);

        // 设置第三方应用商店
        if (BuildConfig.AF_STORE != null && !BuildConfig.AF_STORE.isEmpty()) {
            AppsFlyerLib.getInstance().setOutOfStore(BuildConfig.AF_STORE);
        }
    }


    public static void sendDeepLinkData(Activity activity) {
//        AppsFlyerLib.getInstance().sendDeepLinkData(act);
    }


    //<editor-fold desc="event">

    public static void trackEvent(IDisplay display, String eventName, Map<String, String> eventValueMap) {
        Map<String, Object> eventValue = new HashMap<>(8);
        eventValue.putAll(eventValueMap);
        Context context = display.act().getApplicationContext();
        eventValue.put("appsFlyerId", AppsFlyerLib.getInstance().getAppsFlyerUID(context));
        AppsFlyerLib.getInstance().trackEvent(context, eventName, eventValue);
    }

    //</editor-fold>
}

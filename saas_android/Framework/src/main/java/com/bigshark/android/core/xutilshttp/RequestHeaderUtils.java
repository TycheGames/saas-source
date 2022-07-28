package com.bigshark.android.core.xutilshttp;

import android.content.Context;
import android.support.annotation.NonNull;

import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.core.utils.encry.EncryAesUtils;
import com.bigshark.android.core.utils.encry.EncryRsaUtils;
import com.tencent.bugly.crashreport.CrashReport;

import java.io.UnsupportedEncodingException;
import java.net.URLEncoder;
import java.util.Collections;
import java.util.HashMap;
import java.util.Map;


/**
 * http请求头
 */
public class RequestHeaderUtils {

    // 给h5使用的
    private static final Map<String, String> APP_ATTRIBUTES = new HashMap<>(16);
    // 请求头中的appInfo的value
    private static final Map<String, String> APP_INFOS = new HashMap<>(32);

    private static Callback mCallback;
    private static PutParamCallback mPutParamCallback;
    private static String appVersion;

    public static void init(Context context, String appMarket, String bundleId,
                            String appVersion, String appVersionShow, String packageName,
                            @NonNull Callback callback, PutParamCallback putParamCallback) {
        RequestHeaderUtils.appVersion = appVersion;
        RequestHeaderUtils.mCallback = callback;
        RequestHeaderUtils.mPutParamCallback = putParamCallback;

        Map<String, String> baseInfos = getBaseInfos(appMarket, bundleId, appVersion, appVersionShow, packageName, putParamCallback);
        APP_ATTRIBUTES.putAll(baseInfos);
        APP_INFOS.putAll(baseInfos);

        // 手机
        putParamCallback.handlePutIfCan(APP_INFOS, "deviceId", ViewUtil.getDeviceId(context));
        putParamCallback.handlePutIfCan(APP_INFOS, "screenWidth", ViewUtil.getScreenWidth(context) + "");
        putParamCallback.handlePutIfCan(APP_INFOS, "screenHeight", ViewUtil.getScreenHeight(context) + "");

        // 定位
        putParamCallback.handlePutIfCan(APP_INFOS, "longitude", "");
        putParamCallback.handlePutIfCan(APP_INFOS, "latitude", "");

        putParamCallback.handlePutIfCan(APP_INFOS, "szlmQueryId", "");// 数盟
        putParamCallback.handlePutIfCan(APP_INFOS, "googlePushToken", "");// Google推送
        putParamCallback.handlePutIfCan(APP_INFOS, "tdBlackbox", "");// 同盾
        putParamCallback.handlePutIfCan(APP_INFOS, "timestamp", "");// 时间戳
    }

    /**
     * 基础APP信息
     */
    private static Map<String, String> getBaseInfos(String appMarket, String bundleId,
                                                    String appVersion, String appVersionShow, String packageName,
                                                    PutParamCallback putParamCallback) {
        Map<String, String> baseInfos = new HashMap<>(16);
        // 手机
        putParamCallback.handlePutIfCan(baseInfos, "clientType", "android");
        putParamCallback.handlePutIfCan(baseInfos, "brandName", ViewUtil.getBrandName());
        putParamCallback.handlePutIfCan(baseInfos, "deviceName", ViewUtil.getDeviceName());
        putParamCallback.handlePutIfCan(baseInfos, "osVersion", ViewUtil.getOsVersion());
//         putIfCan(baseInfos, "deviceId", ViewUtil.getDeviceId(context));
//         putIfCan(baseInfos, "screenWidth", ViewUtil.getScreenWidth(context) + "");
//         putIfCan(baseInfos, "screenHeight", ViewUtil.getScreenHeight(context) + "");
        // app
        putParamCallback.handlePutIfCan(baseInfos, "bundleId", bundleId);
        putParamCallback.handlePutIfCan(baseInfos, "appVersion", appVersion);
        putParamCallback.handlePutIfCan(baseInfos, "appVersionShow", appVersionShow);
        putParamCallback.handlePutIfCan(baseInfos, "packageName", packageName);
        putParamCallback.handlePutIfCan(baseInfos, "appMarket", appMarket);

        return baseInfos;
    }


    public interface PutParamCallback {
        void handlePutIfCan(Map<String, String> map, String key, String value);
    }


    // ****************** header ******************

    public static String getAppAttributes() {
        return ConvertUtils.toString(APP_ATTRIBUTES);
    }

    public static String getHeadersContent() {
        return ConvertUtils.toString(getHeaders());
    }

    public static Map<String, String> getHeaders() {
        Map<String, String> headers = new HashMap<>(4);
        headers.put("appVersion", appVersion);

        Map<String, String> contentMap = new HashMap<>(APP_INFOS);
        contentMap.put("timestamp", System.currentTimeMillis() + "");
        String content = ConvertUtils.toString(contentMap);
        RequestHeaderBean headerBean = new RequestHeaderBean(content);

        headers.put("appInfo", headerBean.content);
        headers.put("appKey", headerBean.key);
        headers.put("appIv", headerBean.iv);
        return headers;
    }

    /**
     * 加密请求头
     */
    public static class RequestHeaderBean {
        private String content;
        private String key;
        private String iv;

        public RequestHeaderBean(String content) {
            String keyText = create();
            String ivText = create();

            try {
                String aesContent = EncryAesUtils.encryptNew(content, keyText, ivText);
                this.content = URLEncoder.encode(aesContent, "UTF-8");
            } catch (UnsupportedEncodingException e) {
                e.printStackTrace();
                CrashReport.postCatchedException(new Throwable("request header encrypt error"));
            }

            try {
                String encryptKey = EncryRsaUtils.encrypt(keyText);
                this.key = URLEncoder.encode(encryptKey, "UTF-8");

                String encryptIv = EncryRsaUtils.encrypt(ivText);
                this.iv = URLEncoder.encode(encryptIv, "UTF-8");
            } catch (UnsupportedEncodingException e) {
                e.printStackTrace();
                CrashReport.postCatchedException(new Throwable("request header encrypt error"));
            }
        }

        public static String create() {
            StringBuilder str = new StringBuilder();
            for (int i = 0; i < 16; i++) {
                // 你想生成几个字符的，就把9改成几，如果改成１,那就生成一个随机字母．
                str.append((char) (Math.random() * 26 + 'a'));
            }
            return str.toString();
        }
    }


    // ****************** cookie ******************

    public static Map<String, String> getCookies() {
        if (mCallback == null) {
            return Collections.emptyMap();
        }
        String sessionid = mCallback.getSessionId();
        if (StringUtil.isBlank(sessionid)) {
            return Collections.emptyMap();
        }
        Map<String, String> header = new HashMap<>(1);
        header.put("Cookie", "SESSIONID=" + sessionid);
        return header;
    }


    // ****************** 设置实时的数据 ******************

    public static void changeLoaction(String longitude, String latitude) {
        if (mPutParamCallback != null) {
            mPutParamCallback.handlePutIfCan(APP_INFOS, "longitude", longitude);
            mPutParamCallback.handlePutIfCan(APP_INFOS, "latitude", latitude);
        } else {
            APP_INFOS.put("longitude", longitude);
            APP_INFOS.put("latitude", latitude);
        }
    }

    public static void updateShuZiLm(String queryId) {
        if (mPutParamCallback != null) {
            mPutParamCallback.handlePutIfCan(APP_INFOS, "szlmQueryId", queryId);
        } else {
            APP_INFOS.put("szlmQueryId", queryId);
        }
    }

    public static void updateGooglePushToken(String googlePushToken) {
        if (mPutParamCallback != null) {
            mPutParamCallback.handlePutIfCan(APP_INFOS, "googlePushToken", googlePushToken);
        } else {
            APP_INFOS.put("googlePushToken", googlePushToken);
        }
    }

    public static void setTdBlackboxText(String blackBox) {
        if (mPutParamCallback != null) {
            mPutParamCallback.handlePutIfCan(APP_INFOS, "tdBlackbox", blackBox);
        } else {
            APP_INFOS.put("tdBlackbox", blackBox);
        }
    }

    public static void tryUpdateDeviceId(Context context) {
        if (mPutParamCallback != null) {
            mPutParamCallback.handlePutIfCan(APP_INFOS, "deviceId", ViewUtil.getDeviceId(context));
        } else {
            APP_INFOS.put("deviceId", ViewUtil.getDeviceId(context));
        }
    }


    public interface Callback {
        String getSessionId();
    }

}

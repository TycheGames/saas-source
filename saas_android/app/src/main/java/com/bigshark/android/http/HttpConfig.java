package com.bigshark.android.http;

import android.content.Context;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.contexts.AppConfigContext;
import com.bigshark.android.core.common.event.NetWorkWrapperEvent;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.core.xutilshttp.UserAgent;
import com.bigshark.android.mmkv.MmkvGroup;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.util.Map;

import de.greenrobot.event.EventBus;
import okhttp3.Headers;

/**
 * Created by Administrator on 2017/3/6.
 */
public class HttpConfig {


    public static void configMainData(Context context) {
        UserAgent.newInstance(BuildConfig.HTTP_UA_UNIQUE_IDENTIFIER, BuildConfig.VERSION_NAME_SERVICE, new UserAgent.Callback() {
            @Override
            public StringBuffer checkTargetSb(StringBuffer buffer, StringBuffer newBuffer) {
                try {
                    new Headers.Builder().add("check", newBuffer.toString());
                    return newBuffer;
                } catch (IllegalArgumentException e) {
                    e.printStackTrace();
                    return buffer;
                }
            }
        });

        RequestHeaderUtils.init(
                context,
                BuildConfig.APP_FILE_TAG,
                BuildConfig.APPLICATION_ID,
                BuildConfig.VERSION_NAME_SERVICE,
                BuildConfig.VERSION_NAME,
                BuildConfig.PACKAGE_NAME,
                new RequestHeaderUtils.Callback() {
                    @Override
                    public String getSessionId() {
                        return MmkvGroup.loginInfo().getSessionId();
                    }
                },
                new RequestHeaderUtils.PutParamCallback() {
                    @Override
                    public void handlePutIfCan(Map<String, String> map, String key, String value) {
                        try {
                            new Headers.Builder().add("check", value);
                            map.put(key, value);
                        } catch (IllegalArgumentException | NullPointerException e) {
                            String tipMsg = "request header error:" + "\n--> key:" + key + ", value:" + value + "\n--> app_infos:" + map.toString();
                            KLog.w(tipMsg, e);
                            CrashReport.postCatchedException(new Throwable(tipMsg, e));
                        }
                    }
                }
        );

        new EventController();
    }


    /**
     * Created by Administrator on 2017/12/14.
     * http中共通的响应处理
     * 全局的，不需要注销监听
     */
    public static final class EventController {

        private final LoginEventHelper loginEventHelper;

        public EventController() {
            EventBus.getDefault().register(this);
            loginEventHelper = new LoginEventHelper();
        }


        public void onEventMainThread(NetWorkWrapperEvent event) {
            switch (event.getCode()) {
                case NetWorkWrapperEvent.NETWORK_ERROR_NEED_LOGIN:
                    if (loginEventHelper != null) {
                        loginEventHelper.sendEvent();
                    }
                    break;
                default:
                    break;
            }
        }
    }


    private static String[] CURRENT_BASE_URLS = BuildConfig.NETWORK_URL_PRODUCT_LIST;

    public static void setCurrentServiceBaseUrl(String[] baseUrls) {
        if (baseUrls == null || baseUrls.length == 0) {
            return;
        }
        CURRENT_BASE_URLS = baseUrls;
    }

    /**
     * 获取baseUrls
     */
    public static String[] getBaseUrls() {
        return CURRENT_BASE_URLS == null ? BuildConfig.NETWORK_URL_PRODUCT_LIST : CURRENT_BASE_URLS;
    }

    /**
     * 根据key获取url
     *
     * @param key from NetWorkServiceConstant
     */
    public static String getRealUrl(String key) {
        return AppConfigContext.instance().getRealUrl(key);
    }


    public static String getUrl(String urlKey) {
        return HttpConfig.getRealUrl(urlKey);
    }
}

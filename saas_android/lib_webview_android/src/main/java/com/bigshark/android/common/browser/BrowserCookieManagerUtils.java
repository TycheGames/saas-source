package com.bigshark.android.common.browser;

import android.content.Context;

import java.util.Collections;
import java.util.LinkedHashMap;
import java.util.List;
import java.util.Map;

/**
 * 封装android的webview和x5的webview两个cookie
 *
 * @author Administrator
 * @date 2017/12/27
 */
public class BrowserCookieManagerUtils {

    public static void setCookie(Context context, String sessionId, String uid, List<String> shareCookieDomains) {
        clearCookie(context);
        setCookieForAndroid(context, sessionId, uid, shareCookieDomains);
    }

    private static void setCookieForAndroid(Context context, String sessionId, String uid, List<String> shareCookieDomains) {
        android.webkit.CookieSyncManager.createInstance(context);
        android.webkit.CookieManager cm = android.webkit.CookieManager.getInstance();
        LinkedHashMap<String, String> cookies = createCookies(sessionId, uid, shareCookieDomains);
        for (Map.Entry<String, String> cookieEntry : cookies.entrySet()) {
            cm.setCookie(cookieEntry.getKey(), cookieEntry.getValue());
        }
        android.webkit.CookieSyncManager.getInstance().sync();
    }

    private static LinkedHashMap<String, String> createCookies(String sessionId, String uid, List<String> shareCookieDomains) {
        LinkedHashMap<String, String> cookies = new LinkedHashMap<>(16);

        shareCookieDomains = shareCookieDomains != null ? shareCookieDomains : Collections.<String>emptyList();
        final String cookie = "SESSIONID=" + sessionId + ";UID=" + uid;

        for (String shareCookieDomain : shareCookieDomains) {
            cookies.put(shareCookieDomain, cookie);
        }
        return cookies;
    }


    public static void clearCookie(Context context) {
        clearCookieForAndroid(context);
    }

    private static void clearCookieForAndroid(Context context) {
        android.webkit.CookieSyncManager.createInstance(context);
        android.webkit.CookieManager cm = android.webkit.CookieManager.getInstance();
        cm.removeAllCookie();
        android.webkit.CookieSyncManager.getInstance().sync();
    }

}

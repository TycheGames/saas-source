package com.bigshark.android.common.browser;

import android.view.View;

import com.bigshark.android.core.common.event.UserGotoLoginPageEvent;
import com.bigshark.android.core.display.IDisplay;

import de.greenrobot.event.EventBus;

/**
 * webview的工具类
 *
 * @author Administrator
 * @date 2017/12/27
 */
public class BrowserUtils {


    private BrowserUtils() {
    }

    /**
     * 登录请求
     *
     * @return 是否为登录请求的URL
     */
    public static boolean handleLogin(IDisplay display, View view, String url) {
        boolean isLoginUrl = url.contains("LOANS://LOGIN");
        if (isLoginUrl) {
            EventBus.getDefault().post(new UserGotoLoginPageEvent(display));
        }
        return isLoginUrl;
    }
}

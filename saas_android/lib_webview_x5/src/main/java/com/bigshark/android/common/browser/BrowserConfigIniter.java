package com.bigshark.android.common.browser;


public class BrowserConfigIniter {

    /**
     * 开启Webview 调试
     */
    public static void openBrowserDebugSetting() {
        com.tencent.smtt.sdk.WebView.setWebContentsDebuggingEnabled(true);
    }
}

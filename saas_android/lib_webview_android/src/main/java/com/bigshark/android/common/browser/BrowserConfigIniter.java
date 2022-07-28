package com.bigshark.android.common.browser;


public class BrowserConfigIniter {

    /**
     * 开启Webview 调试
     */
    public static void openWebViewDebugSetting() {
        android.webkit.WebView.setWebContentsDebuggingEnabled(true);
    }
}

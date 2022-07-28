package com.bigshark.android.core.component.browser;

import android.content.Intent;

/**
 * 提供给h5的window.open使用的webview
 */
public interface IBrowserWindowWebView<T extends IBrowserWebView> {

    T getWebView();

    void close();

    void onActivityResult(int requestCode, int resultCode, Intent data);

}

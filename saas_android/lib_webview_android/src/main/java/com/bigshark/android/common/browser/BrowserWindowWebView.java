package com.bigshark.android.common.browser;

import android.content.Intent;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import com.bigshark.android.core.component.browser.INativeJavascriptInterfaceObj;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.component.browser.IBrowserWindowWebView;
import com.bigshark.android.core.component.browser.BrowserConfig;

/**
 * 提供给h5的window.open使用的webview
 */
public class BrowserWindowWebView implements IBrowserWindowWebView<BrowserWebView> {

    private IBrowserWebView.BrowserPage page;
    private BrowserWebView xWebView;


    public BrowserWindowWebView(IBrowserWebView.BrowserPage page, final BrowserConfig webViewConfig) {
        this.page = page;

        xWebView = (BrowserWebView) LayoutInflater.from(page.act()).inflate(R.layout.common_webview_h5window, null, false);
        xWebView.initConfig(page, new BrowserConfig.Builder()
                .setProxyCreater(new BrowserConfig.ProxyCreater() {
                    @Override
                    public INativeJavascriptInterfaceObj createNativeMethod() {
                        return webViewConfig.getProxyCreater().createNativeMethod();
                    }

                    @Override
                    public IBrowserWindowWebView createWindowWebView() {
                        return webViewConfig.getProxyCreater().createWindowWebView();
                    }
                }).build(), null);

        page.getContentView().addView(xWebView, new ViewGroup.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
    }

    @Override
    public BrowserWebView getWebView() {
        return xWebView;
    }

    @Override
    public void close() {
        xWebView.destroy();
        page.getContentView().removeView(xWebView);
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        xWebView.onActivityResult(requestCode, resultCode, data);
    }

}

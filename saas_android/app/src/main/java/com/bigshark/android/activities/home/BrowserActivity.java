package com.bigshark.android.activities.home;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.widget.RelativeLayout;

import com.bigshark.android.R;
import com.bigshark.android.common.browser.BrowserWebView;
import com.bigshark.android.common.browser.BrowserWindowWebView;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.core.component.browser.BrowserConfig;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.component.browser.IBrowserWindowWebView;
import com.bigshark.android.core.component.browser.INativeJavascriptInterfaceObj;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.nativejs.BrowserNativeJavascriptInterfaceObjectImpl;
import com.socks.library.KLog;

/**
 * @author Administrator
 */
public class BrowserActivity extends DisplayBaseActivity implements IBrowserWebView.BrowserPage {
    protected NavigationStatusLinearLayout titleView;

    private RelativeLayout contentView;
    private BrowserWebView mBrowserWebView;
    private BrowserConfig browserConfig;


    public static void goIntent(Activity activity, String url) {
        KLog.d("url:" + url);
        Intent intent = new Intent(activity, BrowserActivity.class);
        intent.putExtra(IBrowserWebView.EXTRA_URL, url);
        activity.startActivity(intent);
    }


    @Override
    protected int getLayoutId() {
        return R.layout.activity_common_browser;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        titleView = findViewById(R.id.common_browser_title);
        contentView = findViewById(R.id.common_browser_content);
        mBrowserWebView = findViewById(R.id.common_browser_webview);

        browserConfig = createWebViewConfig();
        mBrowserWebView.initConfig(this, browserConfig, null);
    }

    private BrowserConfig createWebViewConfig() {
        String url = getIntent().getStringExtra(IBrowserWebView.EXTRA_URL);
        String title = getIntent().getStringExtra(IBrowserWebView.EXTRA_BROWSER_TITLE);
        boolean isPush = getIntent().getBooleanExtra(IBrowserWebView.EXTRA_IS_PUSH, false);
        String jumpToHome = getIntent().getStringExtra(IBrowserWebView.EXTRA_GO_HOME_DISPLAYER);
        String authMethod = getIntent().getStringExtra(IBrowserWebView.EXTRA_AUTH_METHOD);

        return new BrowserConfig.Builder()
                .setUrl(url).setTitle(title)
                .setIsPush(isPush).setJumpToHome(jumpToHome)
                .setAuthMethod(authMethod)
                .setProxyCreater(new BrowserConfig.ProxyCreater() {
                    @Override
                    public INativeJavascriptInterfaceObj createNativeMethod() {
                        return new BrowserNativeJavascriptInterfaceObjectImpl(mBrowserWebView);
                    }

                    @Override
                    public IBrowserWindowWebView createWindowWebView() {
                        return new BrowserWindowWebView(BrowserActivity.this, browserConfig);
                    }
                })
                .build();
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    @Override
    public void setupDatas() {
        mBrowserWebView.loadUrl();
    }


    @Override
    public void onBackPressed() {
        mBrowserWebView.onBackPressedForActivity();
    }


    @Override
    protected void onResume() {
        super.onResume();
        if (mBrowserWebView != null) {
            mBrowserWebView.onShow();
        }
    }

    @Override
    protected void onPause() {
        if (mBrowserWebView != null) {
            mBrowserWebView.onHide();
        }
        super.onPause();
    }

    @Override
    protected void onDestroy() {
        mBrowserWebView.destroy();
        super.onDestroy();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        mBrowserWebView.onActivityResult(requestCode, resultCode, data);
    }


    //************* BrowserPage *************

    @Override
    public NavigationStatusLinearLayout getTitleView() {
        return titleView;
    }

    @Override
    public RelativeLayout getContentView() {
        return contentView;
    }

    @Override
    public IBrowserWebView getWebView() {
        return mBrowserWebView;
    }

}

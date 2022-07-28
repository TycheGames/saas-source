package com.bigshark.android.core.component.browser;

import android.content.Intent;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.view.View;
import android.widget.ImageView;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.core.display.IDisplay;

/**
 * 封装的WebView
 *
 * @author Administrator
 * @date 2017/12/27
 */
public interface IBrowserWebView {

    String EXTRA_BROWSER_TITLE = "extra_browser_title";
    String EXTRA_URL = "extra_url";
    String EXTRA_AUTH_METHOD = "extra_auth_method";
    String EXTRA_IS_PUSH = "extra_is_push";
    String EXTRA_GO_HOME_DISPLAYER = "JumpToHome";
    String EXTRA_GO_HOME_DISPLAYER_VALUE = "1";


    //************* config *************

    void initConfig(@NonNull BrowserPage page, @NonNull BrowserConfig config, @Nullable BrowserUIHandler uiHandler);

//    boolean onActivityResult(int requestCode, int resultCode, Intent data);


    //************* 设置标题 *************

    /**
     * webview所在页面的接口
     */
    interface BrowserPage extends IDisplay {

        BrowserPageTitle getTitleView();

        RelativeLayout getContentView();

        IBrowserWebView getWebView();
    }

    /**
     * webview页面的title
     */
    interface BrowserPageTitle {

        void setLeftClickListener(View.OnClickListener listener);

        ImageView getCloseImage();

        void setCloseClickListener(View.OnClickListener listener);

        void showCloseView();

        void hideCloseView();

        void setTitle(String text);

        TextView getRightTextView();

        ProgressBar getProgressBar();
    }

    BrowserPage getPage();

    void loadUrl();


    //************* 回退、关闭页面功能 *************

    /**
     * activity的返回键拦截
     * 只针对XWebView在activity中的逻辑，现在还没有在fragment中的XWebView
     */
    void onBackPressedForActivity();


    /**
     * 只针对XWebView在activity中的逻辑，现在还没有在fragment中的XWebView，命名区别与WebView的goBack方法，
     */
    void goBackForActivity();


    //************* webview的ui更改处理回调 *************

    /**
     * webview的ui更改处理回调：用于不同页面中，处理特殊的UI显示
     * tip: 只处理UI和listener
     * 都是在方法的最后面调用(覆盖掉前面的UI效果)
     */
    interface BrowserUIHandler {
        void onPageStarted();

        void onPageFinished(IBrowserWebView view, String url);
    }


    //************* view & webview *************

    boolean post(Runnable action);

    boolean postDelayed(Runnable action, long delayMillis);

    boolean canGoBack();

    void goBack();

    void loadUrl(String url);

    void setVisibility(int visibility);

    void clearHistory();

    void clearCache(boolean includeDiskFiles);

    void reload();

    void destroy();

    void onActivityResult(int requestCode, int resultCode, Intent data);
}

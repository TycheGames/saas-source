package com.bigshark.android.common.browser;

import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.net.Uri;
import android.os.Build;
import android.os.Message;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v7.app.AlertDialog;
import android.text.TextUtils;
import android.util.AttributeSet;
import android.util.Log;
import android.view.KeyEvent;
import android.view.View;

import com.bigshark.android.core.component.browser.BrowserConfig;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.component.browser.IBrowserWindowWebView;
import com.bigshark.android.core.component.browser.INativeJavascriptInterfaceObj;
import com.bigshark.android.core.utils.AndroidBug5497Workaround;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.core.xutilshttp.UserAgent;
import com.socks.library.KLog;
import com.tencent.smtt.export.external.interfaces.SslErrorHandler;
import com.tencent.smtt.sdk.CookieManager;
import com.tencent.smtt.sdk.ValueCallback;
import com.tencent.smtt.sdk.WebChromeClient;
import com.tencent.smtt.sdk.WebSettings;
import com.tencent.smtt.sdk.WebView;
import com.tencent.smtt.sdk.WebViewClient;

/**
 * 封装腾讯X5的WebView
 *
 * @author Administrator
 * @date 2017/12/27
 */
public class BrowserWebView extends WebView implements IBrowserWebView {

    private BrowserPage page;
    private BrowserConfig config;

    private BrowserUIHandler uiHandler;
    private BrowserFileChooserHandler fileChooserHandler;

    private INativeJavascriptInterfaceObj nativeMethodJsObj;


    public BrowserWebView(Context context) {
        super(context, null);
    }

    public BrowserWebView(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public BrowserWebView(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }


    //************* config *************

    @Override
    public void initConfig(@NonNull BrowserPage page, @NonNull BrowserConfig config, @Nullable BrowserUIHandler uiHandler) {
        this.page = page;
        this.config = config;
        this.uiHandler = uiHandler;
        configBuild();
//        if (config.getUrl() == null) {
//            //从首页webview 进入
//            //解决H5 输入框 被软键盘遮挡问题
//            AndroidBug5497Workaround.assistActivity(display.act());
//        }
        if (page instanceof Activity) {
            AndroidBug5497Workaround.assistActivity(page.act());
        }
        fileChooserHandler = new BrowserFileChooserHandler(page);
    }

    private void configBuild() {
        Log.i("XWebView", "url=" + config.getUrl());
        initTitle();
        if (nativeMethodJsObj == null) {
            nativeMethodJsObj = config.getProxyCreater().createNativeMethod();
        }

        initSetting();
        initLisenter();
        addJavascriptInterface(config.getUrl());
        setWebViewClient();
        setWebChromeClient();
        adapterHeight();
    }

    private void initSetting() {
        setScrollBarStyle(View.SCROLLBARS_OUTSIDE_OVERLAY);
        setSettings();//WebView属性设置！！！
    }

    private void setSettings() {
        WebSettings settings = getSettings();
        settings.setTextZoom(100);
        settings.setJavaScriptEnabled(true);
        settings.setUseWideViewPort(true);
        settings.setLayoutAlgorithm(WebSettings.LayoutAlgorithm.SINGLE_COLUMN);
        settings.setLoadWithOverviewMode(true);
        settings.setJavaScriptCanOpenWindowsAutomatically(true);//支持js调用window.open方法

        settings.setAllowFileAccess(true);// 设置可以访问文件
        settings.setSupportMultipleWindows(true);// 设置允许开启多窗口
        settings.setLoadsImagesAutomatically(true);    //支持自动加载图片

        //开启DOM形式存储
        settings.setDomStorageEnabled(true);
        //开启数据库形式存储
        settings.setDatabaseEnabled(true);
//        //缓存数据的存储地址
//        String appCacheDir = display.act().getDir("cache", Context.MODE_PRIVATE).getPath();
//        settings.setAppCachePath(appCacheDir);
//        //设置 应用 缓存目录
//        //开启缓存功能
//        settings.setAppCacheEnabled(true);
        //缓存模式
        settings.setCacheMode(WebSettings.LOAD_DEFAULT);

        settings.setUserAgentString(settings.getUserAgentString() + UserAgent.get());
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            settings.setMixedContentMode(android.webkit.WebSettings.MIXED_CONTENT_ALWAYS_ALLOW);
        }

        CookieManager.getInstance().setAcceptThirdPartyCookies(this, true);
    }

    private void initLisenter() {
        page.getTitleView().setLeftClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                page.act().onBackPressed();// 等同于点击返回键，会调用onBackPressed4Activity方法
            }
        });
        page.getTitleView().getCloseImage().setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                close4Activity();
            }
        });
        this.setDownloadListener(new com.tencent.smtt.sdk.DownloadListener() {
            @Override
            public void onDownloadStart(String url, String userAgent, String contentDisposition, String mimetype, long contentLength) {
                if (StringUtil.isBlank(url)) {
                    return;
                }
                Intent intent= new Intent();
                intent.setAction("android.intent.action.VIEW");
                intent.setData(Uri.parse(url));
                page.act().startActivity(intent);
            }
        });
    }

    private void addJavascriptInterface(String url) {
        addJavascriptInterface(nativeMethodJsObj, BrowserConfig.JAVASCRIPT_NATIVE_METHOD);
    }

    private void setWebViewClient() {
        setWebViewClient(new WebViewClient() {
            @Override
            public boolean shouldOverrideUrlLoading(WebView view, String url) {
                Log.i("X5WebView", "shouldOverrideUrlLoading url=" + url);
                if (BrowserUtils.handleLogin(page, view, url)) {
                    return true;
                }
                addJavascriptInterface(url);
                view.loadUrl(url, RequestHeaderUtils.getHeaders());
                return true;
            }

            @Override
            public void onReceivedSslError(WebView view, final SslErrorHandler handler, com.tencent.smtt.export.external.interfaces.SslError error) {
//                handler.proceed();  //接受所有证书
//                handler.cancel();  //不接受所有证书
                AlertDialog.Builder builder = new AlertDialog.Builder(page.act());
                builder.setMessage("Ssl certificate verification failed");
                builder.setPositiveButton("Continue", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        handler.proceed();
                    }
                });
                builder.setNegativeButton("Cancel", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        handler.cancel();
                    }
                });
                builder.setOnKeyListener(new DialogInterface.OnKeyListener() {
                    @Override
                    public boolean onKey(DialogInterface dialog, int keyCode, KeyEvent event) {
                        if (event.getAction() == KeyEvent.ACTION_UP && keyCode == KeyEvent.KEYCODE_BACK) {
                            handler.cancel();
                            dialog.dismiss();
                            return true;
                        }
                        return false;
                    }
                });
                AlertDialog dialog = builder.create();
                dialog.show();
            }

            @Override
            public void onPageStarted(WebView view, String url, Bitmap favicon) {
                super.onPageStarted(view, url, favicon);
                page.getTitleView().getRightTextView().setVisibility(View.GONE);

                if (uiHandler != null) {
                    uiHandler.onPageStarted();
                }
            }

            @Override
            public void onPageFinished(final WebView view, String url) {
                super.onPageFinished(view, url);
                page.getTitleView().getProgressBar().setProgress(100);
                page.getTitleView().getProgressBar().setVisibility(View.GONE);

                if (view.canGoBack()) {
                    page.getTitleView().showCloseView();
                } else {
                    page.getTitleView().hideCloseView();
                }
                if (!StringUtil.isBlank(view.getTitle())) {
                    refreshTitle(view.getTitle());
                }
                if (uiHandler != null) {
                    uiHandler.onPageFinished(BrowserWebView.this, url);
                }
            }
        });
    }

    private void setWebChromeClient() {
        setWebChromeClient(new WebChromeClient() {
            @Override
            public void onProgressChanged(WebView view, int newProgress) {
                page.getTitleView().getProgressBar().setVisibility(View.VISIBLE);
                page.getTitleView().getProgressBar().setProgress(newProgress);
            }

            @Override
            public void onReceivedTitle(WebView view, String title) {
                super.onReceivedTitle(view, title);
                refreshTitle(title);
            }

            // For Android < 3.0
            public void openFileChooser(ValueCallback<Uri> uploadMsg) {
                fileChooserHandler.openFileChooser(uploadMsg);
            }

            // For Android >=3.0
            public void openFileChooser(ValueCallback<Uri> uploadMsg, String acceptType) {
                fileChooserHandler.openFileChooser(uploadMsg, acceptType);
            }

            // For Android  >= 4.1.1
            @Override
            public void openFileChooser(ValueCallback<Uri> uploadMsg, String acceptType, String capture) {
                openFileChooser(uploadMsg, acceptType);
            }

            // For Android  >= 5.0
            @Override
            @SuppressLint("NewApi")
            public boolean onShowFileChooser(WebView webView, ValueCallback<Uri[]> filePathCallback, FileChooserParams fileChooserParams) {
                if (fileChooserParams != null && fileChooserParams.getAcceptTypes() != null
                        && fileChooserParams.getAcceptTypes().length > 0 && fileChooserParams.getAcceptTypes()[0].equals("image/*")) {
                    fileChooserHandler.onShowFileChooser(filePathCallback);
                } else {
                    fileChooserHandler.onReceiveValue();
                }
                return true;
            }

            private IBrowserWindowWebView webViewHelper;

            @Override
            public boolean onCreateWindow(WebView webView, boolean isDialog, boolean isUserGesture, Message resultMsg) {//html中调用window.open()，会回调此函数
                KLog.d("webview:" + BrowserWebView.this);

                //以下的操作应该就是让新的webview去加载对应的url等操作。
                WebView.WebViewTransport transport = (WebView.WebViewTransport) resultMsg.obj;
                webViewHelper = config.getProxyCreater().createWindowWebView();
                transport.setWebView((WebView) webViewHelper.getWebView());
                resultMsg.sendToTarget();

                return true;
            }

            @Override
            public void onCloseWindow(WebView webView) {//html中，用js调用.close(),会回调此函数
                super.onCloseWindow(webView);
                KLog.d("webview:" + BrowserWebView.this);
                if (webViewHelper != null) {
                    webViewHelper.close();
                    webViewHelper = null;
                }
            }
        });
    }

    /**
     * 1、适配如华为、三星的虚拟按键的机子
     * 2、Activity页面的webview才适配
     */
    private void adapterHeight() {
        if (!(getPage() instanceof Activity)) {
            return;
        }

        getPage().getMainHandler().postDelayed(new Runnable() {
            @Override
            public void run() {
                int incrementPaddingBottomIfRoom = AndroidBottomSoftBar.getNavigationBarHeightIfRoom(getPage().act());
                int incrementPaddingBottom = AndroidBottomSoftBar.getCurrentNavigationBarHeight(getPage().act());
                KLog.d("incrementPaddingBottomIfRoom:" + incrementPaddingBottom + ", incrementPaddingBottom:" + incrementPaddingBottomIfRoom);
                setPadding(getPaddingLeft(), getPaddingTop(), getPaddingRight(), getPaddingBottom() + incrementPaddingBottomIfRoom);
            }
        }, 100);
    }


    //************* 设置标题 *************

    private void initTitle() {
        if (!StringUtil.isBlank(config.getTitle())) {
            page.getTitleView().setTitle(config.getTitle());
        }
    }

    private void refreshTitle(String title) {
        if (StringUtil.isBlank(config.getTitle())) {
            page.getTitleView().setTitle(title);
        }
    }


    @Override
    public BrowserPage getPage() {
        return page;
    }

    @Override
    public void loadUrl() {
        loadUrl(config.getUrl(), RequestHeaderUtils.getHeaders());
    }


    //************* 回退、关闭页面功能 *************

    private void close4Activity() {
        goBackForActivity();
    }

    /**
     * activity的返回键拦截
     * 只针对XWebView在activity中的逻辑，现在还没有在fragment中的XWebView
     */
    @Override
    public void onBackPressedForActivity() {
        if (canGoBack()) {
            goBack();
        } else {
            goBackForActivity();
        }
    }

    /**
     * 只针对XWebView在activity中的逻辑，现在还没有在fragment中的XWebView，命名区别与WebView的goBack方法，
     */
    @Override
    public void goBackForActivity() {
        if (config.isPush()) {
            page.act().finish();
        } else {
            if (EXTRA_GO_HOME_DISPLAYER_VALUE.equals(config.getJumpToHome())) {
                page.act().finish();
            } else {
                page.act().finish();
            }
        }
    }

    public void onShow() {
        if (nativeMethodJsObj != null && !TextUtils.isEmpty(nativeMethodJsObj.getOnShowCallback())) {
            this.loadUrl("javascript:" + nativeMethodJsObj.getOnShowCallback() + "()");
        }
    }

    public void onHide() {
        if (nativeMethodJsObj != null && !TextUtils.isEmpty(nativeMethodJsObj.getOnHideCallback())) {
            this.loadUrl("javascript:" + nativeMethodJsObj.getOnHideCallback() + "()");
        }
    }


    @Override
    public void destroy() {
        this.loadUrl("javascript:destroy()");
        Log.i("XWebView", "destroy");
        clearHistory();
        super.destroy();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        fileChooserHandler.onActivityResult(requestCode, resultCode, data);
    }
}

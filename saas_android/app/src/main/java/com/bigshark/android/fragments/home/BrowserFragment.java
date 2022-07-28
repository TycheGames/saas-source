package com.bigshark.android.fragments.home;

import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.text.TextUtils;
import android.view.View;
import android.widget.RelativeLayout;

import com.bigshark.android.R;
import com.bigshark.android.common.browser.BrowserWebView;
import com.bigshark.android.common.browser.BrowserWindowWebView;
import com.bigshark.android.nativejs.BrowserNativeJavascriptInterfaceObjectImpl;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.component.browser.INativeJavascriptInterfaceObj;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.component.browser.IBrowserWindowWebView;
import com.bigshark.android.core.component.browser.BrowserConfig;
import com.bigshark.android.display.DisplayBaseFragment;

/**
 * Date: 2017/11/10
 * Email:
 * Description:
 * 发现功能 Webview 页面
 *
 * @author Administrator
 */
public class BrowserFragment extends DisplayBaseFragment implements IBrowserWebView.BrowserPage {

    private String fragmentUrl;
    private NavigationStatusLinearLayout titleView;
    private RelativeLayout contentView;
    private BrowserWebView mBrowserWebView;
    private BrowserConfig mBrowserConfig;

    public String getFragmentUrl() {
        return fragmentUrl;
    }

    public void setFragmentUrl(String fragmentUrl) {
        this.fragmentUrl = fragmentUrl;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_browser;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
//        EventBus.getDefault().register(this);

        titleView = fragmentRoot.findViewById(R.id.common_browser_fragment_title);
        titleView.setRightButtonImg(R.drawable.view_refresh_black_icon);
        titleView.setRightClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                String url = mBrowserWebView.getUrl();
                if (TextUtils.isEmpty(url)) {
                    initWebview();
                } else {
                    mBrowserWebView.reload();
                }
            }
        });

        contentView = fragmentRoot.findViewById(R.id.common_browser_fragment_content);
        mBrowserWebView = fragmentRoot.findViewById(R.id.common_browser_fragment_xWebView);
        initWebview();

        resetToolbarAfterXWebViewInvokeInitConfig();
    }

    private void initWebview() {
        if (mBrowserWebView != null && TextUtils.isEmpty(mBrowserWebView.getUrl())) {
            mBrowserConfig = createWebViewConfig();
            mBrowserWebView.initConfig(this, mBrowserConfig, createUiHandler());
            mBrowserWebView.loadUrl();
        }
    }

    private BrowserConfig createWebViewConfig() {
        String otherUrl = fragmentUrl;
        return new BrowserConfig.Builder()
                .setUrl(otherUrl)
                .setProxyCreater(new BrowserConfig.ProxyCreater() {
                    @Override
                    public INativeJavascriptInterfaceObj createNativeMethod() {
                        return new BrowserNativeJavascriptInterfaceObjectImpl(mBrowserWebView);
                    }

                    @Override
                    public IBrowserWindowWebView createWindowWebView() {
                        return new BrowserWindowWebView(BrowserFragment.this, mBrowserConfig);
                    }
                })
                .build();
    }

    @NonNull
    private IBrowserWebView.BrowserUIHandler createUiHandler() {
        return new IBrowserWebView.BrowserUIHandler() {
            @Override
            public void onPageStarted() {
                titleView.hideCloseView();
            }

            @Override
            public void onPageFinished(IBrowserWebView view, String url) {
                titleView.hideCloseView();
                if (view.canGoBack()) {
                    titleView.getLeftImage().setVisibility(View.VISIBLE);
                } else {
                    titleView.getLeftImage().setVisibility(View.GONE);
                }
            }
        };
    }

    /**
     * tip: 一定要在xWebView.initConfig()之后调用，作用：覆盖掉XWebView默认设置
     */
    private void resetToolbarAfterXWebViewInvokeInitConfig() {
        titleView.getLeftImage().setVisibility(View.GONE);
        titleView.setLeftClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                mBrowserWebView.goBack();
            }
        });
    }

    @Override
    public void setupDatas() {
        mBrowserWebView.loadUrl();
    }


    @Override
    public void onResume() {
        super.onResume();
        initWebview();
        if (mBrowserWebView != null) {
            mBrowserWebView.onShow();
        }
    }

    @Override
    public void onPause() {
        if (mBrowserWebView != null) {
            mBrowserWebView.onHide();
        }
        super.onPause();
    }

    @Override
    public void onDestroy() {
//        EventBus.getDefault().unregister(this);
        mBrowserWebView.destroy();
        super.onDestroy();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
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


//    public void onEventMainThread(final RefreshDisplayEventModel event) {
//        if (event.getType() == BaseDisplayEventModel.EVENT_LOGOUT) {
//            mBrowserWebView.loadUrl();
//        }
//    }

}


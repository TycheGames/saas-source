package com.deepfinch.kyclib;

import android.annotation.SuppressLint;
import android.content.IntentFilter;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.LinearInterpolator;
import android.view.animation.RotateAnimation;
import android.webkit.JavascriptInterface;
import android.webkit.WebChromeClient;
import android.webkit.WebSettings;
import android.webkit.WebView;
import android.webkit.WebViewClient;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.ScrollView;

import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.listener.DFFragmentArgumentListener;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.utils.DFKYCNetworkUtils;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.DFWebView;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFOTPWebFragment extends DFKYCBaseFragment implements DFFragmentArgumentListener, SmsReceiver.SMSCallback {
    private static final String TAG = "DFOTPInputFragment";

    private DFWebView mWvOtp;
    private ScrollView mSvRooter;
    private RelativeLayout mRlytLoading;

    private DFMessageFragment mMessageFragment;
    private WebSettings mWebViewSettings;
    private SmsReceiver mSmsReceiver;
    private Handler mMainHandler;
    private boolean mShowEnd;
    private int mProgress;

    public static DFOTPWebFragment getInstance() {
        DFOTPWebFragment fragment = new DFOTPWebFragment();
        return fragment;
    }

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_otp_web;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initTitle(view);
        initMessageFragment();
        initView();
        initSMSReceive();
        initWebView();
        initLoadingView();
        initData();
    }

    private void initTitle(View view) {
        View leftView = view.findViewById(R.id.id_iv_left);
        leftView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mKYCProcessListener != null) {
                    mKYCProcessListener.onBack();
                }
            }
        });
    }

    private void initMessageFragment() {
        DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
        messageFragmentModel.setHintTitle(getString(R.string.kyc_opt_error));
        messageFragmentModel.setHintContent(getString(R.string.kyc_opt_error_content));
        mMessageFragment = DFMessageFragment.getInstance(messageFragmentModel);
    }

    @SuppressLint("ClickableViewAccessibility")
    private void initView() {
        DFKYCUtils.logI(TAG, "initView", "start");
        mMainHandler = new Handler();
        mSvRooter = findViewById(R.id.id_sv_rooter);
        mWvOtp = findViewById(R.id.id_wv_otp);
        mRlytLoading = findViewById(R.id.id_rlyt_loading);
    }

    private void initWebView() {
        mWebViewSettings = mWvOtp.getSettings();
        mWebViewSettings.setJavaScriptEnabled(true);
        mWebViewSettings.setDisplayZoomControls(false);
        mWebViewSettings.setBuiltInZoomControls(false);// 2
        mWebViewSettings.setSupportZoom(false);
        mWebViewSettings.setUseWideViewPort(true);
        mWebViewSettings.setLoadWithOverviewMode(true);
        mWebViewSettings.setCacheMode(WebSettings.LOAD_NO_CACHE);// webview页面不使用缓存

        mWvOtp.setInitialScale(15);
        mWvOtp.setScrollBarStyle(View.SCROLLBARS_INSIDE_OVERLAY);
        mWvOtp.setWebChromeClient(new MyWebChromeClient());
        mWvOtp.setWebViewClient(new MyWebViewClientEx());

        mWvOtp.addJavascriptInterface(this, "android");

        mWvOtp.setOnTouchListener(new View.OnTouchListener() {

            @Override
            public boolean onTouch(View v, MotionEvent ev) {

                ((WebView) v).requestDisallowInterceptTouchEvent(true);

                return false;
            }
        });

    }

    private void initLoadingView() {
        mRlytLoading.setClickable(true);
        ImageView progressView = findViewById(R.id.id_iv_progress_spinner);
        RotateAnimation rotateAnimation = new RotateAnimation(0f, 359, Animation.RELATIVE_TO_SELF, 0.5f, Animation.RELATIVE_TO_SELF, 0.5f);
        rotateAnimation.setDuration(700);
        rotateAnimation.setInterpolator(new LinearInterpolator());
        rotateAnimation.setRepeatCount(Animation.INFINITE);
        progressView.startAnimation(rotateAnimation);

        mMainHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                mShowEnd = true;
                hideLoadingView();
            }
        }, 4000);

        mMainHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                loadTimeout();
            }
        }, DFKYCNetworkUtils.DF_TIME_OUT_LOAD_OTP * 1000);
    }

    private void loadTimeout() {
        if (mShowEnd && mProgress >= 100) {

        } else {
            DFKYCUtils.logI(TAG, "load界面超时");
            if (mKYCProcessListener != null) {
                mKYCProcessListener.onBack();
            }
        }
    }

    @JavascriptInterface
    public void jsCallAndroid(String args) {
        DFKYCUtils.logI(TAG, "jsCallAndroid", args);
        if (!TextUtils.isEmpty(args)) {
            String[] split = args.split("@");
            String otp = null;
            String password = null;
            if (split.length >= 2) {
                otp = split[0];
                password = split[1];
                if (otp.length() == 6 && password.length() == 4) {
                    verifyOTPNumber(otp, password);
                }
            }
        }
    }

    @JavascriptInterface
    public void clickInputOTP() {
        mMainHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                mSvRooter.fullScroll(ScrollView.FOCUS_DOWN);
            }
        }, 300);
    }

    private void initSMSReceive() {
        IntentFilter filter = new IntentFilter();
        filter.addAction("android.provider.Telephony.SMS_RECEIVED");
        mSmsReceiver = new SmsReceiver();
        mSmsReceiver.setSMSCallback(this);
        getActivity().registerReceiver(mSmsReceiver, filter);
    }

    @Override
    public void onResume() {
        super.onResume();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        getActivity().unregisterReceiver(mSmsReceiver);
    }

    private void initData() {
        DFProcessStepModel processDataModel = getProcessDataModel();
        if (processDataModel != null) {
            String otpHtml = processDataModel.getOtpHtml();
            // 替换download按钮为android可调用的js
            String replaceOtpHtml = otpHtml.replace("<button type=\"button\" onclick=\"\" value=\"Submit\" class=\"smt-totp btn btn-primary ripple r-width-300 m-5 button-icon\" ><i class=\"material-icons fs-15\">file_download</i>Download</button>",
                    " <button type=\"button\" onclick=\"window.android.jsCallAndroid(jQuery('#spaced-inputenter-otp').val() + '@'+ jQuery('#input_password').val())\" class=\"btn btn-primary ripple r-width-300 m-5 button-icon\" ><i class=\"material-icons fs-15\">file_download</i>Download</button>");
            // 将加密结果的加密密码默认填入
            replaceOtpHtml = replaceOtpHtml.replace("<label class=\"control-label required\" for=\"enter-otp\" data-toggle=\"tooltip\" data-original-title=\"Create Code\">",
                    "<label class=\"control-label required\" id=\"input_password_hint\" for=\"enter-otp\" data-toggle=\"tooltip\" data-original-title=\"Create Code\">");
            replaceOtpHtml = replaceOtpHtml.replaceFirst("<input type=\"text\" class=\"font-style-big spaced-input form-control\"",
                    "<input type=\"text\" id=\"input_password\" class=\"font-style-big spaced-input form-control\"");
            replaceOtpHtml = replaceOtpHtml.replaceFirst("<script type=\"text/javascript\">",
                    "<script type=\"text/javascript\">\n" +
                            "    \n" +
                            "    function javacalljs(){\n" +
                            "         jQuery('html, body').animate({\n" +
                            "            scrollTop: jQuery(\"#input_password_hint\").offset().top\n" +
                            "        }, 1000);\n" +
                            "\n" +
                            "    }");
            replaceOtpHtml = replaceOtpHtml.replaceFirst("<input name=\"totp\" type=\"text\"",
                    "<input name=\"totp\" type=\"text\" onclick=\"window.android.clickInputOTP()\"");
            String baseUrl = "https://resident.uidai.gov.in";
            mWvOtp.loadDataWithBaseURL(baseUrl, replaceOtpHtml,
                    "text/html", "utf-8", null);
        }
    }

    private void verifyOTPNumber(String otpNumber, String password) {
        if (mKYCProcessListener != null) {
            DFProcessStepModel processDataModel = getProcessDataModel();
            processDataModel.setOtp(otpNumber);
            processDataModel.setPassword(password);
            mKYCProcessListener.callbackResult(processDataModel);
        }
    }

    private void showErrorView() {
        hideLoadingDialog();
        mMessageFragment.showMessage(getFragmentManager());
    }

    @Override
    public void onReturnValidCode(String validCode) {
        DFKYCUtils.logI(TAG, "onReturnValidCode", "validCode", validCode);
    }

    private final class MyWebChromeClient extends WebChromeClient {

        @Override
        public void onProgressChanged(WebView view, final int newProgress) {
            super.onProgressChanged(view, newProgress);
            DFKYCUtils.logI(TAG, "onProgressChanged", "newProgress", newProgress);
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    updateProgress(newProgress);
                }
            });
        }
    }

    private void updateProgress(int newProgress) {
        mProgress = newProgress;
        if (newProgress >= 100) {
            scrollInputOTPView();
        }
        hideLoadingView();
    }

    private void scrollInputOTPView() {
        mWvOtp.loadUrl("javascript:javacalljs()");
    }

    private void hideLoadingView() {
        if (mShowEnd && mProgress >= 100) {
            DFKYCUtils.refreshVisibilit(mRlytLoading, false);
        }
    }

    class MyWebViewClientEx extends WebViewClient {
        @Override
        public boolean shouldOverrideUrlLoading(WebView view, String url) {
            view.loadUrl(url);
            return super.shouldOverrideUrlLoading(view, url);
        }

        @Override
        public void onPageFinished(WebView view, String url) {
            DFKYCUtils.logI(TAG, "MyWebViewClientEx", "onPageFinished", url);
            super.onPageFinished(view, url);
        }
    }
}
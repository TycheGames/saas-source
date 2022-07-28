package com.deepfinch.kyclib;

import android.annotation.SuppressLint;
import android.graphics.Bitmap;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.view.MotionEvent;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.ScrollView;

import com.deepfinch.kyc.DFKYCSDK;
import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.listener.DFFragmentArgumentListener;
import com.deepfinch.kyclib.presenter.DFKYCSDKPresenter;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFCaptchaFragment extends DFKYCBaseFragment implements DFFragmentArgumentListener {
    private static final String TAG = "DFCaptchaFragment";

    private ScrollView mSvRooter;
    private EditText mEtCaptchaNumber;

    private DFMessageFragment mMessageFragment;

    private Handler mMainHandler;

    private DFKYCSDKPresenter mKYCSDKPresenter;

    public static DFCaptchaFragment getInstance() {
        DFCaptchaFragment fragment = new DFCaptchaFragment();
        return fragment;
    }

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_captcha;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        createSDKPresenter();
        initSDKPresenter();
        initTitle(view);
        initMessageFragment();
        initView();
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
        mEtCaptchaNumber = findViewById(R.id.id_et_captcha);
        Button nextBtn = findViewById(R.id.id_btn_next);

        nextBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                onClickNext();
            }
        });

        mEtCaptchaNumber.setOnTouchListener(new View.OnTouchListener() {
            @Override
            public boolean onTouch(View view, MotionEvent event) {
//                DFKYCUtils.logI(TAG, "mEtAadhaarNumber", "onTouch");
                mMainHandler.postDelayed(new Runnable() {
                    @Override
                    public void run() {
                        mSvRooter.fullScroll(ScrollView.FOCUS_DOWN);
                    }
                }, 300);
                return false;
            }
        });
    }

    private void onClickNext() {
        final String captchaNumber = DFKYCUtils.getText(mEtCaptchaNumber);
        if (!TextUtils.isEmpty(captchaNumber)) {
            verifyAadhaarNumber(captchaNumber);
        } else {
            showErrorView();
        }
    }

    private void verifyAadhaarNumber(String captchaNumber) {
        if (mKYCProcessListener != null) {
            DFProcessStepModel processDataModel = getProcessDataModel();
            processDataModel.setCaptcha(captchaNumber);
            mKYCProcessListener.callbackResult(processDataModel);
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        initData();
    }

    private void initData() {
        DFKYCUtils.refreshText(mEtCaptchaNumber, "");
    }

    private void showErrorView() {
        hideLoadingDialog();
        mMessageFragment.showMessage(getFragmentManager());
    }

    private void createSDKPresenter() {
        DFKYCUtils.logI(TAG, "createSDKPresenter");
        showLoadingDialog();
        mKYCSDKPresenter = DFKYCSDKPresenter.getInstance(getActivity());
    }

    private void initSDKPresenter() {
        DFKYCUtils.logI(TAG, "initSDKPresenter");
        releaseKYCSDKPresenter();
        mKYCSDKPresenter.addKYCSDKView(this);
        mKYCSDKPresenter.init();
    }

    private void releaseKYCSDKPresenter() {
        DFKYCUtils.logI(TAG, "releaseKYCSDKPresenter");
        if (mKYCSDKPresenter != null) {
            mKYCSDKPresenter.removeSDKView(this);
            mKYCSDKPresenter.releaseResource();
        }
    }

    @Override
    public void createFinish(final int result) {
        DFKYCUtils.logI(TAG, "createFinish", "result", result);
        hideLoadingDialog();
        if (result == DFKYCSDK.OK){
            mMainHandler.removeCallbacksAndMessages(null);
            runOnUiThread(new Runnable() {
                @Override
                public void run() {
                    refreshCaptchaImage();
                }
            });
        } else {
            loadCaptchaFail();
        }
    }

    private void refreshCaptchaImage(){
        if (mKYCSDKPresenter != null) {
            ImageView ivCaptcha = findViewById(R.id.id_iv_captcha);
            Bitmap captchaImage = mKYCSDKPresenter.getCaptchaImage();
            ivCaptcha.setImageBitmap(captchaImage);
        }
    }

    private void loadCaptchaFail(){
        hideLoadingDialog();
        DFKYCUtils.logI(TAG, "createFinish", "loadCaptchaFail");
        releaseKYCSDKPresenter();
        if (mKYCProcessListener != null) {
            mKYCProcessListener.onBack();
        }
    }
}

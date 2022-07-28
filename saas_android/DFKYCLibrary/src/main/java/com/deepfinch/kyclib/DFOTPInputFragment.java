package com.deepfinch.kyclib;

import android.annotation.SuppressLint;
import android.content.IntentFilter;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.view.MotionEvent;
import android.view.View;
import android.widget.Button;
import android.widget.EditText;
import android.widget.ScrollView;
import android.widget.TextView;

import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.listener.DFFragmentArgumentListener;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFOTPInputFragment extends DFKYCBaseFragment implements DFFragmentArgumentListener, SmsReceiver.SMSCallback {
    private static final String TAG = "DFOTPInputFragment";
    private ScrollView mSvRooter;
    private EditText mEtOTPNumber;

    private DFMessageFragment mMessageFragment;

    private Handler mMainHandler;
    private SmsReceiver mSmsReceiver;

    public static DFOTPInputFragment getInstance() {
        DFOTPInputFragment fragment = new DFOTPInputFragment();
        return fragment;
    }

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_otp;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initTitle(view);
        initMessageFragment();
        initView();
        initSMSReceive();
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
        mEtOTPNumber = findViewById(R.id.id_et_otp_number);
        Button nextBtn = findViewById(R.id.id_btn_next);
        nextBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                onClickNext();
            }
        });

        mEtOTPNumber.setOnTouchListener(new View.OnTouchListener() {
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
        initData();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        getActivity().unregisterReceiver(mSmsReceiver);
    }

    private void initData() {
        DFProcessStepModel processDataModel = getProcessDataModel();
        if (processDataModel != null) {
            String aadhaarNumber = processDataModel.getAadhaarNumber();
            TextView tvHintTitle = findViewById(R.id.id_tv_hint_title);
            DFKYCUtils.refreshText(tvHintTitle, aadhaarNumber);
        }
        DFKYCUtils.refreshText(mEtOTPNumber, "");

    }

    private void onClickNext() {
        final String otpNumber = DFKYCUtils.getText(mEtOTPNumber);
        if (!TextUtils.isEmpty(otpNumber)) {
            verifyOTPNumber(otpNumber);
        } else {
            showErrorView();
        }
    }

    private void verifyOTPNumber(String otpNumber) {
        if (mKYCProcessListener != null) {
            DFProcessStepModel processDataModel = getProcessDataModel();
            processDataModel.setOtp(otpNumber);
            processDataModel.setPassword("1903");
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
        if (mEtOTPNumber != null){
            mEtOTPNumber.setText(validCode);
        }
    }
}

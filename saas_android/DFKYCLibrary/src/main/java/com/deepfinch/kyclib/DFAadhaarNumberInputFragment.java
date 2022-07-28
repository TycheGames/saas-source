package com.deepfinch.kyclib;

import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.view.MotionEvent;
import android.view.View;
import android.widget.Button;
import android.widget.ScrollView;

import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.view.BankCardNumEditText;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFAadhaarNumberInputFragment extends DFKYCBaseFragment {
    private static final String TAG = "DFAadhaarNumberInputFragment";

    private ScrollView mSvRooter;
    private BankCardNumEditText mEtAadhaarNumber;

    private DFMessageFragment mMessageFragment;
    private Handler mMainHandler;


    public static DFAadhaarNumberInputFragment getInstance() {
        DFAadhaarNumberInputFragment fragment = new DFAadhaarNumberInputFragment();
        return fragment;
    }

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_aadhaar_number;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initTitle();
        initMessageFragment();
        initView();
    }

    private void initTitle() {
        View leftView = findViewById(R.id.id_iv_left);
        leftView.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mKYCProcessListener != null) {
                    mKYCProcessListener.onBack();
                }
            }
        });
    }

    private void initView() {
        mMainHandler = new Handler();
        mSvRooter = findViewById(R.id.id_sv_rooter);
        mEtAadhaarNumber = findViewById(R.id.id_et_aadhaar_number);
        Button nextBtn = findViewById(R.id.id_btn_next);
        nextBtn.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                onClickNext();
            }
        });
        mEtAadhaarNumber.setOnTouchListener(new View.OnTouchListener() {
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

    private void initMessageFragment() {
        DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
        messageFragmentModel.setHintTitle(getString(R.string.kyc_aadhaar_number_error));
        messageFragmentModel.setHintContent("");
        mMessageFragment = DFMessageFragment.getInstance(messageFragmentModel);
    }

    private void onClickNext() {
        final String aadhaarNumber = getBankNumberText(mEtAadhaarNumber);
        int length = aadhaarNumber.length();
        if (!TextUtils.isEmpty(aadhaarNumber) && (length == 12 || length == 16)) {
            verifyAadhaarNumber(aadhaarNumber);
        } else {
            showErrorView();
        }
    }

    private void verifyAadhaarNumber(String aadhaarNumber) {
        if (callback != null) {
            callback.onAadhaarNumber(aadhaarNumber);
        }
        if (mKYCProcessListener != null) {
            DFProcessStepModel processDataModel = getProcessDataModel();
            processDataModel.setAadhaarNumber(aadhaarNumber);
            mKYCProcessListener.callbackResult(processDataModel);
        }
    }

    private String getBankNumberText(BankCardNumEditText editText) {
        return editText.getTextWithoutSpace();
    }

    private void showErrorView() {
        mMessageFragment.showMessage(getFragmentManager());
    }


    private Callback callback;

    public void setCallback(Callback callback) {
        this.callback = callback;
    }

    public interface Callback {
        /**
         * 输入了Aadhaar卡的卡号
         */
        void onAadhaarNumber(String aadhaarNumber);
    }

}

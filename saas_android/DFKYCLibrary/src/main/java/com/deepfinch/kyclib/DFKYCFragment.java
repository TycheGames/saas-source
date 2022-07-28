package com.deepfinch.kyclib;

import android.app.Fragment;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.View;

import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.listener.DFKYCCallback;
import com.deepfinch.kyclib.model.DFErrorHint;
import com.deepfinch.kyclib.model.DFErrorModel;
import com.deepfinch.kyclib.model.DFKYCModel;
import com.deepfinch.kyclib.presenter.DFKYCProcessPresenter;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCFragment extends DFKYCBaseFragment implements DFKYCProcessPresenter.DFKYCProcessViewCallback {
    private static final String TAG = "DFKYCFragment";
    private DFKYCProcessPresenter mKYCProcessPresenter;

    private DFKYCCallback mKYCCallback;

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_main;
    }

    public static DFKYCFragment getInstance() {
        DFKYCFragment kYCFragment = new DFKYCFragment();
        return kYCFragment;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initPresenter();
        startKycDetect();
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (mKYCProcessPresenter != null) {
            mKYCProcessPresenter.destroy();
        }
    }

    protected void initPresenter() {
        mKYCProcessPresenter = new DFKYCProcessPresenter(this);
    }

    private void startKycDetect() {
        mKYCProcessPresenter.startGetFace();
    }

    @Override
    public void showFragment(Fragment fragment) {
        replaceFragment(R.id.id_flyt_kyc_contain, fragment);
    }

    @Override
    public void showErrorView(DFMessageFragmentModel messageFragmentModel) {
        DFMessageFragment messageFragment = DFMessageFragment.getInstance(messageFragmentModel);
        messageFragment.showMessage(getFragmentManager());
    }


    private String getErrorString(int errorCode) {
        String errorStr = getString(R.string.kyc_error_unknown);
        DFErrorHint errorHint = DFErrorHint.getErrorHintByCode(errorCode);
        if (errorHint != null) {
            errorStr = getString(errorHint.getErrorStrResId());
        }
        return errorStr;
    }

    public void setKYCCallback(DFKYCCallback callback) {
        this.mKYCCallback = callback;
    }

    @Override
    public void finishActivity() {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                if (mKYCCallback != null) {
                    mKYCCallback.onBack();
                }
            }
        });
    }

    @Override
    public void returnKYCModel(DFKYCModel kycModel, String aadhaarNumber) {
        if (mKYCCallback != null) {
            mKYCCallback.callbackResult(kycModel, aadhaarNumber);
        }
    }

    @Override
    public void returnError(int errorCode) {
        onError(errorCode);
    }

    @Override
    public void startLoading() {
        showLoadingDialog();
    }

    @Override
    public void endLoading() {
        hideLoadingDialog();
    }

    private void onError(final int errorCode) {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                boolean canCallback = DFKYCUtils.canCallback(errorCode);
                if (mKYCCallback != null && canCallback) {
                    DFErrorModel errorModel = new DFErrorModel();
                    errorModel.setErrorCode(errorCode);
                    errorModel.setErrorStr(getErrorString(errorCode));
                    mKYCCallback.onError(errorModel);
                }
            }
        });
    }
}

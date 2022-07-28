package com.deepfinch.kyclib.presenter;

import android.app.Activity;
import android.app.Fragment;
import android.os.Handler;
import android.support.annotation.NonNull;
import android.text.TextUtils;

import com.deepfinch.kyc.DFKYCSDK;
import com.deepfinch.kyc.jni.DFSendUIDResult;
import com.deepfinch.kyc.jni.DFUIDResult;
import com.deepfinch.kyclib.DFAadhaarNumberInputFragment;
import com.deepfinch.kyclib.DFCaptchaFragment;
import com.deepfinch.kyclib.DFOTPWebFragment;
import com.deepfinch.kyclib.DFPermissionFragment;
import com.deepfinch.kyclib.R;
import com.deepfinch.kyclib.listener.DFKYCListener;
import com.deepfinch.kyclib.model.DFKYCModel;
import com.deepfinch.kyclib.presenter.model.DFProcessErrorCode;
import com.deepfinch.kyclib.presenter.model.DFProcessStep;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.utils.DFSDCardUtils;
import com.deepfinch.kyclib.utils.DFZipUtils;
import com.deepfinch.kyclib.utils.xml.DFAadhaarXmlUtils;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

import net.lingala.zip4j.exception.ZipException;

import java.io.File;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCProcessPresenter implements DFKYCSDKPresenter.DFKYCSDKView, DFKYCListener<DFProcessStepModel> {
    private static final String TAG = "DFKYCProcessPresenter";

    private DFKYCProcessViewCallback mKYCProcessViewCallback;
    private DFProcessStep mCurrentProcessModel;
    private DFProcessStepModel mProcessDataModel;
    private DFKYCSDKPresenter mKYCSDKPresenter;

    private String aadhaarNumber;// 当前输入的Aadhaar卡的卡号

    public DFKYCProcessPresenter(@NonNull DFKYCProcessViewCallback kycProcessViewCallback) {
        mKYCProcessViewCallback = kycProcessViewCallback;
        mProcessDataModel = new DFProcessStepModel();
        initProcess();
    }

    private void initProcess() {
        initSDKPresenter();

        DFProcessStep permissionProcessStep = new DFProcessStep();
        DFPermissionFragment permissionFragment = DFPermissionFragment.getInstance();
        permissionFragment.setKYCProcessListener(this);

        DFProcessStep aadhaarNumberProcessModel = new DFProcessStep();
        DFAadhaarNumberInputFragment aadhaarNumberInputFragment = DFAadhaarNumberInputFragment.getInstance();
        aadhaarNumberInputFragment.setKYCProcessListener(this);
        aadhaarNumberInputFragment.setCallback(new DFAadhaarNumberInputFragment.Callback() {
            @Override
            public void onAadhaarNumber(String aadhaarNumber) {
                DFKYCProcessPresenter.this.aadhaarNumber = aadhaarNumber;
            }
        });


        DFProcessStep captchaProcessModel = new DFProcessStep();
        DFCaptchaFragment captchaFragment = DFCaptchaFragment.getInstance();
        captchaFragment.setKYCProcessListener(this);

        DFProcessStep otpProcessModel = new DFProcessStep();
//        DFOTPInputFragment otpInputFragment = DFOTPInputFragment.getInstance();
        DFOTPWebFragment otpInputFragment = DFOTPWebFragment.getInstance();
        otpInputFragment.setKYCProcessListener(this);

        permissionProcessStep.setShowFragment(permissionFragment);
        permissionProcessStep.setArgumentListener(permissionFragment);
        permissionProcessStep.setNextProcessModel(aadhaarNumberProcessModel);
        permissionProcessStep.setKycDealNextPresenter(new DFKYCPermissionPresenter());

        aadhaarNumberProcessModel.setShowFragment(aadhaarNumberInputFragment);
        aadhaarNumberProcessModel.setArgumentListener(aadhaarNumberInputFragment);
        aadhaarNumberProcessModel.setNextProcessModel(otpProcessModel);
        aadhaarNumberProcessModel.setErrorProcessModel(captchaProcessModel);
        aadhaarNumberProcessModel.setKycDealNextPresenter(new DFKYCAadhaarPresenter());

        captchaProcessModel.setShowFragment(captchaFragment);
        captchaProcessModel.setArgumentListener(captchaFragment);
        captchaProcessModel.setNextProcessModel(otpProcessModel);
        captchaProcessModel.setPreviousProcessModel(aadhaarNumberProcessModel);
        captchaProcessModel.setKycDealNextPresenter(new DFKYCAadhaarPresenter());

        otpProcessModel.setShowFragment(otpInputFragment);
        otpProcessModel.setArgumentListener(otpInputFragment);
        otpProcessModel.setPreviousProcessModel(aadhaarNumberProcessModel);
        otpProcessModel.setKycDealNextPresenter(new DFKYCOTPPresenter());

        mCurrentProcessModel = permissionProcessStep;
    }

    private void initSDKPresenter() {
        mKYCSDKPresenter = DFKYCSDKPresenter.getInstance(mKYCProcessViewCallback.getActivity());
        mKYCSDKPresenter.addKYCSDKView(this);
    }

    public void startGetFace() {
        mCurrentProcessModel.setFragmentArguments(mProcessDataModel);
        Fragment showFragment = mCurrentProcessModel.getShowFragment();
        mKYCProcessViewCallback.showFragment(showFragment);
    }

    public void startPreviousProcess() {
        DFProcessStep previousProcessModel = mCurrentProcessModel.getPreviousProcessModel();
        if (previousProcessModel != null) {
            mCurrentProcessModel = previousProcessModel;
            startGetFace();
        } else {
            if (mKYCProcessViewCallback != null) {
                mKYCProcessViewCallback.finishActivity();
            }
        }
    }

    public void startNextProcess(boolean success) {
        DFProcessStep nextProcessModel = mCurrentProcessModel.getNextProcessModel();
        if (!success) {
            nextProcessModel = mCurrentProcessModel.getErrorProcessModel();
        }
        if (nextProcessModel != null) {
            mCurrentProcessModel = nextProcessModel;
            startGetFace();
        }
    }

    @Override
    public void callbackResult(DFProcessStepModel result) {
        startLoading();
        mProcessDataModel = result;
        DFKYCDealNextPresenter kycDealNextPresenter = mCurrentProcessModel.getKycDealNextPresenter();
        if (kycDealNextPresenter != null) {
            kycDealNextPresenter.dealProcess(mKYCSDKPresenter, mProcessDataModel);
        }
    }

    @Override
    public void onBack() {
        startPreviousProcess();
    }

    @Override
    public void returnRequestPermission(boolean success) {
        endLoading();
        if (success) {
            startNextProcess(true);
        } else {
            onBack();
        }
    }

    @Override
    public void returnUIDResult(final DFSendUIDResult sendUIDResult) {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                dealUIDResult(sendUIDResult);
            }
        });
    }

    private void dealUIDResult(DFSendUIDResult sendUIDResult) {
        if (sendUIDResult != null) {
            int result = sendUIDResult.getResult();
            DFKYCUtils.logI(TAG, "returnUIDResult", "result", result);
            String requestResult = sendUIDResult.getRequestResult();
            if (mProcessDataModel != null) {
                mProcessDataModel.setOtpHtml(requestResult);
            }
            if (result == 0) {
                startNextProcess(true);
//                testCaptcha();
            } else if (result == DFKYCSDK.ERROR_CODE_INVALID_CAPTCHA) {
                startNextProcess(false);
//                showErrorView(result);
//            testCaptcha();
            } else {
                showErrorView(result);
            }
        } else {
            showErrorView(-1);
        }
        endLoading();
    }

    private void testCaptcha() {
        String captcha = mProcessDataModel.getCaptcha();
        if (TextUtils.isEmpty(captcha)) {
            startNextProcess(false);
        } else {
            startNextProcess(true);
        }
    }

    @Override
    public void returnGetUIDResult(final DFUIDResult uidResult) {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
//                returnTestModel();
                if (uidResult != null) {
                    dealGetUIDResult(uidResult);
                } else {
                    showErrorView(DFProcessErrorCode.ERROR_CODE_NETWORK_CONNECT_FAIL.getErrorCode());
                }
                endLoading();
            }
        });
    }

    private void dealGetUIDResult(DFUIDResult uidResult) {
        int result = uidResult.getResult();
        byte[] bytes = uidResult.getZipResult();
        if (result == DFKYCSDK.OK) {
            String userInfoDir = DFSDCardUtils.getUserInfoDir(mKYCProcessViewCallback.getActivity());
            String zipPath = DFSDCardUtils.saveFile(bytes, userInfoDir, "result.zip");
            File[] unZipFileList = null;
            try {
                String password = mProcessDataModel.getPassword();
                DFKYCUtils.logI(TAG, "dealGetUIDResult", "password", password);
                unZipFileList = DFZipUtils.unzip(zipPath, userInfoDir, password);
            } catch (ZipException e) {
                e.printStackTrace();
            }
            DFKYCModel kycModel = null;
            if (unZipFileList != null && unZipFileList.length >= 1) {
                kycModel = new DFKYCModel();
                File unZipFile = unZipFileList[0];
                DFSDCardUtils.PATH_KEY_FILE = unZipFile.getAbsolutePath();
                DFAadhaarXmlUtils xmlUtils = new DFAadhaarXmlUtils();
                kycModel = xmlUtils.parserXml(unZipFile);
                DFKYCUtils.logI(TAG, "parserXml");

                if (mKYCProcessViewCallback != null) {
                    mKYCProcessViewCallback.returnKYCModel(kycModel, aadhaarNumber);
                }
            } else {
                showOTPErrorView();
            }
        } else {
            showErrorView(result);
        }
    }

    private void returnTestModel() {
        Handler handler = new Handler();
        handler.postDelayed(new Runnable() {
            @Override
            public void run() {
                DFKYCModel kycModel = DFAadhaarXmlUtils.createTestModel(mKYCProcessViewCallback.getActivity());
                if (mKYCProcessViewCallback != null) {
                    mKYCProcessViewCallback.returnKYCModel(kycModel, aadhaarNumber);
                }
                endLoading();
            }
        }, 200);
    }

    @Override
    public void createFinish(final int result) {
        runOnUiThread(new Runnable() {
            @Override
            public void run() {
                showErrorView(result);
            }
        });
        onError(result);
        DFKYCUtils.logI(TAG, "createFinish", "result", result);
        if (result != 0) {
            getCookieError();
        }
    }

    private void onError(int errorCode) {
        if (mKYCProcessViewCallback != null) {
            mKYCProcessViewCallback.returnError(errorCode);
        }
    }

    private void showErrorView(int errorCode) {
        if (mKYCProcessViewCallback != null) {
            DFProcessErrorCode processError = DFProcessErrorCode.getProcessError(errorCode);
            int errorTitleId = R.string.kyc_aadhaar_number_error;
            int errorContentId = R.string.kyc_aadhaar_number_error_content;
            if (processError != null) {
                errorTitleId = processError.getErrorTitle();
                errorContentId = processError.getErrorContent();
                DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
                messageFragmentModel.setHintTitle(getString(errorTitleId));
                messageFragmentModel.setHintContent(getString(errorContentId));
                mKYCProcessViewCallback.showErrorView(messageFragmentModel);
            }
        }
    }

    private void showUIDErrorView() {
        if (mKYCProcessViewCallback != null) {
            DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
            messageFragmentModel.setHintTitle(getString(R.string.kyc_aadhaar_number_error));
            messageFragmentModel.setHintContent(getString(R.string.kyc_aadhaar_number_error_content));
            mKYCProcessViewCallback.showErrorView(messageFragmentModel);
        }
    }

    private void showCaptchaErrorView() {
        if (mKYCProcessViewCallback != null) {
            DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
            messageFragmentModel.setHintTitle(getString(R.string.kyc_validation_error));
            messageFragmentModel.setHintContent(getString(R.string.kyc_validation_error_content));
            mKYCProcessViewCallback.showErrorView(messageFragmentModel);
        }
    }

    private void showOTPErrorView() {
        if (mKYCProcessViewCallback != null) {
            DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
            messageFragmentModel.setHintTitle(getString(R.string.kyc_opt_error));
            messageFragmentModel.setHintContent(getString(R.string.kyc_opt_error_content));
            mKYCProcessViewCallback.showErrorView(messageFragmentModel);
        }
    }

    private void getCookieError() {
        releaseKYCSDKPresenter();
    }

    private void releaseKYCSDKPresenter() {
        if (mKYCSDKPresenter != null) {
            mKYCSDKPresenter.releaseResource();
        }
    }

    private void destroyKYCSDKPresenter() {
        if (mKYCSDKPresenter != null) {
            mKYCSDKPresenter.destroy();
        }
    }

    private void runOnUiThread(Runnable runnable) {
        Activity activity = mKYCProcessViewCallback.getActivity();
        if (activity != null) {
            activity.runOnUiThread(runnable);
        }
    }

    private void startLoading() {
        if (mKYCProcessViewCallback != null) {
            mKYCProcessViewCallback.startLoading();
        }
    }

    private void endLoading() {
        if (mKYCProcessViewCallback != null) {
            mKYCProcessViewCallback.endLoading();
        }
    }

    private String getString(int stringRedId) {
        String result = null;
        if (mKYCProcessViewCallback != null && stringRedId != -1) {
            Activity activity = mKYCProcessViewCallback.getActivity();
            result = activity.getString(stringRedId);
        }
        return result;
    }

    public void destroy() {
        destroyKYCSDKPresenter();
    }

    public interface DFKYCProcessViewCallback {
        void showFragment(Fragment fragment);

        Activity getActivity();

        void showErrorView(DFMessageFragmentModel messageFragmentModel);

        void finishActivity();

        void returnKYCModel(DFKYCModel kycModel, String aadhaarNumber);

        void returnError(int errorCode);

        void startLoading();

        void endLoading();
    }
}

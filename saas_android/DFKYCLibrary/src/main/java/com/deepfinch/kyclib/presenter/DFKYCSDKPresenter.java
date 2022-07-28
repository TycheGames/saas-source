package com.deepfinch.kyclib.presenter;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;

import com.deepfinch.kyc.DFKYCSDK;
import com.deepfinch.kyc.jni.DFSendUIDResult;
import com.deepfinch.kyc.jni.DFUIDResult;
import com.deepfinch.kyclib.utils.DFKYCUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCSDKPresenter implements DFKYCSDK.SDKCallback {
    private static final String TAG = "DFKYCSDKPresenter";
    private static DFKYCSDKPresenter mInstance;

    private DFKYCSDK mKYCSDK = null;

    private List<DFKYCSDKView> mKYCSDKViewList;
    private Context mContext;

    private byte[] mCaptchaImage;

    private boolean mInitFinish;

    public DFKYCSDKPresenter(Context context) {
        DFKYCUtils.logI(TAG, "DFKYCSDKPresenter");
        mContext = context;
        mKYCSDKViewList = new ArrayList<>();
        init();
    }

    public static DFKYCSDKPresenter getInstance(Context context) {
        if (mInstance == null) {
            synchronized (DFKYCSDKPresenter.class) {
                if (mInstance == null) {
                    mInstance = new DFKYCSDKPresenter(context);
                }
            }
        }
        return mInstance;
    }

    public boolean init() {
        DFKYCUtils.logI(TAG, "init", (mKYCSDK == null));
        if (mKYCSDK == null) {
            mKYCSDK = new DFKYCSDK(mContext, DFKYCUtils.API_ID, DFKYCUtils.API_SECRET, this);
        }
        return true;
    }

    public void requestPermission(boolean success) {
        callbackRequestPermission(success);
    }

    private void callbackRequestPermission(boolean success){
        if (mKYCSDKViewList != null){
            for (DFKYCSDKView kycSDKView : mKYCSDKViewList){
                kycSDKView.returnRequestPermission(success);
            }
        }
    }

    public void sendUID(String aadhaarNumber) {
        DFKYCUtils.logI(TAG, "sendUID", "aadhaarNumber", aadhaarNumber);
        init();
        mKYCSDK.sendUID(aadhaarNumber, null);
    }

    public void sendUID(String aadhaarNumber, String captcha) {
        DFKYCUtils.logI(TAG, "sendUID", "aadhaarNumber", aadhaarNumber, "captcha", captcha);
        init();
        mKYCSDK.sendUID(aadhaarNumber, captcha);
//        Handler handler = new Handler();
//        handler.postDelayed(new Runnable() {
//            @Override
//            public void run() {
//                if (mKYCSDKView != null) {
//                    DFSendUIDResult sendUIDResult = new DFSendUIDResult();
//                    sendUIDResult.setRequestResult("");
//                    mKYCSDKView.returnUIDResult(sendUIDResult);
//                }
//            }
//        }, 3000);
    }

    public void getUIDResult(String otpNumber, String password) {
        DFKYCUtils.logI(TAG, "getUIDResult", "otpNumber", otpNumber);
        init();
        mKYCSDK.getUIDResult(otpNumber, password);
    }

    public void releaseResource() {
        DFKYCUtils.logI(TAG, "releaseResource", "mKYCSDK", (mKYCSDK == null));
        if (mKYCSDK != null) {
            mKYCSDK.destroy();
            mKYCSDK = null;
        }
    }

    public void destroy() {
        releaseResource();
        mInstance = null;
    }

    public boolean isInitFinish() {
        return mInitFinish;
    }

    @Override
    public void createHandleAfterFinish(int i) {
        DFKYCUtils.logI(TAG, "createHandleAfterFinish", i);
        if (mKYCSDK != null) {
            mCaptchaImage = mKYCSDK.getCaptchaImage();
        }
        callbackCreateFinish(i);
        mInitFinish = true;
    }

    private void callbackCreateFinish(int i){
        if (mKYCSDKViewList != null){
            for (DFKYCSDKView kycSDKView : mKYCSDKViewList){
                kycSDKView.createFinish(i);
            }
        }
    }

    @Override
    public void destroyAfterFinish() {
        DFKYCUtils.logI(TAG, "destroyAfterFinish");
        mInitFinish = false;
    }

    @Override
    public void sendUIDAfterFinish(DFSendUIDResult sendUIDResult) {
        DFKYCUtils.logI(TAG, "sendUIDAfterFinish");
        callbackSendUIDResult(sendUIDResult);
    }

    private void callbackSendUIDResult(DFSendUIDResult sendUIDResult){
        if (mKYCSDKViewList != null){
            for (DFKYCSDKView kycSDKView : mKYCSDKViewList){
                kycSDKView.returnUIDResult(sendUIDResult);
            }
        }
    }

    @Override
    public void getUIDResultAfterFinish(DFUIDResult uidResult) {
        DFKYCUtils.logI(TAG, "getUIDResultAfterFinish");
        callbackGetUIDResult(uidResult);
    }

    private void callbackGetUIDResult(DFUIDResult uidResult){
        if (mKYCSDKViewList != null){
            for (DFKYCSDKView kycSDKView : mKYCSDKViewList){
                kycSDKView.returnGetUIDResult(uidResult);
            }
        }
    }

    public Bitmap getCaptchaImage() {
        Bitmap captchaImage = null;
        if (mCaptchaImage != null) {
            captchaImage = BitmapFactory.decodeByteArray(mCaptchaImage, 0, mCaptchaImage.length);
        }
        return captchaImage;
    }

    public void addKYCSDKView(DFKYCSDKView kycSDKView) {
        mKYCSDKViewList.add(kycSDKView);
    }

    public void removeSDKView(DFKYCSDKView kycSDKView){
        if (mKYCSDKViewList != null && mKYCSDKViewList.contains(kycSDKView)){
            mKYCSDKViewList.remove(kycSDKView);
        }
    }

    public interface DFKYCSDKView {
        void returnRequestPermission(boolean success);

        void returnUIDResult(DFSendUIDResult sendUIDResult);

        void returnGetUIDResult(DFUIDResult uidResult);

        void createFinish(int result);
    }
}

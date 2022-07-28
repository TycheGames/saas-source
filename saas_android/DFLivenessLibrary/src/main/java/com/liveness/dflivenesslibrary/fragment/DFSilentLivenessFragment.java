package com.liveness.dflivenesslibrary.fragment;

import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.View;
import android.widget.TextView;

import com.deepfinch.liveness.DFLivenessSDK;
import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.liveness.util.Constants;
import com.liveness.dflivenesslibrary.process.DFSilentLivenessProcess;
import com.liveness.dflivenesslibrary.utils.DFBitmapUtils;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class DFSilentLivenessFragment extends DFLivenessBaseFragment {
    private static final String TAG = DFSilentLivenessFragment.class.getSimpleName();
    private TextView mTvHintView;

    @Override
    protected void initialize() {
        super.initialize();
        mProcess = new DFSilentLivenessProcess(getActivity(), mCameraBase);
        mProcess.registerLivenessDetectCallback(mLivenessListener);
        mOverlayView.showBorder();
        mVGBottomDots.setVisibility(View.INVISIBLE);
        mTimeView.setVisibility(View.GONE);
        mGvView.setVisibility(View.GONE);
        mDetectList = new String[1];
        mDetectList[0] = Constants.HOLD_STILL;
        mTimeViewContoller = null;

        mTvHintView = (TextView) mRootView.findViewById(R.id.id_tv_silent_hint);
        mNoteTextView.setVisibility(View.GONE);
        mTvHintView.setVisibility(View.VISIBLE);
        if (mNoFaceHint != null) {
            mTvHintView.setText(mNoFaceHint);
        }
    }

    @Override
    protected void refreshHintText(String hintStr) {
        if (hintStr != null) {
            mTvHintView.setText(hintStr);
        }
    }

    @Override
    protected void startAnimation(int animation) {
    }

    @Override
    protected boolean isSilent() {
        return true;
    }

    @Override
    protected void updateUi(int stringId, int animationId, int number) {
    }

    @Override
    protected void onLivenessDetectCallBack(final int value, final int status, final byte[] livenessEncryptResult, final DFLivenessSDK.DFLivenessImageResult[] imageResult) {
        getActivity().runOnUiThread(new Runnable() {
            @Override
            public void run() {
                if (value == Constants.LIVENESS_SUCCESS) {
                    if (imageResult != null) {
                        for (DFLivenessSDK.DFLivenessImageResult itemImageResult : imageResult) {
                            byte[] image = itemImageResult.image;
                            Bitmap cropBitmap = null;
                            BitmapFactory.Options options = new BitmapFactory.Options();
                            options.inPreferredConfig = Bitmap.Config.ARGB_8888;
                            cropBitmap = BitmapFactory.decodeByteArray(image, 0, image.length, options);
                            Bitmap bmp = DFBitmapUtils.cropResultBitmap(cropBitmap, mCameraBase.getPreviewWidth(), mCameraBase.getPreviewHeight(), mCameraBase.getScanRatio());
                            itemImageResult.detectImage = DFBitmapUtils.convertBmpToJpeg(bmp);
                            DFBitmapUtils.recyleBitmap(cropBitmap);
                            DFBitmapUtils.recyleBitmap(bmp);
                        }
                    }

                    mLivenessResultFileProcess.saveFinalEncrytFile(livenessEncryptResult, imageResult);
                } else if (value == Constants.DETECT_BEGIN_WAIT) {
                    showDetectWaitUI();
                } else if (value == Constants.DETECT_END_WAIT) {
                    removeDetectWaitUI();
                }
            }
        });

    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        if (mLivenessCallback != null) {
            mLivenessCallback.startDetect();
        }
    }

    protected int isBottomDotsVisibility() {
        return View.INVISIBLE;
    }
}

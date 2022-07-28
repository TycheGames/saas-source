package com.liveness.dflivenesslibrary.fragment;

import android.graphics.Color;
import android.hardware.Sensor;
import android.hardware.SensorEvent;
import android.hardware.SensorEventListener;
import android.os.Bundle;
import android.text.TextUtils;
import android.util.Log;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.deepfinch.liveness.DFLivenessSDK;
import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.callback.DFLivenessResultCallback;
import com.liveness.dflivenesslibrary.fragment.model.DFSilentOverlayModel;
import com.liveness.dflivenesslibrary.liveness.util.DFSensorManager;
import com.liveness.dflivenesslibrary.liveness.util.LivenessUtils;
import com.liveness.dflivenesslibrary.process.DFLivenessBaseProcess;
import com.liveness.dflivenesslibrary.utils.DFViewShowUtils;
import com.liveness.dflivenesslibrary.view.CircleTimeView;
import com.liveness.dflivenesslibrary.view.DFGifView;
import com.liveness.dflivenesslibrary.view.DeepFinchAlertDialog;
import com.liveness.dflivenesslibrary.view.TimeViewContoller;

import java.util.HashMap;
import java.util.Map;

import static com.liveness.dflivenesslibrary.liveness.DFLivenessBaseActivity.EXTRA_MOTION_SEQUENCE;
import static com.liveness.dflivenesslibrary.liveness.DFLivenessBaseActivity.KEY_HINT_MESSAGE_FACE_NOT_VALID;
import static com.liveness.dflivenesslibrary.liveness.DFLivenessBaseActivity.KEY_HINT_MESSAGE_HAS_FACE;
import static com.liveness.dflivenesslibrary.liveness.DFLivenessBaseActivity.KEY_HINT_MESSAGE_NO_FACE;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class DFLivenessBaseFragment extends DFProductFragmentBase {
    private static final String TAG = "DFLivenessFragment";

    private static final int CURRENT_ANIMATION = -1;

    protected DFLivenessBaseProcess mProcess;
    protected DFGifView mGvView;
    protected TextView mNoteTextView;
    protected ViewGroup mVGBottomDots;
    private RelativeLayout mWaitDetectView;
    private View mAnimFrame;
    protected CircleTimeView mTimeView;
    protected String[] mDetectList = null;
    private DeepFinchAlertDialog mDialog;
    protected TimeViewContoller mTimeViewContoller;
    protected DFLivenessSDK.DFLivenessMotion[] mMotionList = null;
    private boolean mIsOnlySilent = false;
    protected DFSensorManager mSensorManger;
    private boolean mIsStart = false;
    protected DFLivenessResultCallback mLivenessResultFileProcess;

    protected String mHasFaceHint, mNoFaceHint, mFaceNotValid;
    private Map<String, DFSilentOverlayModel> mFaceHintMap;
    private String mFaceProcessResult;

    protected DFLivenessCallback mLivenessCallback;

    @Override
    protected int getLayoutResourceId() {
        return R.layout.layout_liveness_fragment;
    }

    protected void initView() {
        mGvView = (DFGifView) mRootView.findViewById(R.id.id_gv_play_action);
        mNoteTextView = (TextView) mRootView.findViewById(R.id.noteText);
        mWaitDetectView = (RelativeLayout) mRootView.findViewById(R.id.wait_time_notice);
        mWaitDetectView.setVisibility(View.VISIBLE);
        mAnimFrame = mRootView.findViewById(R.id.anim_frame);
        mAnimFrame.setVisibility(View.INVISIBLE);
        mVGBottomDots = (ViewGroup) mRootView.findViewById(R.id.viewGroup);
        if (mDetectList != null && mDetectList.length >= 1) {
            for (int i = 0; i < mDetectList.length; i++) {
                TextView tvBottomCircle = new TextView(getActivity());
                tvBottomCircle.setBackgroundResource(R.drawable.drawable_liveness_detect_bottom_cicle_bg_selector);
                tvBottomCircle.setEnabled(i == 0 ? false : true);
                LinearLayout.LayoutParams layoutParams = new LinearLayout.LayoutParams(dp2px(8),
                        dp2px(8));
                layoutParams.leftMargin = dp2px(8);
                mVGBottomDots.addView(tvBottomCircle, layoutParams);
            }
        }


        mTimeView = (CircleTimeView) mRootView.findViewById(R.id.time_view);
        mTimeViewContoller = new TimeViewContoller(mTimeView);

        mHasFaceHint = getActivity().getIntent().getStringExtra(KEY_HINT_MESSAGE_HAS_FACE);
        mNoFaceHint = getActivity().getIntent().getStringExtra(KEY_HINT_MESSAGE_NO_FACE);
        mFaceNotValid = getActivity().getIntent().getStringExtra(KEY_HINT_MESSAGE_FACE_NOT_VALID);

        initFaceHintMap();
    }

    @Override
    protected void initialize() {
        mLivenessResultFileProcess = (DFLivenessResultCallback) getActivity();
        mSensorManger = new DFSensorManager(getActivity());
        Bundle bundle = getActivity().getIntent().getExtras();
        if (bundle != null) {
            String motionString = bundle.getString(EXTRA_MOTION_SEQUENCE);
            if (motionString != null) {
                mDetectList = LivenessUtils.getDetectActionOrder(motionString);
                setMotionList(motionString);
            }
        }

        mProcess = new DFLivenessBaseProcess(getActivity(), mCameraBase);
        mProcess.registerLivenessDetectCallback(mLivenessListener);

        initView();
    }

    private void setMotionList(String motionString) {
        mMotionList = LivenessUtils.getMctionOrder(motionString);
        if (mMotionList != null && mMotionList.length == 1) {
            DFLivenessSDK.DFLivenessMotion firstMotion = mMotionList[0];
            if (firstMotion == DFLivenessSDK.DFLivenessMotion.HOLD_STILL) {
                mIsOnlySilent = true;
            }
        }
    }

    @Override
    public void onResume() {
        super.onResume();
        setLivenessState(false);
        mSensorManger.registerListener(mSensorEventListener);
    }

    @Override
    public void onPause() {
        super.onPause();
        setLivenessState(true);
        mSensorManger.unregisterListener(mSensorEventListener);
    }

    protected DFLivenessBaseProcess.OnLivenessCallBack mLivenessListener = new DFLivenessBaseProcess.OnLivenessCallBack() {
        @Override
        public void onLivenessDetect(final int value, final int status, byte[] livenessEncryptResult,
                                     DFLivenessSDK.DFLivenessImageResult[] imageResult) {
            Log.i(TAG, "onLivenessDetect" + "***value***" + value);
            onLivenessDetectCallBack(value, status, livenessEncryptResult, imageResult);
        }

        @Override
        public void onFaceDetect(int value, boolean hasFace, boolean faceValid, DFLivenessSDK.DFRect rect) {
            Log.i(TAG, "onLivenessDetect" + "***value***" + value + "=hasFace=" + hasFace + "=faceValid=" + faceValid);
            onFaceDetectCallback(value, hasFace, faceValid, rect);
        }
    };

    protected void removeDetectWaitUI() {
        mWaitDetectView.setVisibility(View.GONE);
        setLivenessState(false);
        mAnimFrame.setVisibility(View.VISIBLE);
        if (isSilent() == false) {
            onLivenessDetectCallBack(mMotionList[0].getValue(), 0, null, null);
        }
    }

    protected void showDetectWaitUI() {
        mWaitDetectView.setVisibility(View.VISIBLE);
        mIsStart = true;
        if (mTimeViewContoller != null) {
            mTimeViewContoller.setCallBack(null);
        }
    }

    protected boolean isSilent() {
        return false;
    }

    private void setLivenessState(boolean pause) {
        if (null == mProcess) {
            return;
        }
        if (pause) {
            mProcess.stopLiveness();
        } else {
            mProcess.startLiveness();
        }
    }

    protected String getIntentString(String key) {
        return getActivity().getIntent().getStringExtra(key);
    }

    protected String getHint(String hintKey, int defaultHintResId) {
        String blinkHint = getIntentString(hintKey);
        if (TextUtils.isEmpty(blinkHint)) {
            blinkHint = getStringWithID(defaultHintResId);
        }
        return blinkHint;
    }

    protected void onLivenessDetectCallBack(final int value, final int status, final byte[] livenessEncryptResult, final DFLivenessSDK.DFLivenessImageResult[] imageResult) {

    }

    protected void onFaceDetectCallback(int value, boolean hasFace, boolean faceValid, DFLivenessSDK.DFRect rect) {
        if (value == DFLivenessSDK.DFLivenessMotion.HOLD_STILL.getValue()) {
            String hasFaceShow = DFViewShowUtils.booleanTrans(hasFace);
            String faceValidShow = DFViewShowUtils.booleanTrans(faceValid);
            String faceProcessResult = hasFaceShow.concat("_").concat(faceValidShow);
            if (!TextUtils.equals(mFaceProcessResult, faceProcessResult)) {
                mFaceProcessResult = faceProcessResult;
                getActivity().runOnUiThread(new Runnable() {
                    @Override
                    public void run() {
                        DFSilentOverlayModel silentOverlayModel = mFaceHintMap.get(mFaceProcessResult);
                        String hintID = silentOverlayModel.getShowHint();
                        int borderColor = silentOverlayModel.getBorderColor();
                        if (borderColor != -1) {
                            mOverlayView.showBorder();
                            mOverlayView.setBorderColor(borderColor);
                        }
                        refreshHintText(hintID);
                    }
                });

            }

//            if (hasFace) {
//                mOverlayView.setFaceRect(new RectF(rect.left, rect.top, rect.right, rect.bottom), mCameraBase.getCameraOrientation());
//            } else {
//                mOverlayView.setFaceRect(new RectF(0, 0, 0,0), mCameraBase.getCameraOrientation());
//            }
        } else {
            mOverlayView.hideBorder();
        }
    }

    protected void refreshHintText(String hintStr) {
        if (hintStr != null) {
            mNoteTextView.setText(hintStr);
        }
    }

    protected int isBottomDotsVisibility() {
        return View.VISIBLE;
    }

    protected void showIndicateView() {
        if (mGvView != null) {
            mGvView.setVisibility(View.VISIBLE);
        }
        if (mVGBottomDots != null) {
            mVGBottomDots.setVisibility(isBottomDotsVisibility());
        }
        if (mNoteTextView != null && !isSilent()) {
            mNoteTextView.setVisibility(View.VISIBLE);
        }
    }

    private boolean isDialogShowing() {
        return mDialog != null && mDialog.isShowing();
    }

    private void hideTimeContoller() {
        if (mTimeViewContoller != null) {
            mTimeViewContoller.hide();
        }
    }

    private void hideIndicateView() {
        if (mGvView != null) {
            mGvView.setVisibility(View.GONE);
        }
        if (mVGBottomDots != null) {
            mVGBottomDots.setVisibility(View.GONE);
        }
        if (mNoteTextView != null) {
            mNoteTextView.setVisibility(View.GONE);
        }
    }

    @Override
    public void onDestroy() {
        super.onDestroy();
        if (mProcess != null) {
            mProcess.registerLivenessDetectCallback(null);
            mProcess.stopDetect();
            mProcess.exitDetect();

            mProcess = null;
        }
        if (mTimeViewContoller != null) {
            mTimeViewContoller.setCallBack(null);
            mTimeViewContoller = null;
        }
    }

    protected void startAnimation(int animation) {
        if (animation != CURRENT_ANIMATION) {
            mGvView.setMovieResource(animation);
            if (isDialogShowing()) {
                return;
            }
        }
    }

    private void startCountdown() {
        if (mTimeViewContoller != null) {
            mTimeViewContoller.start();
            mTimeViewContoller.setCallBack(new TimeViewContoller.CallBack() {
                @Override
                public void onTimeEnd() {
                    mProcess.onTimeEnd();
                }
            });
        }
    }

    protected void updateUi(int stringId, int animationId, int number) {
        String stringWithID = getStringWithID(stringId);
        updateUi(stringWithID, animationId, number);
    }

    protected void updateUi(String string, int animationId, int number) {
        LivenessUtils.logI(TAG, "mNoteTextView", "stringId", string);
        mNoteTextView.setText(string);
        if (animationId != 0) {
            startAnimation(animationId);
        }
        if (number >= 0) {
            View childAt = mVGBottomDots.getChildAt(number);
            childAt.setEnabled(false);
        }
    }

    private String getStringWithID(int id) {
        return getResources().getString(id);
    }

    protected SensorEventListener mSensorEventListener = new SensorEventListener() {

        @Override
        public void onAccuracyChanged(Sensor arg0, int arg1) {
        }

        @Override
        public void onSensorChanged(SensorEvent event) {
            mProcess.addSequentialInfo(event.sensor.getType(), event.values);
        }
    };

    private int dp2px(float dpValue) {
        int densityDpi = this.getResources().getDisplayMetrics().densityDpi;
        return (int) (dpValue * (densityDpi / 160));
    }

    private void initFaceHintMap() {
        mFaceHintMap = new HashMap<>();
        mFaceHintMap.put("0_0", new DFSilentOverlayModel(mNoFaceHint, Color.RED));
        mFaceHintMap.put("0_1", new DFSilentOverlayModel(mNoFaceHint, Color.RED));
        mFaceHintMap.put("1_0", new DFSilentOverlayModel(mFaceNotValid, Color.RED));
        mFaceHintMap.put("1_1", new DFSilentOverlayModel(mHasFaceHint, Color.GREEN));
    }

    public void setLivenessCallback(DFLivenessCallback mLivenessCallback) {
        this.mLivenessCallback = mLivenessCallback;
    }

    public interface DFLivenessCallback {
        void startDetect();
    }

}

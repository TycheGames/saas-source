package com.liveness.dflivenesslibrary.view;

import android.os.Handler;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class TimeViewContoller implements Runnable {
    public static final String TAG = "TimeViewContoller";

    private ITimeViewBase mTimeView;
    private Handler mHandler = new Handler();
    private float mCurrentTime;
    private int mMaxTime;
    private boolean mStop;
    private CallBack mCallBack;

    public interface CallBack{
        void onTimeEnd();
    }

    public TimeViewContoller(ITimeViewBase view){
        mTimeView = view;
        if (mTimeView != null) {
            mMaxTime = mTimeView.getMaxTime();
        }
    }

    @Override
    public void run() {
        if(mStop){
            return;
        }
        if(mCurrentTime > mMaxTime){
            onTimeEnd();
            return ;
        }
        mHandler.postDelayed(this, 50);
        mCurrentTime = mCurrentTime + 0.05f;
        if (mTimeView != null) {
            mTimeView.setProgress(mCurrentTime);
        }
    }

    public void stop(){
        mStop = true;
        mHandler.removeCallbacksAndMessages(null);
    }

    public void start(){
        start(true);
    }

    public void start(boolean again){
        if(!again){
            if(!mStop){
                return;
            }
            mStop = false;
            if(mCurrentTime > mMaxTime){
                onTimeEnd();
                return;
            }
            mHandler.removeCallbacksAndMessages(null);
            mHandler.post(this);
        }else{
            reset();
        }
    }

    private void onTimeEnd() {
        hide();
        if(null != mCallBack){
            mCallBack.onTimeEnd();
        }
    }

    public void setCallBack(CallBack callback){
        mCallBack = callback;
    }

    private void reset(){
        show();
        mCurrentTime = 0;
        mHandler.removeCallbacksAndMessages(null);
        mHandler.post(this);
    }

    public void hide(){
        mStop = true;
        mHandler.removeCallbacksAndMessages(null);
        if (mTimeView != null) {
            mTimeView.hide();
        }
    }

    public void show(){
        mStop = false;
        if (mTimeView != null) {
            mTimeView.show();
        }
    }

}

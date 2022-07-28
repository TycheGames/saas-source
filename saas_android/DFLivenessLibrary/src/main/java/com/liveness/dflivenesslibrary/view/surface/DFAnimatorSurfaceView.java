package com.liveness.dflivenesslibrary.view.surface;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.PixelFormat;
import android.graphics.PorterDuff;
import android.os.Handler;
import android.os.HandlerThread;
import android.util.AttributeSet;
import android.view.SurfaceHolder;
import android.view.SurfaceView;

import com.liveness.dflivenesslibrary.utils.DFListUtils;

import java.util.ArrayList;
import java.util.List;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved.
 */

public class DFAnimatorSurfaceView extends SurfaceView implements SurfaceHolder.Callback {
    public static final String TAG = "LFSurfaceView";

    private Handler mHandler;
    private SurfaceHolder mHolder;
    private int mAnimatorDelayTime = 30;
    private List<DFAnimatorInterface> mAnimatorInterfaceList;

    private boolean mIsCanDraw;

    public DFAnimatorSurfaceView(Context context) {
        super(context);
        init(context);
    }

    public DFAnimatorSurfaceView(Context context, AttributeSet attrs) {
        super(context, attrs);
        init(context);
    }

    public DFAnimatorSurfaceView(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        init(context);
    }

    private void init(Context context) {
        mHolder = this.getHolder();
        mHolder.setFormat(PixelFormat.TRANSPARENT);
        this.setZOrderOnTop(true);
        mHolder.addCallback(this);
        setLayerType(LAYER_TYPE_HARDWARE, null);

        HandlerThread handlerThread = new HandlerThread("CircleAnimatorThread");
        handlerThread.start();
        mHandler = new Handler(handlerThread.getLooper());


    }

    @Override
    public void surfaceCreated(SurfaceHolder holder) {
//        LFLog.i(TAG, "surfaceCreated: " + getLayerType());
        mIsCanDraw = true;
    }

    @Override
    public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {
//        LFLog.i(TAG, "surfaceChanged");
        startAnimator();
    }

    @Override
    public void surfaceDestroyed(SurfaceHolder holder) {
//        LFLog.i(TAG, "surfaceDestroyed");
        endAnimator();
        mIsCanDraw = false;
    }

    public void setAnimator(DFAnimatorInterface animator) {
        addAnimator(animator);
    }

    public void addAnimator(DFAnimatorInterface animator) {
        if (mAnimatorInterfaceList == null) {
            mAnimatorInterfaceList = new ArrayList<>();
        }
        this.mAnimatorInterfaceList.add(animator);
    }

    public void removeAnimator(DFAnimatorInterface animator) {
        if (mAnimatorInterfaceList != null) {
            mAnimatorInterfaceList.remove(animator);
        }
    }

    public void startAnimator() {
        endAnimator();
        if (!DFListUtils.isEmpty(mAnimatorInterfaceList)) {
            if (mHandler != null) {
                mHandler.postDelayed(mAnimatorRunnable, mAnimatorDelayTime);
            }
        }
    }

    public void endAnimator() {
        if (mHandler != null) {
            mHandler.removeCallbacksAndMessages(null);
        }
    }

    private Runnable mAnimatorRunnable = new Runnable() {
        @Override
        public void run() {
            if (mIsCanDraw) {
                doAnimator();
            }
            startAnimator();
        }
    };

    private void doAnimator() {
//        LFLog.i(TAG, "doAnimator");

        Canvas canvas = null;
        try {
            canvas = mHolder.lockCanvas(); //Access to the canvas
            if (canvas != null) {
                canvas.drawColor(0, PorterDuff.Mode.CLEAR);
                try {
                    playAnimator(canvas);
                } catch (Exception e) {
                    e.printStackTrace();
                    mHolder.unlockCanvasAndPost(canvas);//Unlock the canvas and submit the finished image
                }
                mHolder.unlockCanvasAndPost(canvas);//Unlock the canvas and submit the finished image
            }

        } catch (Exception e) {
            // TODO: handle exception
            e.printStackTrace();
        }
    }

    private void playAnimator(Canvas canvas) {
        if (mAnimatorInterfaceList != null) {
            for (DFAnimatorInterface animatorInterface : mAnimatorInterfaceList) {
                animatorInterface.playAnimator(canvas, this);
            }
        }
    }

    public void reverseAnimator() {
        if (mAnimatorInterfaceList != null) {
            for (DFAnimatorInterface animatorInterface : mAnimatorInterfaceList) {
                animatorInterface.reverseAnimator(this);
            }
        }
    }
}

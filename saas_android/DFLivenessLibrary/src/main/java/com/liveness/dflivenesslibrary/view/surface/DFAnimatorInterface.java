package com.liveness.dflivenesslibrary.view.surface;

import android.graphics.Bitmap;
import android.graphics.Canvas;
import android.os.Handler;
import android.view.SurfaceView;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved.
 */

public abstract class DFAnimatorInterface {
    private Bitmap mSourceBitmap;

    protected LFSurfaceAnimatorListener mAnimatorListener;

    protected Handler mHandler;

    protected boolean mStartAnimator = true;

    public DFAnimatorInterface() {
        mHandler = new Handler();
    }

    public void playAnimator(Canvas canvas, SurfaceView surfaceView) {
        if (mStartAnimator) {
            doAnimator(canvas, surfaceView);
        }
    }

    public abstract void doAnimator(Canvas canvas, SurfaceView surfaceView);

    public void reverseAnimator(SurfaceView surfaceView) {

    }

    protected void runUIThread(Runnable runnable) {
        if (mHandler != null) {
            mHandler.post(runnable);
        }
    }

    public void setBitmap(Bitmap bitmap) {
        this.mSourceBitmap = bitmap;
    }

    public Bitmap getSourceBitmap() {
        return mSourceBitmap;
    }


    public void startAnimator() {
        mStartAnimator = true;
    }

    public void clearAnimator() {
        mStartAnimator = false;
    }

    public void setAnimatorListener(LFSurfaceAnimatorListener animatorListener) {
        this.mAnimatorListener = animatorListener;
    }

    public interface LFSurfaceAnimatorListener {
        void onAnimatorStart(DFAnimatorInterface animator);

        void onAnimatorEnd(DFAnimatorInterface animator);

        void onAnimationRepeat(DFAnimatorInterface animator);
    }
}

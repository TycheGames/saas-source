package com.liveness.dflivenesslibrary.camera;

import android.content.Context;
import android.util.AttributeSet;
import android.view.SurfaceView;
import android.widget.FrameLayout;

import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.liveness.util.Constants;
import com.liveness.dflivenesslibrary.view.DFLivenessOverlayView;


public class FixedAspectRatioFrameLayout  extends FrameLayout {
    private SurfaceView mSurfaceView;
    private DFLivenessOverlayView mOverlayView;

    public FixedAspectRatioFrameLayout(Context context)
    {
        super(context);
    }

    public FixedAspectRatioFrameLayout(Context context, AttributeSet attrs)
    {
        super(context, attrs);
    }

    public FixedAspectRatioFrameLayout(Context context, AttributeSet attrs, int defStyle)
    {
        super(context, attrs, defStyle);
    }
    
    @Override
    protected void onLayout(boolean changed, int l, int t, int r, int b) {
        super.onLayout(changed, l, t, r, b);

        if (mSurfaceView == null) {
            mSurfaceView = (SurfaceView) findViewById(R.id.surfaceViewCamera);
        }

        if(mOverlayView == null) {
            mOverlayView = (DFLivenessOverlayView) findViewById(R.id.id_ov_mask);
        }

        if (mSurfaceView != null) {
             int h = b - t;
             int w = r - l;

             int newW = (int) ((float)Constants.PREVIEW_HEIGHT / Constants.PREVIEW_WIDTH * h);
             if (newW > w) {
                 int newL = -(newW - w) / 2;
                 int newR = newL + newW;
                 mSurfaceView.layout(newL, t, newR, b);
                 mOverlayView.layout(newL, t, newR, b);

             } else {
                int newH = (int) ((float)Constants.PREVIEW_WIDTH / Constants.PREVIEW_HEIGHT * w);
                int newT = -(newH - h ) / 2;
                int newB = newT + newH;
                mSurfaceView.layout(0, newT, w, newB);
                mOverlayView.layout(0, newT, w, newB);
             }
         }
    }
}

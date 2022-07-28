package com.liveness.dflivenesslibrary.camera;

import android.content.Context;
import android.util.AttributeSet;
import android.widget.FrameLayout;

import com.liveness.dflivenesslibrary.liveness.util.Constants;

/**
 * A layout which handles the preview aspect ratio.
 */
public class PreviewFrameLayout extends FrameLayout {

    private int mAspectRatioWidth = Constants.PREVIEW_WIDTH;
    private int mAspectRatioHeight = Constants.PREVIEW_HEIGHT;

    private static final String TAG = "PreviewFrameLayout";

    /**
     * A callback to be invoked when the preview frame's size changes.
     */
    public interface OnSizeChangedListener {
        public void onSizeChanged(int width, int height);
    }

    private double mAspectRatio;
    private OnSizeChangedListener mListener;

    public PreviewFrameLayout(Context context, AttributeSet attrs) {
        super(context, attrs);
        init(context, attrs);
    }

    private void init(Context context, AttributeSet attrs) {

    }

    public void setAspectRatio(double ratio) {
        if (ratio <= 0.0) throw new IllegalArgumentException();

        if (mAspectRatio != ratio) {
            mAspectRatio = ratio;
            requestLayout();
        }
    }

    public void setOnSizeChangedListener(OnSizeChangedListener listener) {
        mListener = listener;
    }

    @Override
    protected void onSizeChanged(int w, int h, int oldw, int oldh) {
        if (mListener != null) mListener.onSizeChanged(w, h);
    }

    @Override
    protected void onLayout(boolean changed, int l, int t, int r, int b) {
        super.onLayout(changed, l, t, r, b);
    }

    @Override
    protected void onMeasure(int widthMeasureSpec, int heightMeasureSpec) {
        int originalWidth = MeasureSpec.getSize(widthMeasureSpec);
        int originalHeight = MeasureSpec.getSize(heightMeasureSpec);
        int finalWidth = originalWidth;
        int finalHeight = originalHeight;
        float scaleRadio = 0;
        float temp_size = -20.f;
        scaleRadio = (originalWidth + temp_size) / mAspectRatioHeight;
        int calculatedHeight = (int) (scaleRadio * mAspectRatioWidth);
        if (calculatedHeight > originalHeight) {
            finalWidth = originalHeight * mAspectRatioHeight / mAspectRatioWidth;
            finalHeight = originalHeight;
        } else {
            finalWidth = originalWidth;
            finalHeight = calculatedHeight;
        }
        super.onMeasure(
                MeasureSpec.makeMeasureSpec(finalWidth, MeasureSpec.EXACTLY),
                MeasureSpec.makeMeasureSpec(finalHeight, MeasureSpec.EXACTLY)
        );
    }
}


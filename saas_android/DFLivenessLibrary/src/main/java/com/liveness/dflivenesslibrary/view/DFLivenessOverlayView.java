package com.liveness.dflivenesslibrary.view;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.graphics.Canvas;
import android.graphics.Color;
import android.graphics.Matrix;
import android.graphics.Paint;
import android.graphics.Path;
import android.graphics.PixelFormat;
import android.graphics.PorterDuff;
import android.graphics.PorterDuffXfermode;
import android.graphics.Rect;
import android.graphics.RectF;
import android.os.Handler;
import android.os.HandlerThread;
import android.util.AttributeSet;
import android.util.DisplayMetrics;
import android.util.TypedValue;
import android.view.SurfaceHolder;
import android.view.SurfaceView;

import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.liveness.util.Constants;
import com.liveness.dflivenesslibrary.utils.DFListUtils;
import com.liveness.dflivenesslibrary.view.surface.DFAnimatorInterface;

import java.util.ArrayList;
import java.util.List;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class DFLivenessOverlayView extends SurfaceView implements SurfaceHolder.Callback {
    private static final String TAG = DFLivenessOverlayView.class.getSimpleName();

    /**
     * the color of four corner
     */
    private int mBorderColor = Color.TRANSPARENT;

    /**
     * paint of border
     */
    private Paint mBorderPaint;

    protected Paint mXmodePaint;

    protected Paint mFaceRectPaint;


    /**
     * color of background
     */
    protected int mBackgroundColor = Color.WHITE;

    /**
     * region of background
     */
    protected Path mLockedBackgroundPath;

    /**
     * background's paint
     */
    protected Paint mLockedBackgroundPaint;

    protected RectF mFaceRect;
    protected int mOrientation;

    /**
     * scanner region
     */
    protected Rect mScanRect;

    /**
     * scanner resource
     */
    protected Bitmap mScanLineVerticalBitmap;


    protected Bitmap mDisplayBitmap;

    protected Bitmap mAnimationBitmap = null;
    protected int mCurrentLineOffset = 0;
    protected Matrix mRotateMatrix = new Matrix();
    protected Canvas mCanvas = new Canvas();
    private boolean mIsBorderHidden = true;

    protected PorterDuffXfermode mPorterDuffXfermode = new PorterDuffXfermode(PorterDuff.Mode.DST_IN);


    private int mCircleRadius;

    private int mCircleCenterX, mCircleCenterY;

    protected Handler mHandler;
    private SurfaceHolder mHolder;
    private int mAnimatorDelayTime = 10;
    private List<DFAnimatorInterface> mAnimatorInterfaceList;

    private boolean mIsCanDraw;

    public DFLivenessOverlayView(Context context, AttributeSet attributeSet) {
        super(context, attributeSet);

        initBackgroundPaint();
        initBorderPaint();
        initFaceRectPaint();

        initialize();
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inScaled = false;
        mScanLineVerticalBitmap = BitmapFactory.decodeResource(getResources(), R.drawable.livenesslibrary_icon_scanner_line, options);

        mXmodePaint = new Paint(Paint.ANTI_ALIAS_FLAG);
        mXmodePaint.setFilterBitmap(false);

        initAnimator();
    }

    private void initAnimator() {
        mHolder = this.getHolder();
        mHolder.setFormat(PixelFormat.TRANSPARENT);
//        this.setZOrderOnTop(true);
        mHolder.addCallback(this);
        setLayerType(LAYER_TYPE_HARDWARE, null);

        HandlerThread handlerThread = new HandlerThread("CircleAnimatorThread");
        handlerThread.start();
        mHandler = new Handler(handlerThread.getLooper());

        DFOverlayDrawAnimator overlayDrawAnimator = new DFOverlayDrawAnimator();
        addAnimator(overlayDrawAnimator);
    }

    protected void initialize() {
        DisplayMetrics displayMetrics = getResources().getDisplayMetrics();
        float scale = displayMetrics.heightPixels / 1920.f;
        mCircleRadius = (int) (437 * scale);
        mCircleCenterY = (int) (319 * scale + mCircleRadius);
    }

    private void initBackgroundPaint() {
        mLockedBackgroundPaint = new Paint(Paint.ANTI_ALIAS_FLAG);
        mLockedBackgroundPaint.clearShadowLayer();
        mLockedBackgroundPaint.setStyle(Paint.Style.FILL);
        mLockedBackgroundPaint.setColor(mBackgroundColor); // 75% black
//        mLockedBackgroundPaint.setAlpha(200);//set BackGround alpha, range of value 0~255
    }

    private void initBorderPaint() {
        mBorderPaint = new Paint(Paint.ANTI_ALIAS_FLAG);
        mBorderPaint.clearShadowLayer();
        mBorderPaint.setStyle(Paint.Style.STROKE);
        mBorderPaint.setColor(mBorderColor);
        mBorderPaint.setStrokeWidth(14);
    }

    private void initFaceRectPaint() {
        DisplayMetrics metrics = getResources().getDisplayMetrics();

        int stroke = (int) TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_DIP, 2, metrics);
        mFaceRectPaint = new Paint();
        mFaceRectPaint.setAntiAlias(true);
        mFaceRectPaint.setDither(true);
        mFaceRectPaint.setColor(Color.GREEN);
        mFaceRectPaint.setStrokeWidth(stroke);
        mFaceRectPaint.setStyle(Paint.Style.STROKE);
    }

    public void setBorderColor(int color) {
        if (mIsBorderHidden == true) {
            return;
        }
        mBorderColor = color;
        if (mBorderPaint != null) {
            mBorderPaint.setColor(mBorderColor);
        }
        postInvalidate();
    }

    public void showBorder() {
        mIsBorderHidden = false;
        setBorderColor(Color.RED);
    }

    public void hideBorder() {
        mIsBorderHidden = true;
        mBorderColor = Color.TRANSPARENT;
        if (mBorderPaint != null) {
            mBorderPaint.setColor(mBorderColor);
        }
        postInvalidate();
    }
//    @Override
//    public void onDraw(Canvas canvas) {
//       drawOverlay(canvas);
//    }

    private void drawOverlay(Canvas canvas) {
        if (mScanRect == null) {
            return;
        }
        canvas.drawPath(mLockedBackgroundPath, mLockedBackgroundPaint);
        canvas.drawCircle(mCircleCenterX, mCircleCenterY, mCircleRadius, mBorderPaint);
        drawVerticalScanLine(canvas);

        if (mFaceRect != null && mFaceRect.width() > 0) {
            int viewWidth = getWidth();
            int viewHeight = getHeight();
            float scaleX = (float) viewWidth / (float) Constants.PREVIEW_HEIGHT; // 480
            float scaleY = (float) viewHeight / (float) Constants.PREVIEW_WIDTH; // 640

            RectF rectF = new RectF(mFaceRect.left * scaleX, mFaceRect.top * scaleY, mFaceRect.right * scaleX, mFaceRect.bottom * scaleY);

            float left = rectF.left;
            float right = rectF.right;
            rectF.left = getWidth() - right;
            rectF.right = getWidth() - left;

            // Log.e(TAG, String.format("draw rect: %f, %f, w:%f, h:%f", rectF.left, rectF.top, rectF.right - rectF.left, rectF.bottom - rectF.top));
            canvas.drawRect(rectF, mFaceRectPaint);
        }
        // canvas.drawRect(mScanRect, mFaceRectPaint);

    }


    public Rect getScanRect() {
        return mScanRect;
    }

    public RectF getScanRectRatio() {
        RectF ratioRectF = new RectF();
        ratioRectF.left = (float) mScanRect.left / getWidth();
        ratioRectF.top = (float) mScanRect.top / getHeight();
        ratioRectF.right = (float) mScanRect.right / getWidth();
        ratioRectF.bottom = (float) mScanRect.bottom / getHeight();
        return ratioRectF;
    }

    public void setFaceRect(RectF rect, int orientation) {
        // float scaleX = (float) getWidth() / (float) Constants.PREVIEW_WIDTH;
        // float scaleY = (float) getHeight() / (float) Constants.PREVIEW_HEIGHT;

        mOrientation = orientation;

        // Log.e(TAG, String.format("setFaceRect: %f, %f, w:%f, h:%f", rect.left, rect.top, rect.right - rect.left, rect.bottom - rect.top));

//        if (mOrientation == 90) {
//            float height = (float) Constants.PREVIEW_HEIGHT;
//
//            float left = rect.left;
//            rect.left = height - rect.bottom;
//            rect.bottom = rect.right;
//            rect.right = height - rect.top;
//            rect.top = left;
//        } else if (mOrientation == 270) {
//            float width = (float) Constants.PREVIEW_WIDTH;
//
//            float left = rect.left;
//            rect.left = rect.top;
//            rect.top = width - rect.right;
//            rect.right = rect.bottom;
//            rect.bottom = width - left;
//        }

        // Log.e(TAG, String.format("rotated setFaceRect: %f, %f, w:%f, h:%f", rect.left, rect.top, rect.right - rect.left, rect.bottom - rect.top));
        mFaceRect = rect;
    }

    protected void initialInfo() {
        mCircleCenterX = getWidth() / 2;
        mScanRect = new Rect(mCircleCenterX - mCircleRadius, mCircleCenterY - mCircleRadius, mCircleCenterX + mCircleRadius, mCircleCenterY + mCircleRadius);
        mLockedBackgroundPath = new Path();
        mLockedBackgroundPath.addRect(new RectF(getLeft(), getTop(), getRight(), getBottom()), Path.Direction.CCW);
        mLockedBackgroundPath.addCircle(mCircleCenterX, mCircleCenterY, mCircleRadius, Path.Direction.CW);

        recycleBitmap(mDisplayBitmap);
        mDisplayBitmap = makeCircleBitmap(mScanRect.width(), mScanRect.height());
        recycleBitmap(mAnimationBitmap);
        mAnimationBitmap = Bitmap.createBitmap(mScanRect.width(), mScanRect.height(), Bitmap.Config.ARGB_8888);
        mCanvas.setBitmap(mAnimationBitmap);
        mCurrentLineOffset = -mScanRect.height() / 2;

    }

    @Override
    public void layout(int l, int t, int r, int b) {
        super.layout(l, t, r, b);

        initialInfo();
        invalidate();
    }


    protected void recycleBitmap(Bitmap bitmap) {
        if (bitmap != null && !bitmap.isRecycled()) {
            bitmap.recycle();
            bitmap = null;
        }
    }

    private Bitmap makeCircleBitmap(int w, int h) {
        Bitmap bm = Bitmap.createBitmap(w, h, Bitmap.Config.ARGB_8888);
        Canvas c = new Canvas(bm);
        Paint p = new Paint(Paint.ANTI_ALIAS_FLAG);

        c.drawCircle(w / 2, h / 2, w / 2, p);
        return bm;
    }

    private Bitmap makeAnimationBmp(int padding) {
        if (mCanvas != null) {
            Canvas canvas = mCanvas;
            mXmodePaint.setXfermode(new PorterDuffXfermode(PorterDuff.Mode.CLEAR));
            canvas.drawPaint(mXmodePaint);

            mRotateMatrix.reset();
            mXmodePaint.setXfermode(null);
            float scaleX = (mScanRect.width() + 0.0f) / mScanLineVerticalBitmap.getWidth();
            mRotateMatrix.setScale(scaleX, scaleX);
            mRotateMatrix.postTranslate(0, padding);
            canvas.drawBitmap(mScanLineVerticalBitmap, mRotateMatrix, mXmodePaint);
            mXmodePaint.setXfermode(mPorterDuffXfermode);
            canvas.drawBitmap(mDisplayBitmap, 0, 0, mXmodePaint);
        }
        return mAnimationBitmap;
    }

    long mLastDrawTime = 0;

    private void drawVerticalScanLine(Canvas canvas) {
        canvas.save();

        if (mDisplayBitmap != null) {
            long currentTimeMillis = System.currentTimeMillis();
            long timeSpace = currentTimeMillis - mLastDrawTime;
//            Log.e(TAG, "drawVerticalScanLine" + "redraw====" + timeSpace);
            mLastDrawTime = currentTimeMillis;
            mCurrentLineOffset += 8;
            canvas.drawBitmap(makeAnimationBmp(mCurrentLineOffset), mScanRect.left, mScanRect.top, mBorderPaint);

            int currentScanLineY = mScanRect.top + mCurrentLineOffset;
            if (currentScanLineY > mScanRect.bottom) {
                mCurrentLineOffset = -mScanRect.height() / 2;
            }
        }

        canvas.restore();

        postInvalidateDelayed(2, mScanRect.left, mScanRect.top, mScanRect.right, mScanRect.bottom);
    }

    private class DFOverlayDrawAnimator extends DFAnimatorInterface {

        @Override
        public void doAnimator(Canvas canvas, SurfaceView surfaceView) {
            drawOverlay(canvas);
        }
    }

    public void addAnimator(DFAnimatorInterface animator) {
        if (mAnimatorInterfaceList == null) {
            mAnimatorInterfaceList = new ArrayList<>();
        }
        this.mAnimatorInterfaceList.add(animator);
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

    private void releaseCanvas() {
        if (mCanvas != null) {
            mCanvas = null;
        }
    }

    public void releaseReSource() {
        releaseCanvas();
        recycleBitmap(mScanLineVerticalBitmap);
        recycleBitmap(mDisplayBitmap);
        recycleBitmap(mAnimationBitmap);
    }

    @Override
    public void surfaceCreated(SurfaceHolder holder) {
        mIsCanDraw = true;
    }

    @Override
    public void surfaceChanged(SurfaceHolder holder, int format, int width, int height) {
        startAnimator();
    }

    @Override
    public void surfaceDestroyed(SurfaceHolder holder) {
        endAnimator();
        mIsCanDraw = false;
//        releaseSource();
    }
}

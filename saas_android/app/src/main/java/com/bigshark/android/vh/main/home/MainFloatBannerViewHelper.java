package com.bigshark.android.vh.main.home;

import android.content.Context;
import android.graphics.drawable.Drawable;
import android.os.Build;
import android.text.TextUtils;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;

import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.fragments.home.MainFragment;
import com.bigshark.android.http.model.home.MainHomeResponseModel;
import com.bigshark.android.jump.JumpOperationHandler;

import org.xutils.common.Callback;
import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.lang.reflect.Field;

/**
 * @author admin
 * @date 2018/3/1
 * 悬浮广告栏
 */
public class MainFloatBannerViewHelper {
    private MainFragment mMainFragment;
    private ImageView floatView;

    private float minX = 0f, minY = 0f, maxX = 0f, maxY = 0f;

    public MainFloatBannerViewHelper(MainFragment fragment, ImageView float_View) {
        try {
            this.mMainFragment = fragment;
            this.floatView = float_View;
            if (Build.VERSION.SDK_INT > Build.VERSION_CODES.KITKAT) {
                minY = getStatusBarHeight();
            }
            this.maxX = ViewUtil.getScreenWidth(mMainFragment.context());
            this.maxY = ViewUtil.getScreenHeight(mMainFragment.context());
            initRewardTask(float_View);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    /**
     * 得到状态栏的高度
     *
     * @return 状态栏的高度
     */
    private int getStatusBarHeight() {
        Class<?> c = null;
        Object obj = null;
        Field field = null;
        int x = 0, sbar = 0;
        try {
            c = Class.forName("com.android.internal.R$dimen");
            obj = c.newInstance();
            field = c.getField("status_bar_height");
            x = Integer.parseInt(field.get(obj).toString());
            sbar = mMainFragment.context().getResources().getDimensionPixelSize(x);
        } catch (Exception e1) {
            e1.printStackTrace();
        }
        return sbar;
    }

    public MainFloatBannerViewHelper(MainFragment fragment, ImageView floatView, float maxX, float maxY) {
        try {
            this.mMainFragment = fragment;
            this.floatView = floatView;
            if (Build.VERSION.SDK_INT > Build.VERSION_CODES.KITKAT) {
                minY = getStatusBarHeight();
            }
            this.maxX = maxX;
            this.maxY = maxY;
            if (maxX == 0) {
                this.maxX = ViewUtil.getScreenWidth(mMainFragment.context());
            }
            if (maxY == 0) {
                this.maxY = ViewUtil.getScreenHeight(mMainFragment.context());
            }
            initRewardTask(floatView);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public MainFloatBannerViewHelper(MainFragment fragment, ImageView float_View, float minX, float minY, float maxX, float maxY) {
        try {
            this.mMainFragment = fragment;
            this.floatView = float_View;
            this.minX = minX;
            this.minY = minY;
            this.maxX = maxX;
            this.maxY = maxY;
            initRewardTask(float_View);
        } catch (Exception e) {
            e.printStackTrace();
        }
    }

    public void setData(final MainHomeResponseModel.FloatImageEntity floatImageEntity) {
        if (floatImageEntity == null) {
            floatView.setVisibility(View.GONE);
            return;
        }

        floatView.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String jumpData = floatImageEntity.getJump();
                if (TextUtils.isEmpty(jumpData)) {
                    return;
                }
                JumpOperationHandler.convert(jumpData).createRequest().setDisplay(mMainFragment).jump();
            }
        });

        x.image().bind(
                floatView,
                floatImageEntity.getImageUrl(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .build(),
                new Callback.CommonCallback<Drawable>() {
                    @Override
                    public void onSuccess(Drawable result) {
                        floatView.setVisibility(View.VISIBLE);
                    }

                    @Override
                    public void onError(Throwable ex, boolean isOnCallback) {

                    }

                    @Override
                    public void onCancelled(CancelledException cex) {

                    }

                    @Override
                    public void onFinished() {

                    }
                }
        );
    }

    //初始化View拖动效果
    private void initRewardTask(final View tv) {
        minX += dip2px(mMainFragment.context(), 10);
        maxX -= dip2px(mMainFragment.context(), 10);
        tv.setOnTouchListener(new View.OnTouchListener() {
            float downY = 0;
            float moveY = 0;
            float oldY = 0;
            float downX = 0;
            float moveX = 0;
            float oldX = 0;
            long oldTime = 0;
            boolean isMove = false;

            @Override
            public boolean onTouch(View v, MotionEvent event) {
                switch (event.getAction()) {
                    case MotionEvent.ACTION_DOWN:
                        downY = event.getRawY();
                        oldY = tv.getY();
                        downX = event.getRawX();
                        oldX = tv.getX();
                        oldTime = System.currentTimeMillis();
                        isMove = false;
                        break;
                    case MotionEvent.ACTION_MOVE:
                        moveY = event.getRawY();
                        moveX = event.getRawX();
                        float newY = tv.getY() + (moveY - downY);
                        float newX = tv.getX() + (moveX - downX);
                        if (Math.abs(newY - oldY) > dip2px(mMainFragment.context(), 5) || Math.abs(newX - oldX) > dip2px(mMainFragment.context(), 5)) {
                            isMove = true;
                        }
                        if (isMove) {
                            if (newY > minY && (newY + tv.getHeight()) < maxY) {
                                tv.setY(newY);
                                downY = moveY;
                            }
                            if (newX > minX && (newX + tv.getWidth()) < maxX) {
                                tv.setX(newX);
                                downX = moveX;
                            }
                        }
                        break;
                    case MotionEvent.ACTION_UP:
                        if (!isMove && System.currentTimeMillis() - oldTime < 300) {
                            tv.performClick();
                        }
                        if (tv.getX() + tv.getWidth() / 2 < maxX / 2) {
                            isLeft = false;
                            tv.setX(minX);
                        } else {
                            isLeft = true;
                            tv.setX(maxX - tv.getWidth());
                        }
                        break;
                    default:
                        break;
                }
                return true;//处理了触摸消息，消息不再传递
            }
        });
    }

    private boolean isLeft = true;

    public void hide() {
        if (floatView != null && floatView.getVisibility() == View.VISIBLE) {
            floatView.clearAnimation();
            if (isLeft) {
                floatView.setAnimation(AnimationUtils.makeOutAnimation(mMainFragment.context(), true));
            } else {
                floatView.setAnimation(AnimationUtils.makeOutAnimation(mMainFragment.context(), false));
            }
            floatView.setVisibility(View.GONE);
        }
    }

    public void show() {
        if (floatView != null && floatView.getVisibility() == View.GONE) {
            floatView.clearAnimation();
            if (isLeft) {
                floatView.setAnimation(AnimationUtils.makeInAnimation(mMainFragment.context(), false));
            } else {
                floatView.setAnimation(AnimationUtils.makeInAnimation(mMainFragment.context(), true));
            }
            floatView.setVisibility(View.VISIBLE);
        }
    }


    private static int dip2px(Context context, float dpValue) {
        int densityDpi = context.getResources().getDisplayMetrics().densityDpi;
        return (int) (dpValue * (densityDpi / 160));
    }


}

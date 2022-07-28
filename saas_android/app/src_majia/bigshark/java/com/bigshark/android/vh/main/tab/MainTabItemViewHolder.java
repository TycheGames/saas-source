package com.bigshark.android.vh.main.tab;

import android.annotation.SuppressLint;
import android.graphics.Color;
import android.support.annotation.NonNull;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.AnimationUtils;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.app.MainTabItemResponseModel;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.core.display.IDisplay;


import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * 主页的tab item
 */
public class MainTabItemViewHolder extends DisplayBaseVh<View, MainTabItemResponseModel> {

    private LinearLayout mTabContentRoot;
    private ImageView mTabIconView;
    private TextView mTabTextView;
    private View mRedPointView;

    private boolean mIsSelected;
    private final int mPosition;

    private Callback mCallback;

    public MainTabItemViewHolder(IDisplay display, int mPosition, @NonNull final Callback mCallback) {
        super(display, LayoutInflater.from(display.act()).inflate(R.layout.main_tab_layout, null));
        this.mPosition = mPosition;
        this.mCallback = mCallback;
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mTabContentRoot = findViewById(R.id.main_tab_content_LinearLayout);
        mTabIconView = findViewById(R.id.main_tab_icon_ImageView);
        mTabTextView = findViewById(R.id.main_tab_title_TextView);
        mRedPointView = findViewById(R.id.main_tab_red_point);
    }


    @Override
    protected void bindListeners() {
        super.bindListeners();
        getRoot().setOnTouchListener(new View.OnTouchListener() {
            @SuppressLint("ClickableViewAccessibility")
            @Override
            public boolean onTouch(View v, MotionEvent event) {
                return mCallback != null && mCallback.intercept(MainTabItemViewHolder.this, mPosition);
            }
        });
    }


    @Override
    public void bindViewData(MainTabItemResponseModel mData) {
        super.bindViewData(mData);
        mTabTextView.setText(mData.getTitle());
    }

    public MainTabItemResponseModel getData() {
        return mData;
    }


    public void refreshBySelectState(boolean isSelected) {
        final boolean preIsSelected = this.mIsSelected;
        this.mIsSelected = isSelected;

        if (isSelected && !preIsSelected) {
            mTabContentRoot.clearAnimation();
            mTabContentRoot.setAnimation(AnimationUtils.loadAnimation(mDisplay.act(), R.anim.main_tab_content_anim_show));
        } else if (!isSelected && preIsSelected) {
            mTabContentRoot.clearAnimation();
            mTabContentRoot.setAnimation(AnimationUtils.loadAnimation(mDisplay.act(), R.anim.main_tab_content_anim_hide));
        }

        String textColorText = isSelected ? mData.getSel_span_color() : mData.getSpan_color();
        mTabTextView.setTextColor(Color.parseColor(textColorText));

        int resId = isSelected ? mData.getSelectIcon() : mData.getUnSelectIcon();
        if (resId != 0) { // 本地默认样式
            mTabIconView.setVisibility(View.VISIBLE);
            mTabIconView.setImageResource(resId);
        } else {
            String imageUrl = isSelected ? mData.getSelectImage() : mData.getNormalImage();
            x.image().bind(
                    mTabIconView,
                    imageUrl,
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .build()
            );
        }

        resetRedPointTip();
    }

    /**
     * 重置选中的小红点
     */
    public void resetRedPointTip() {
        if (mData.isClickRedPoint()) {
            mRedPointView.setVisibility(View.GONE);
            return;
        }

        // 红点逻辑判断
        boolean isShowRedPoint = !TextUtils.isEmpty(mData.getRedPointShowTime()) && !mData.isClickRedPoint();
        mRedPointView.setVisibility(isShowRedPoint ? View.VISIBLE : View.GONE);

        mData.setClickRedPoint(true);
    }


    public interface Callback {
        /**
         * 点击拦截事件 登录判断
         */
        boolean intercept(MainTabItemViewHolder tabVh, int position);
    }

}

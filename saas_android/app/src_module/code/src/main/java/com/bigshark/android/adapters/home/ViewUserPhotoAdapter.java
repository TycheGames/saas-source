package com.bigshark.android.adapters.home;

import android.support.v4.content.ContextCompat;
import android.view.View;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.user.PicsModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.common.util.DensityUtil;
import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/21 19:16
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class ViewUserPhotoAdapter extends BaseQuickAdapter<PicsModel, BaseViewHolder> {

    private IDisplay mDisplay;

    public ViewUserPhotoAdapter(IDisplay display) {
        super(R.layout.adapter_view_userphoto_listitem);
        this.mDisplay = display;
    }

    @Override
    protected void convert(BaseViewHolder helper, PicsModel item) {
        ImageView iv_photo = helper.getView(R.id.iv_photo);
        RelativeLayout rl_photo_state = helper.getView(R.id.rl_photo_state);
        RelativeLayout rl_photo_bg = helper.getView(R.id.rl_photo_bg);
        TextView tv_photo_state = helper.getView(R.id.tv_photo_state);
        //        PhotoUtils.loadPhoto(mDisplay, item.getPic_url(), iv_photo);
        if (item.isIs_burn_after_reading() && item.isIs_red_pack()) {
            x.image().bind(
                    iv_photo,
                    item.getPic_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setRadius(DensityUtil.dip2px(10))
                            .build()
            );
            rl_photo_bg.setVisibility(View.VISIBLE);
            rl_photo_state.setVisibility(View.VISIBLE);
            if (item.isIs_burn()) {
                //已焚毁
                rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.drawable.user_homepage_burn_after_reading_img));
                tv_photo_state.setText("已焚毁");
                tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_857d7a));
            } else {
                rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.mipmap.shade_photo_red_envelope_thumbnail));
                tv_photo_state.setText("阅后即焚的红包照片");
                tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_ff655e));
            }
        } else if (item.isIs_burn_after_reading()) {
            x.image().bind(
                iv_photo,
                item.getPic_url(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setRadius(DensityUtil.dip2px(10))
                        .build()
        );
            rl_photo_bg.setVisibility(View.VISIBLE);
            rl_photo_state.setVisibility(View.VISIBLE);
            if (item.isIs_burn()) {
                //已焚毁
                rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.drawable.user_homepage_burn_after_reading_img));
                tv_photo_state.setText("已焚毁");
                tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_857d7a));
            } else {
                rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.mipmap.shade_photo_not_burning_thumbnail));
                tv_photo_state.setText("阅后即焚");
                tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_ff7729));
            }
        } else if (item.isIs_red_pack()) {
            rl_photo_bg.setVisibility(View.VISIBLE);
            rl_photo_state.setVisibility(View.VISIBLE);
            if (item.isIs_pay()) {
                //红包照片已付款
                if (item.isIs_burn_after_reading()) {
                    x.image().bind(
                            iv_photo,
                            item.getPic_url(),
                            new ImageOptions.Builder()
                                    .setUseMemCache(true)//设置使用缓存
                                    .setRadius(DensityUtil.dip2px(10))
                                    .build()
                    );
                    if (item.isIs_burn()) {
                        //已焚毁
                        rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.drawable.user_homepage_burn_after_reading_img));
                        tv_photo_state.setText("已焚毁");
                        tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_857d7a));
                    } else {
                        rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.mipmap.shade_photo_not_burning_thumbnail));
                        tv_photo_state.setText("阅后即焚");
                        tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_ff7729));
                    }
                } else {
                    x.image().bind(
                            iv_photo,
                            item.getPic_url(),
                            new ImageOptions.Builder()
                                    .setUseMemCache(true)//设置使用缓存
                                    .setRadius(DensityUtil.dip2px(10))
                                    .build()
                    );
                    rl_photo_bg.setVisibility(View.GONE);
                    rl_photo_state.setVisibility(View.GONE);
                }
            } else {
                x.image().bind(
                        iv_photo,
                        item.getPic_url(),
                        new ImageOptions.Builder()
                                .setUseMemCache(true)//设置使用缓存
                                .setRadius(DensityUtil.dip2px(10))
                                .build()
                );
                rl_photo_state.setBackground(ContextCompat.getDrawable(mDisplay.context(), R.mipmap.shade_photo_red_envelope_thumbnail));
                tv_photo_state.setText("红包照片");
                tv_photo_state.setTextColor(ContextCompat.getColor(mDisplay.context(), R.color.color_ff655e));
            }
        } else {
            x.image().bind(
                    iv_photo,
                    item.getPic_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setRadius(DensityUtil.dip2px(10))
                            .build()
            );
            rl_photo_bg.setVisibility(View.GONE);
            rl_photo_state.setVisibility(View.GONE);
        }

    }
}

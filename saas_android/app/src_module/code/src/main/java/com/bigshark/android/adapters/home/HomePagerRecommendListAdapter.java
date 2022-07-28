package com.bigshark.android.adapters.home;

import android.support.v4.content.ContextCompat;
import android.text.Html;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.home.HomePagerRecommendListResponseModel;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/8 10:54
 * @描述 首页 推荐 list adapter
 */
public class HomePagerRecommendListAdapter extends BaseQuickAdapter<HomePagerRecommendListResponseModel, BaseViewHolder> {

    private IDisplay display;
    private ImageView iv_home_item_like;

    public HomePagerRecommendListAdapter(IDisplay display, int layoutResId) {
        super(layoutResId);
        this.display = display;
    }

    @Override
    protected void convert(BaseViewHolder helper, HomePagerRecommendListResponseModel item) {
        ImageView iv_item_head_portrait = helper.getView(R.id.iv_item_head_portrait);
        helper.setText(R.id.tv_home_item_nickname, item.getNickname());
        helper.setImageDrawable(R.id.iv_home_item_gender,
                item.getSex() == 1
                        ? ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_gender_men_icon)
                        : ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_gender_women_icon)
        );
        helper.setText(R.id.iv_home_item_age, item.getAge());
        //        ImageView iv_home_item_vip = helper.getView(R.id.iv_home_item_vip);
        if (item.getSex() == 1) {
            helper.setVisible(R.id.iv_home_item_vip, item.getIs_vip() == 1);
            x.image().bind(
                    iv_item_head_portrait,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
        } else if (item.getSex() == 2) {
            x.image().bind(
                    iv_item_head_portrait,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );

            helper.setImageDrawable(R.id.iv_home_item_vip,
                    item.getIs_real() == 1
                            ? ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_certified_icon)
                            : ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_uncertified_icon)
            );

        }
        helper.setText(R.id.tv_home_item_location, item.getLocation());
        helper.setText(R.id.tv_home_item_career, item.getCareer());
        helper.setText(R.id.tv_home_item_distance, item.getDistance());
        helper.setText(R.id.tv_home_item_online, Html.fromHtml(item.getOnline()));
        //关注
        iv_home_item_like = helper.getView(R.id.iv_home_item_like);
        if (item.getC_status() == 1) {
            iv_home_item_like.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_like_icon));
        } else {
            iv_home_item_like.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.home_listitem_unlike_icon));
        }
        iv_home_item_like.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick("" + helper.getAdapterPosition());
                }
            }
        });
    }

    private OnConfirmClickListener mOnConfirmClickListener;

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }
}

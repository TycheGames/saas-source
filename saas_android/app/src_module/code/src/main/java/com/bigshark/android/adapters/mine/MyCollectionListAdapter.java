package com.bigshark.android.adapters.mine;

import android.app.Activity;
import android.content.Intent;
import android.support.v4.content.ContextCompat;
import android.text.Html;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.UserMenHomePageActivity;
import com.bigshark.android.activities.home.UserWomenHomePageActivity;
import com.bigshark.android.http.model.home.HomePagerRecommendListResponseModel;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/8 10:54
 * @描述 我的收藏 list adapter
 */
public class MyCollectionListAdapter extends BaseQuickAdapter<HomePagerRecommendListResponseModel, BaseViewHolder> {

    private Activity mActivity;
    private ImageView iv_home_item_like;

    public MyCollectionListAdapter(Activity activity) {
        super(R.layout.adapter_home_recommend_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, HomePagerRecommendListResponseModel item) {
        ImageView iv_item_head_portrait = helper.getView(R.id.iv_item_head_portrait);
        helper.setText(R.id.tv_home_item_nickname, item.getNickname());
        helper.setImageDrawable(R.id.iv_home_item_gender, item.getSex() == 1 ? ContextCompat.getDrawable(mActivity, R.mipmap.home_listitem_gender_men_icon) : ContextCompat.getDrawable(mActivity, R.mipmap.home_listitem_gender_women_icon));
        helper.setText(R.id.iv_home_item_age, item.getAge());
        //        ImageView iv_home_item_vip = helper.getView(R.id.iv_home_item_vip);
        if (item.getSex() == 1) {
            x.image().bind(
                    iv_item_head_portrait,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            helper.setVisible(R.id.iv_home_item_vip, item.getIs_vip() == 1);
        } else if (item.getSex() == 2) {
            x.image().bind(
                    iv_item_head_portrait,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );

            helper.setImageDrawable(R.id.iv_home_item_vip, item.getIs_real() == 1 ? ContextCompat.getDrawable(mActivity, R.mipmap.home_listitem_certified_icon) : ContextCompat.getDrawable(mActivity, R.mipmap.home_listitem_uncertified_icon));
        }
        helper.setText(R.id.tv_home_item_location, item.getLocation());
        helper.setText(R.id.tv_home_item_career, item.getCareer());
        helper.setText(R.id.tv_home_item_distance, item.getDistance());
        helper.setText(R.id.tv_home_item_online, Html.fromHtml(item.getOnline()));
        //关注
        iv_home_item_like = helper.getView(R.id.iv_home_item_like);
        iv_home_item_like.setImageDrawable(ContextCompat.getDrawable(mActivity, R.mipmap.home_listitem_like_icon));
        iv_home_item_like.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick("" + helper.getAdapterPosition());
                }
            }
        });
        iv_item_head_portrait.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                //用户主页
                Intent intent = null;
                if (item.getSex() == 1) {
                    intent = new Intent(mActivity, UserMenHomePageActivity.class);
                    intent.putExtra(UserMenHomePageActivity.EXTRA_USER_ID, item.getUser_id());
                } else if (item.getSex() == 2) {
                    intent = new Intent(mActivity, UserWomenHomePageActivity.class);
                    intent.putExtra(UserWomenHomePageActivity.EXTRA_USER_ID, item.getUser_id());
                }
                mActivity.startActivity(intent);
            }
        });
    }

    private OnConfirmClickListener mOnConfirmClickListener;

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }
}

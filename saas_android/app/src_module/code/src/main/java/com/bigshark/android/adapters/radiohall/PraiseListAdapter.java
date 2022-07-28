package com.bigshark.android.adapters.radiohall;

import android.app.Activity;
import android.support.v4.content.ContextCompat;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/28 13:59
 * @描述 点赞列表
 */
public class PraiseListAdapter extends BaseQuickAdapter<RaidoDetailsModel.ClickGoodBean, BaseViewHolder> {

    private Activity mActivity;

    public PraiseListAdapter(Activity activity) {
        super(R.layout.adapter_praise_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, RaidoDetailsModel.ClickGoodBean item) {
        ImageView iv_paraise_listitem_head = helper.getView(R.id.iv_paraise_listitem_head);
        helper.setText(R.id.tv_paraise_listitem_name, item.getNickname());
        if (1 == item.getSex()) {
            x.image().bind(
                    iv_paraise_listitem_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            helper.setImageDrawable(R.id.iv_paraise_listitem_gender, ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_men_icon));
        } else {
            x.image().bind(
                    iv_paraise_listitem_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );

            helper.setImageDrawable(R.id.iv_paraise_listitem_gender, ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_women_icon));
        }
        helper.setText(R.id.tv_paraise_listitem_time, item.getCreated_at());
    }

}

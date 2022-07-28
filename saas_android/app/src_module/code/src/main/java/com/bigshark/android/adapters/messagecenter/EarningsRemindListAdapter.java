package com.bigshark.android.adapters.messagecenter;

import android.app.Activity;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.message.NewsProfitItemModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/13 10:06
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class EarningsRemindListAdapter extends BaseQuickAdapter<NewsProfitItemModel, BaseViewHolder> {

    private Activity mActivity;

    public EarningsRemindListAdapter(Activity activity) {
        super(R.layout.adapter_earnings_remind_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, NewsProfitItemModel item) {
        ImageView iv_icon = helper.getView(R.id.iv_icon);
        x.image().bind(
                iv_icon,
                item.getAvatar(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                        .build()
        );
        helper.setText(R.id.tv_created_at, item.getCreated_at());
        helper.setText(R.id.tv_contents, item.getContents());
        helper.setText(R.id.tv_name, item.getNickname());
        helper.setText(R.id.tv_title, item.getTitle());
    }
}

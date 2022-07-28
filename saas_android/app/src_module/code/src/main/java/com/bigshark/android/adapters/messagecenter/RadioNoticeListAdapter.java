package com.bigshark.android.adapters.messagecenter;

import android.app.Activity;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.message.RadioNoticeItemModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

//import com.bumptech.glide.Glide;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/13 10:06
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RadioNoticeListAdapter extends BaseQuickAdapter<RadioNoticeItemModel, BaseViewHolder> {

    private Activity mActivity;

    public RadioNoticeListAdapter(Activity activity) {
        super(R.layout.adapter_radio_notice_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, RadioNoticeItemModel item) {
        ImageView iv_icon = helper.getView(R.id.iv_icon);
//        Glide.with(mContext).load(item.getAvatar()).into(iv_icon);
        x.image().bind(iv_icon, item.getAvatar(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .build());
        helper.setText(R.id.tv_title, item.getNickname());
        helper.setText(R.id.tv_created_at, item.getCreated_at());
        helper.setText(R.id.tv_contents, "在" + item.getCity() + "发布了一条约会广播，点击查看…");
    }
}

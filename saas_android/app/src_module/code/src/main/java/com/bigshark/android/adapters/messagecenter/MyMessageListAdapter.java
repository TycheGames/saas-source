package com.bigshark.android.adapters.messagecenter;

import android.content.Context;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.message.NewsAllListItemModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

//import com.bumptech.glide.Glide;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 19:11
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class MyMessageListAdapter extends BaseQuickAdapter<NewsAllListItemModel, BaseViewHolder> {

    private Context mContext;

    public MyMessageListAdapter(Context context) {
        super(R.layout.adapter_my_message_listitem);
        this.mContext = context;
    }

    @Override
    protected void convert(BaseViewHolder helper, NewsAllListItemModel item) {
        ImageView iv_icon = helper.getView(R.id.iv_icon);
//        Glide.with(mContext).load(item.getImage()).into(iv_icon);
        x.image().bind(iv_icon, item.getImage(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .build());
        helper.setText(R.id.tv_title, item.getTitle());
        helper.setText(R.id.tv_created_at, item.getCreated_at());
        helper.setText(R.id.tv_contents, item.getContents());
        TextView tv_unread = helper.getView(R.id.tv_unread);
        if (item.getUnread() != 0) {
            tv_unread.setVisibility(View.VISIBLE);
            tv_unread.setText(item.getUnread() + "");
        } else {
            tv_unread.setVisibility(View.INVISIBLE);
        }

    }
}

package com.bigshark.android.adapters.messagecenter;

import android.app.Activity;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.message.NewsSystemItemModel;
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
public class SystematicNotificationListAdapter extends BaseQuickAdapter<NewsSystemItemModel, BaseViewHolder> {

    private Activity mActivity;

    public SystematicNotificationListAdapter(Activity activity) {
        super(R.layout.adapter_systematic_notification_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, NewsSystemItemModel item) {

        helper.setText(R.id.tv_title, item.getTitle());
        helper.setText(R.id.tv_created_at, item.getCreated_at());
        helper.setText(R.id.tv_contents, item.getContents());
        ImageView iv_image = helper.getView(R.id.iv_image);
        if (item.getImg_url() != null) {
            iv_image.setVisibility(View.VISIBLE);
//            Glide.with(attachedActivity).load(item.getImg_url()).into(iv_image);
            x.image().bind(iv_image, item.getImg_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .build());
        } else {
            iv_image.setVisibility(View.GONE);

        }

    }
}

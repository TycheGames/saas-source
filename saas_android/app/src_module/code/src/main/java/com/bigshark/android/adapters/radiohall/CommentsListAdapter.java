package com.bigshark.android.adapters.radiohall;

import android.app.Activity;
import android.support.v4.content.ContextCompat;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/28 14:16
 * @描述 评论列表adapter
 */
public class CommentsListAdapter extends BaseQuickAdapter<RaidoDetailsModel.CommentedListBean, BaseViewHolder> {

    private Activity mActivity;

    public CommentsListAdapter(Activity activity) {
        super(R.layout.adapter_comments_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, RaidoDetailsModel.CommentedListBean item) {
        ImageView iv_comments_listitem_head = helper.getView(R.id.iv_comments_listitem_head);
        helper.setText(R.id.tv_comments_listitem_name, item.getNickname());
        if (1 == item.getSex()) {
            x.image().bind(
                    iv_comments_listitem_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            helper.setImageDrawable(R.id.iv_comments_listitem_gender, ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_men_icon));
        } else {
            x.image().bind(
                    iv_comments_listitem_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );
            helper.setImageDrawable(R.id.iv_comments_listitem_gender, ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_women_icon));
        }
        helper.setText(R.id.tv_comments_listitem_content, item.getContent());
        helper.setText(R.id.tv_comments_listitem_time, item.getCreated_at());
        ImageView iv_comments_listitem_comments = helper.getView(R.id.iv_comments_listitem_comments);
        RecyclerView recycler_view_reply = helper.getView(R.id.recycler_view_reply);
        if (item.getReply() != null && item.getReply().size() > 0) {
            recycler_view_reply.setVisibility(View.VISIBLE);
            recycler_view_reply.setLayoutManager(new LinearLayoutManager(mActivity));
            ReplyListAdapter adapter = new ReplyListAdapter();
            recycler_view_reply.setAdapter(adapter);
            adapter.setNewData(item.getReply());
        } else {
            recycler_view_reply.setVisibility(View.GONE);
        }

    }
}

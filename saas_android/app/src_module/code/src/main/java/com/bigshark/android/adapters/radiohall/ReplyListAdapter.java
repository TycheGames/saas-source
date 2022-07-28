package com.bigshark.android.adapters.radiohall;

import com.bigshark.android.R;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/28 14:46
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class ReplyListAdapter extends BaseQuickAdapter<RaidoDetailsModel.CommentedListBean.ReplyBean, BaseViewHolder> {


    public ReplyListAdapter() {
        super(R.layout.adapter_reply_listitem);
    }

    @Override
    protected void convert(BaseViewHolder helper, RaidoDetailsModel.CommentedListBean.ReplyBean item) {
        helper.setText(R.id.tv_reply_listitem_name, item.getNickname() + ":");
        helper.setText(R.id.tv_reply_listitem_content, item.getContent());

    }
}

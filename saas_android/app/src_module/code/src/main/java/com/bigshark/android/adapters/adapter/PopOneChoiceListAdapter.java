package com.bigshark.android.adapters.adapter;

import com.bigshark.android.R;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 11:09
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PopOneChoiceListAdapter extends BaseQuickAdapter<String, BaseViewHolder> {

    public PopOneChoiceListAdapter() {
        super(R.layout.adapter_pop_one_choice_list);
    }

    @Override
    protected void convert(BaseViewHolder helper, String item) {
        helper.setText(R.id.tv_name, item);
    }
}

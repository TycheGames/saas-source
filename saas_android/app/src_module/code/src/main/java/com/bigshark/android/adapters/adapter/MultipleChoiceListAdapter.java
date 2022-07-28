package com.bigshark.android.adapters.adapter;

import android.widget.CheckBox;

import com.bigshark.android.R;
import com.bigshark.android.http.model.bean.MultipleChoiceTextModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 14:38
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class MultipleChoiceListAdapter extends BaseQuickAdapter<MultipleChoiceTextModel, BaseViewHolder> {

    public MultipleChoiceListAdapter() {
        super(R.layout.adapter_pop_multiple_choice_list);
    }

    @Override
    protected void convert(BaseViewHolder helper, MultipleChoiceTextModel item) {
        helper.setText(R.id.tv_choice_text, item.getText());
        CheckBox cb_choice = helper.getView(R.id.cb_choice);
        cb_choice.setChecked(item.isIs_selected());
    }
}

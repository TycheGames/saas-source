package com.bigshark.android.adapters.adapter;

import android.support.v4.content.ContextCompat;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.home.EvaluationItemModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/29 10:39
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class EvaluationTaListAdapter extends BaseQuickAdapter<EvaluationItemModel, BaseViewHolder> {

    private IDisplay display;

    public EvaluationTaListAdapter(IDisplay display) {
        super(R.layout.adapter_evaluation_ta_listitem);
        this.display = display;
    }

    @Override
    protected void convert(BaseViewHolder helper, EvaluationItemModel item) {
        TextView tv_evaluation_number = helper.getView(R.id.tv_evaluation_number);
        if (0 == item.getCount()) {
            tv_evaluation_number.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
        } else {
            tv_evaluation_number.setTextColor(ContextCompat.getColor(display.context(), R.color.color_3e3d3d));
        }
        helper.setText(R.id.tv_evaluation_number, item.getCount() + "");
        helper.setText(R.id.tv_evaluation_trait, item.getName());

    }
}

package com.bigshark.android.adapters.home;

import android.widget.CheckBox;

import com.bigshark.android.R;
import com.bigshark.android.http.model.home.UserReportOptionsModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/24 21:47
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class AnonymousReportingAdapter extends BaseQuickAdapter<UserReportOptionsModel, BaseViewHolder> {

    public AnonymousReportingAdapter() {
        super(R.layout.adapter_anonymous_reporting_listitem);
    }

    @Override
    protected void convert(BaseViewHolder helper, UserReportOptionsModel item) {
        helper.setText(R.id.tv_report_name, item.getName());
        CheckBox cb_choice = helper.getView(R.id.cb_choice);
        cb_choice.setChecked(item.isSelected());
    }

}

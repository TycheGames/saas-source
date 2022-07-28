package com.bigshark.android.adapters.adapter;

import android.support.annotation.Nullable;
import android.widget.CheckBox;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.bean.MethodPaymentModel;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/14 16:17
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PaymentMethodAdapter extends BaseQuickAdapter<MethodPaymentModel, BaseViewHolder> {

    public PaymentMethodAdapter(@Nullable List<MethodPaymentModel> data) {
        super(R.layout.adapter_payment_method_listitem, data);
    }

    @Override
    protected void convert(BaseViewHolder helper, MethodPaymentModel item) {
        helper.setText(R.id.tv_method_method_name, item.getMethodName());
        ImageView iv_method_icon = helper.getView(R.id.iv_method_icon);
        CheckBox cb_method = helper.getView(R.id.cb_method);
        cb_method.setChecked(item.isCheck());
        if (item.isClick()) {
            iv_method_icon.setImageResource(item.getCheckIcon());
        } else {
            iv_method_icon.setImageResource(item.getUncheckIcon());
        }
    }
}

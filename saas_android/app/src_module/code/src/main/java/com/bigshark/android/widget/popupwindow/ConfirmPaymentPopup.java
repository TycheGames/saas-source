package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.adapter.PaymentMethodAdapter;
import com.bigshark.android.http.model.bean.MethodPaymentModel;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;

import java.util.ArrayList;
import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/24 16:51
 * @描述 确认支付
 */
public class ConfirmPaymentPopup extends PopupWindow implements View.OnClickListener {

    private Context mContext;
    private OnConfirmClickListener mOnConfirmClickListener;
    private TextView mTv_payment_amount, tv_popup_confirm;
    private RecyclerView mRecyclerView;

    public static final String PAYTYPE_BALANCE = "1";
    public static final String PAYTYPE_ZHIFUBAO = "2";
    public static final String PAYTYPE_WEIXIN = "3";

    private int[] checkIcons = {R.mipmap.method_payment_balance_check_icon, R.mipmap.method_payment_alipay_check_icon, R.mipmap.method_payment_weixin_check_icon};
    private int[] unCheckIcons = {R.mipmap.method_payment_balance_uncheck_icon, R.mipmap.method_payment_alipay_uncheck_icon, R.mipmap.method_payment_weixin_uncheck_icon};
    private String[] methodNameStrs = {"Balance Payment", "Alipay Pay", "WeChat Pay"};
    private String[] methodTypes = {PAYTYPE_BALANCE, PAYTYPE_ZHIFUBAO, PAYTYPE_WEIXIN};
    private List<MethodPaymentModel> mMethodList;
    private String selectedMethodType;
    private String mMoney;

    public ConfirmPaymentPopup(@NonNull Context context) {
        super(context);
        this.mContext = context;
        init(context);
    }

    public ConfirmPaymentPopup(@NonNull Context context, String money) {
        super(context);
        this.mContext = context;
        this.mMoney = money;
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_confirm_payment, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        mTv_payment_amount = mPopView.findViewById(R.id.tv_payment_amount);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        mRecyclerView = mPopView.findViewById(R.id.recycler_view);
        iv_popup_close.setOnClickListener(this);
        tv_popup_confirm.setOnClickListener(this);
        initRecyclerView();
        if (mMoney != null) {
            mTv_payment_amount.setText("¥" + mMoney);
        }
    }

    private void initRecyclerView() {
        if (mMethodList == null) {
            mMethodList = new ArrayList<>();
        } else {
            mMethodList.clear();
        }
        for (int i = 0; i < methodNameStrs.length; i++) {
            MethodPaymentModel bean = new MethodPaymentModel();
            bean.setCheckIcon(checkIcons[i]);
            bean.setUncheckIcon(unCheckIcons[i]);
            bean.setMethodName(methodNameStrs[i]);
            bean.setClick(true);
            bean.setType(methodTypes[i]);
            mMethodList.add(bean);
        }
        mRecyclerView.setLayoutManager(new LinearLayoutManager(mContext));
        PaymentMethodAdapter adapter = new PaymentMethodAdapter(mMethodList);
        mRecyclerView.setAdapter(adapter);
        adapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                MethodPaymentModel itemBean = (MethodPaymentModel) adapter.getData().get(position);
                if (!itemBean.isClick()) {
                    return;
                }
                for (int i = 0; i < mMethodList.size(); i++) {
                    mMethodList.get(i).setCheck(false);
                }
                mMethodList.get(position).setCheck(true);
                selectedMethodType = mMethodList.get(position).getType();
                adapter.notifyDataSetChanged();
            }
        });
    }

    public void setContent(String amount) {
        if (mTv_payment_amount != null) {
            mTv_payment_amount.setText("¥" + amount);
        }
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            case R.id.tv_popup_confirm:
                if (StringUtil.isBlank(selectedMethodType)) {
                    ToastUtil.showToast(mContext, "Please select mode of payment");
                    return;
                }
                //确认支付
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick(selectedMethodType);
                }
                break;
            default:
                break;
        }
    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }


}

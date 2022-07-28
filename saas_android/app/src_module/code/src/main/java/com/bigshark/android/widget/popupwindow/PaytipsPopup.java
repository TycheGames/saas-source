package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.bean.PaytipsPopupModel;
import com.bigshark.android.listener.OnTwoButtonClickListener;
//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/24 16:51
 * @描述 付费提示
 */
public class PaytipsPopup extends PopupWindow implements View.OnClickListener {

    private Context mContext;
    private OnTwoButtonClickListener mOnTwoButtonClickListener;
    private TextView tv_popup_title, tv_pop_pay_content, tv_pop_pay_number, tv_pop_vip_content;
    private PaytipsPopupModel mPaytipsPopupModel;
    private ImageView iv_vip_icon;

    public PaytipsPopup(@NonNull Context context, PaytipsPopupModel paytipsPopupModel) {
        super(context);
        this.mContext = context;
        this.mPaytipsPopupModel = paytipsPopupModel;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_pay_tips, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        LinearLayout ll_pay = mPopView.findViewById(R.id.ll_pay);
        LinearLayout ll_vip = mPopView.findViewById(R.id.ll_vip);
        tv_popup_title = mPopView.findViewById(R.id.tv_popup_title);
        tv_pop_pay_content = mPopView.findViewById(R.id.tv_pop_pay_content);
        tv_pop_pay_number = mPopView.findViewById(R.id.tv_pop_pay_number);
        tv_pop_vip_content = mPopView.findViewById(R.id.tv_pop_vip_content);
        iv_vip_icon = mPopView.findViewById(R.id.iv_vip_icon);
//        if (1 == UserCenter.instance().getUserGender()) {
//            iv_vip_icon.setImageResource(R.mipmap.release_radio_dialog_vip_icon);
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            iv_vip_icon.setImageResource(R.mipmap.release_radio_dialog_certification_icon);
//        }
        iv_popup_close.setOnClickListener(this);
        ll_pay.setOnClickListener(this);
        ll_vip.setOnClickListener(this);
        setContent(mPaytipsPopupModel);
    }

    public void setContent(PaytipsPopupModel paytipsPopupModel) {
        if (paytipsPopupModel == null) {
            return;
        }
        if (tv_popup_title != null) {
            tv_popup_title.setText(paytipsPopupModel.getTitle());
        }
        if (tv_pop_pay_content != null) {
            tv_pop_pay_content.setText(paytipsPopupModel.getPay_content());
        }
        if (tv_pop_pay_number != null) {
            tv_pop_pay_number.setText(paytipsPopupModel.getPay_number());
        }
        if (tv_pop_vip_content != null) {
            tv_pop_vip_content.setText(paytipsPopupModel.getVip_content());
        }
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            case R.id.ll_pay:
                //去付费
                if (mOnTwoButtonClickListener != null) {
                    mOnTwoButtonClickListener.OnOneButtonClick();
                }
                break;
            case R.id.ll_vip:
                //去充值成为vip或者认证
                if (mOnTwoButtonClickListener != null) {
                    mOnTwoButtonClickListener.OnTwoButtonClick();
                }
                break;
            default:
                break;
        }
    }

    public void setOnTwoButtonClickListener(OnTwoButtonClickListener onTwoButtonClickListener) {
        this.mOnTwoButtonClickListener = onTwoButtonClickListener;
    }

}

package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.utils.ToastUtil;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/24 16:51
 * @描述 发送社交账号
 */
public class SendSocialAccountPopup extends PopupWindow implements View.OnClickListener {

    private Context mContext;
    private OnConfirmClickListener mOnConfirmClickListener;
    private TextView tv_popup_confirm;
    private EditText et_social_account;

    public SendSocialAccountPopup(@NonNull Context context) {
        super(context);
        this.mContext = context;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_send_social_account, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        et_social_account = mPopView.findViewById(R.id.et_social_account);

        iv_popup_close.setOnClickListener(this);
        tv_popup_confirm.setOnClickListener(this);
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            case R.id.tv_popup_confirm:
                String account = et_social_account.getText().toString().trim();
                if (account != null && account.length() > 0) {
                    //发送给他
                    if (mOnConfirmClickListener != null) {
                        mOnConfirmClickListener.OnConfirmClick(account);
                    }
                } else {
                    ToastUtil.showToast(mContext, "Please enter your account");
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

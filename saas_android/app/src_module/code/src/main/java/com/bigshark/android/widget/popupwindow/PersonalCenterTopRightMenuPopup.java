package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.listener.OnShieldingAndReportClickListener;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/31 18:01
 * @描述 用户个人中心 顶部更多 拉黑和举报
 */
public class PersonalCenterTopRightMenuPopup extends PopupWindow implements View.OnClickListener {

    private Context mContext;
    private OnShieldingAndReportClickListener mOnShieldingAndReportClickListener;

    public PersonalCenterTopRightMenuPopup(@NonNull Context context) {
        super(context);
        this.mContext = context;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_shielding_and_report, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        RelativeLayout rl_shielding = mPopView.findViewById(R.id.rl_shielding);
        TextView tv_report = mPopView.findViewById(R.id.tv_report);
        iv_popup_close.setOnClickListener(this);
        rl_shielding.setOnClickListener(this);
        tv_report.setOnClickListener(this);
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            //拉黑
            case R.id.rl_shielding:
                if (mOnShieldingAndReportClickListener != null) {
                    mOnShieldingAndReportClickListener.OnShieldingClick();
                }
                break;
            //举报
            case R.id.tv_report:
                if (mOnShieldingAndReportClickListener != null) {
                    mOnShieldingAndReportClickListener.OnReportClick();
                }
                break;
            default:
                break;
        }
    }

    public void setOnShieldingAndReportClickListener(OnShieldingAndReportClickListener onShieldingAndReportClickListener) {
        this.mOnShieldingAndReportClickListener = onShieldingAndReportClickListener;
    }
}

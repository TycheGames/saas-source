package com.bigshark.android.widget.popupwindow;

import android.app.Activity;
import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.home.TimesNoticeModel;
import com.bigshark.android.listener.OnConfirmClickListener;
//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/24 16:51
 * @描述 使用查看次数
 */
public class ViewTimesUsedPopup extends PopupWindow implements View.OnClickListener {

    private Activity mActivity;
    private OnConfirmClickListener mOnConfirmClickListener;

    private TextView tv_prompt_message, tv_default_prompt, tv_popup_close, tv_popup_confirm;

    private TimesNoticeModel mTimesNoticeModel;

    public ViewTimesUsedPopup(@NonNull Context context, TimesNoticeModel noticeBean) {
        super(context);
        this.mActivity = (Activity) context;
        this.mTimesNoticeModel = noticeBean;
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_view_times_used, null);
        //设置View
        setContentView(mPopView);
        tv_prompt_message = mPopView.findViewById(R.id.tv_prompt_message);
        tv_default_prompt = mPopView.findViewById(R.id.tv_default_prompt);
        tv_popup_close = mPopView.findViewById(R.id.tv_popup_close);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        if (mTimesNoticeModel != null) {
            tv_prompt_message.setText(mTimesNoticeModel.getMessage());
//            if (1 == UserCenter.instance().getUserGender()) {
//                tv_default_prompt.setText("非会员用户每天只能查看" + mTimesNoticeModel.getNot_vip_watch_times_limit() + "位女士");
//                tv_popup_confirm.setText("升级会员");
//            } else if (2 == UserCenter.instance().getUserGender()) {
//                tv_default_prompt.setText("未认证用户每天只能查看" + mTimesNoticeModel.getNot_vip_watch_times_limit() + "位男士");
//                tv_popup_confirm.setText("去认证");
//            }
            if (mTimesNoticeModel.isIs_show_profile()) {
                tv_popup_close.setText("Continue to view");
            } else {
                tv_popup_close.setText("Cancel");
            }
        }
        tv_popup_close.setOnClickListener(this);
        tv_popup_confirm.setOnClickListener(this);

    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.tv_popup_close:
                dismiss();
                if (mTimesNoticeModel.isIs_show_profile()) {
                } else {
                    mActivity.finish();
                }
                break;
            case R.id.tv_popup_confirm:
                dismiss();
//                if (1 == UserCenter.instance().getUserGender()) {
//                    com.bigshark.android.activities.home.BrowserActivity.goIntent(attachedActivity, MmkvGroup.global().getVipLink());
//                } else if (2 == UserCenter.instance().getUserGender()) {
//                    com.bigshark.android.activities.home.BrowserActivity.goIntent(attachedActivity, MmkvGroup.global().getAuthCenterLink());
//                }
                break;
            default:
                break;
        }
    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }

}

package com.bigshark.android.widget.popupwindow;

import android.app.Activity;
import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.mmkv.MmkvGroup;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/29 17:25
 * @描述 认证提醒pop
 */
public class CertificationRemindPopup extends PopupWindow {

    private TextView tv_popup_close, tv_popup_confirm;

    private Activity mActivity;

    public CertificationRemindPopup(@NonNull Context context) {
        super(context);
        this.mActivity = (Activity) context;
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_certification_remind, null);
        //设置View
        setContentView(mPopView);
        tv_popup_close = mPopView.findViewById(R.id.tv_popup_close);
        tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        tv_popup_close.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
            }
        });
        tv_popup_confirm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
                com.bigshark.android.activities.home.BrowserActivity.goIntent(mActivity, MmkvGroup.global().getAuthCenterLink());
            }
        });
    }

}

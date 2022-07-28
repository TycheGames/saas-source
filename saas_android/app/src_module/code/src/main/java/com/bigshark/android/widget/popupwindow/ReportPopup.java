package com.bigshark.android.widget.popupwindow;

import android.app.Activity;
import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.AnonymousReportingActivity;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 18:36
 * @描述 举报
 */
public class ReportPopup extends PopupWindow {

    private Context mContext;
    private String mRadioId;

    public ReportPopup(@NonNull Context context, String radioId) {
        super(context);
        mContext = context;
        mRadioId = radioId;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_report, null);
        //设置View
        setContentView(mPopView);
        //设置View
        setContentView(mPopView);
        TextView tv_report = mPopView.findViewById(R.id.tv_report);
        tv_report.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
                AnonymousReportingActivity.openIntent((Activity) mContext, 2, "", mRadioId);
            }
        });

    }
}

package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.text.Html;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.listener.OnConfirmClickListener;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/10 14:57
 * @描述 居中的弹窗
 */
public class CommonOnePopup extends PopupWindow {

    private String mTitle;
    private String mContent;
    private String mConfirmStr;
    private OnConfirmClickListener mOnConfirmClickListener;

    public CommonOnePopup(@NonNull Context context, String content) {
        super(context);
        this.mTitle = "Tips";
        this.mContent = content;
        this.mConfirmStr = "Confirm";
        init(context);
    }

    public CommonOnePopup(@NonNull Context context, String content, String confirmStr) {
        super(context);
        this.mTitle = "Tips";
        this.mContent = content;
        this.mConfirmStr = confirmStr;
        init(context);
    }

    public CommonOnePopup(@NonNull Context context, String title, String content, String confirmStr) {
        super(context);
        this.mTitle = title;
        this.mContent = content;
        this.mConfirmStr = confirmStr;
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_common_one, null);
        //设置View
        setContentView(mPopView);
        TextView tv_popup_one_title = mPopView.findViewById(R.id.tv_popup_one_title);
        ImageView iv_popup_one_close = mPopView.findViewById(R.id.iv_popup_one_close);
        TextView tv_popup_one_content = mPopView.findViewById(R.id.tv_popup_one_content);
        TextView tv_popup_one_confirm = mPopView.findViewById(R.id.tv_popup_one_confirm);
        if (mTitle != null) {
            tv_popup_one_title.setText(Html.fromHtml(mTitle));
        }
        if (mContent != null) {
            tv_popup_one_content.setText(Html.fromHtml(mContent));
        }
        if (mConfirmStr != null) {
            tv_popup_one_confirm.setText(Html.fromHtml(mConfirmStr));
        }
        iv_popup_one_close.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
            }
        });
        tv_popup_one_confirm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick(null);
                }
            }
        });
    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        this.mOnConfirmClickListener = onConfirmClickListener;
    }
}

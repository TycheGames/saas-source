package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
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
 * @描述 性别选择提示
 */
public class SexSelectionHintPopup extends PopupWindow {

    private OnConfirmClickListener mOnConfirmClickListener;

    public SexSelectionHintPopup(@NonNull Context context) {
        super(context);
        init(context);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_sex_selection_hint, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_one_close = mPopView.findViewById(R.id.iv_popup_one_close);
        TextView tv_popup_one_confirm = mPopView.findViewById(R.id.tv_popup_one_confirm);
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
        mOnConfirmClickListener = onConfirmClickListener;
    }
}

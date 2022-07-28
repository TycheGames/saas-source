package com.bigshark.android.widget.popupwindow;

import android.graphics.drawable.ColorDrawable;
import android.view.ViewGroup;
import android.widget.PopupWindow;

public class PopupWindowUtil {
    public static void setPopupWindow(PopupWindow window) {
        window.setWidth(ViewGroup.LayoutParams.WRAP_CONTENT);// 设置弹出窗口的宽
        window.setHeight(ViewGroup.LayoutParams.WRAP_CONTENT);// 设置弹出窗口的高
        window.setFocusable(true);
        window.setBackgroundDrawable(new ColorDrawable(0x00000000));// 设置背景透明
        window.setOutsideTouchable(true);
    }
}

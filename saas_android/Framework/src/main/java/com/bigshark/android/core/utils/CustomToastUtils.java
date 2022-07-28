package com.bigshark.android.core.utils;

import android.content.Context;
import android.view.Gravity;
import android.widget.Toast;


/**
 * Created by User on 2018/7/17.
 */
public class CustomToastUtils {

    public static void showToast(Context ctx, String content, boolean isCenter) {
        Toast toast = Toast.makeText(ctx, content, Toast.LENGTH_LONG);
        // 设置toast显示的位置，这是居中
        if (isCenter) {
            toast.setGravity(Gravity.CENTER, 0, 0);
        } else {
            toast.setGravity(Gravity.CENTER | Gravity.BOTTOM, 0, 300);
        }
        toast.show();
    }

    public void showToast(Context ctx, int stringId) {
        showToast(ctx, ctx.getString(stringId), false);
    }

}


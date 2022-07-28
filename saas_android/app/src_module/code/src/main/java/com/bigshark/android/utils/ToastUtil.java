package com.bigshark.android.utils;

import android.content.Context;
import android.os.Handler;
import android.widget.Toast;

public class ToastUtil {

    private static Toast    mToast;
    private static Handler  mHandler      = new Handler();
    private static Runnable toastRunnable = new Runnable() {
        public void run() {
            mToast.cancel();
            mToast = null;//toast隐藏后，将其置为null
        }
    };

    /**
     * 在底部显示提示信息
     *
     * @param msg 需要显示的信息，如果为空的话，
     *            那么显示"网络出错，请稍后再试"
     */
    public static void showToast(Context context, String msg) {
        cancelToast();
        //只有mToast==null时才重新创建，否则只需更改提示文字
        if (mToast == null) {
            mToast = Toast.makeText(context, msg, Toast.LENGTH_SHORT);
        }

        mHandler.postDelayed(toastRunnable, 1000);//延迟1秒隐藏toast
        mToast.show();
    }

    public static void cancelToast() {
        mHandler.removeCallbacks(toastRunnable);
        if (mToast != null) {
            mToast.cancel();
            mToast = null;
        }
    }
}

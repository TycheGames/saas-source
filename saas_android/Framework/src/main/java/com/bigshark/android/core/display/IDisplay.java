package com.bigshark.android.core.display;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.os.Handler;
import android.support.annotation.StringRes;

/**
 * 页面接口
 */
public interface IDisplay {

    /**
     * 获取当前的Page
     */
    IDisplay display();

    Context context();

    Activity act();


    void startActivity(Intent intent);

    void startActivityForResult(Intent intent, int requestCode);

    ComponentName startService(Intent service);

    Intent getIntent();


    Handler getMainHandler();


    String getString(@StringRes int stringResourceId);

    void showToast(@StringRes int messageResId);

    void showToast(String message);

    void showTipDialog(String message);


    /**
     * fragment的页面
     */
    interface FDisplay extends IDisplay {
        void onShow();
    }


    /**
     * Activity的页面
     */
    interface ADisplay extends IDisplay {
    }


}

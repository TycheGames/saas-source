package com.bigshark.android.core.utils;

import android.app.Activity;
import android.app.AlertDialog;
import android.view.Window;
import android.widget.TextView;

import com.bigshark.android.core.R;

public class LoadingDialogUtils {


    public static void showLoadingDialog(Activity activity) {
        showLoadingDialogWithContent(activity, "");
    }

    public static void showLoadingDialogWithContent(Activity activity, String content) {
        showLoadingDialog(activity, content);
    }


    //自定义内容加载提示窗
    private static AlertDialog loadingDialog;

    private static void showLoadingDialog(Activity activity, String content) {
        hideLoadingDialog();
        loadingDialog = new AlertDialog.Builder(activity, R.style.app_framework_alert_dialog).create();
        loadingDialog.setCancelable(false);
        if (!activity.isFinishing()) {
            loadingDialog.show();
            Window window = loadingDialog.getWindow();
            window.setContentView(R.layout.core_dialog_loading);
            TextView contentView = window.findViewById(R.id.app_framework_dialog_loading_content);
            contentView.setText(content);
        }
    }


    /*******
     * 关闭loading
     */
    public static void hideLoadingDialog() {
        if (loadingDialog == null) {
            return;
        }

        if (loadingDialog.isShowing()) {
            try {
                loadingDialog.dismiss();
            } catch (Exception e) {
                e.printStackTrace();
            }
        }
        loadingDialog = null;
    }
}

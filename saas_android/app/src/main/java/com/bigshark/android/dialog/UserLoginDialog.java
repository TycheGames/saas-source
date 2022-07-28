package com.bigshark.android.dialog;

import android.app.Activity;
import android.app.Dialog;
import android.content.Context;
import android.content.DialogInterface;
import android.support.annotation.NonNull;
import android.view.Display;
import android.view.LayoutInflater;
import android.view.View;
import android.view.View.OnClickListener;
import android.view.WindowManager;
import android.widget.FrameLayout;
import android.widget.LinearLayout;
import android.widget.LinearLayout.LayoutParams;
import android.widget.TextView;

import com.bigshark.android.R;

/**
 * 登录态失效，重新登录
 */
public class UserLoginDialog {
    private final Activity mActivity;
    private final Callback mCallback;

    private Dialog dialog;
    private TextView cancelText;
    private TextView okText;

    public UserLoginDialog(Activity activity, @NonNull Callback callback) {
        this.mActivity = activity;
        this.mCallback = callback;

        // 获取Dialog布局
        View view = LayoutInflater.from(mActivity).inflate(R.layout.dialog_global_user_login, null);

        // 获取自定义Dialog布局中的控件
        LinearLayout root = view.findViewById(R.id.login_dialog_root);
        cancelText = view.findViewById(R.id.dialog_cancle_text);
        okText = view.findViewById(R.id.dialog_ok_text);

        // 定义Dialog布局和参数
        dialog = new Dialog(mActivity, R.style.AlertDialogStyle);
        dialog.setContentView(view);
        dialog.setCancelable(true);
        dialog.setOnCancelListener(new DialogInterface.OnCancelListener() {
            @Override
            public void onCancel(DialogInterface dialog) {
                mCallback.onCancelClick();
            }
        });

        // 调整dialog背景大小
        WindowManager windowManager = (WindowManager) mActivity.getSystemService(Context.WINDOW_SERVICE);
        if (windowManager != null) {
            Display display = windowManager.getDefaultDisplay();
            root.setLayoutParams(new FrameLayout.LayoutParams((int) (display.getWidth() * 0.80), LayoutParams.WRAP_CONTENT));
        }

        // bindListeners
        cancelText.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                dialog.dismiss();
                mCallback.onCancelClick();
            }
        });
        okText.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                dialog.dismiss();
                mCallback.onOkClick();
            }
        });
    }


    public void show() {
        if (dialog != null && !dialog.isShowing()) {
            dialog.show();
        }
    }

    public boolean isShowing() {
        return dialog != null && dialog.isShowing();
    }


    public interface Callback {
        void onOkClick();

        void onCancelClick();
    }
}

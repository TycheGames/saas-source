package com.bigshark.android.dialog;

import android.app.Dialog;
import android.content.Context;
import android.text.Html;
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
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.StringUtil;


public class CommonJumpDialog {
    private IDisplay display;
    private Dialog dialog;
    private TextView titleText;
    private TextView contentText;
    private TextView cancelBtn;
    private TextView okBtn;

    private boolean isBtnClose;

    public CommonJumpDialog(IDisplay display, boolean isBtnClose) {
        this.display = display;
        this.isBtnClose = !isBtnClose;
        builder();
    }

    private void builder() {
        // 获取Dialog布局
        View view = LayoutInflater.from(display.act()).inflate(R.layout.dialog_global_jump, null);

        // 获取自定义Dialog布局中的控件
        LinearLayout root = view.findViewById(R.id.dialog_root);
        titleText = view.findViewById(R.id.dialog_title_text);
        contentText = view.findViewById(R.id.dialog_content_text);
        cancelBtn = view.findViewById(R.id.dialog_cancle_text);
        okBtn = view.findViewById(R.id.dialog_ok_text);

        // 定义Dialog布局和参数
        dialog = new Dialog(display.act(), R.style.AlertDialogStyle);
        dialog.setContentView(view);

        // 调整dialog背景大小
        WindowManager windowManager = (WindowManager) display.act().getSystemService(Context.WINDOW_SERVICE);
        if (windowManager != null) {
            Display display = windowManager.getDefaultDisplay();
            root.setLayoutParams(new FrameLayout.LayoutParams((int) (display.getWidth() * 0.80), LayoutParams.WRAP_CONTENT));
        }

        dialog.setCancelable(isBtnClose);
    }

    public CommonJumpDialog setTitle(String title) {
        if (StringUtil.isBlank(title)) {
            titleText.setVisibility(View.GONE);
        } else {
            titleText.setVisibility(View.VISIBLE);
            titleText.setText(Html.fromHtml(title));
        }
        return this;
    }

    public CommonJumpDialog setContent(String content) {
        if (StringUtil.isBlank(content)) {
            contentText.setVisibility(View.GONE);
        } else {
            contentText.setVisibility(View.VISIBLE);
            contentText.setText(Html.fromHtml(content));
        }
        return this;
    }

    public CommonJumpDialog setCancleText(String cancle) {
        if (StringUtil.isBlank(cancle)) {
            cancelBtn.setVisibility(View.GONE);
        } else {
            cancelBtn.setVisibility(View.VISIBLE);
            cancelBtn.setText(Html.fromHtml(cancle));
        }
        return this;
    }

    public CommonJumpDialog setOkText(String ok) {
        if (StringUtil.isBlank(ok)) {
            okBtn.setVisibility(View.GONE);
        } else {
            okBtn.setVisibility(View.VISIBLE);
            okBtn.setText(Html.fromHtml(ok));
        }
        return this;
    }

    public CommonJumpDialog setCancleBtnClick(final OnClickListener listener) {
        cancelBtn.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                dialog.dismiss();
                if (listener != null) {
                    listener.onClick(v);
                }
            }
        });
        return this;
    }


    public CommonJumpDialog setOkClick(final OnClickListener listener) {
        okBtn.setOnClickListener(new OnClickListener() {
            @Override
            public void onClick(View v) {
                dialog.dismiss();
                if (listener != null) {
                    listener.onClick(v);
                }
            }
        });
        return this;
    }

    public void show() {
        if (dialog != null && !dialog.isShowing()) {
            dialog.show();
        }
    }
}

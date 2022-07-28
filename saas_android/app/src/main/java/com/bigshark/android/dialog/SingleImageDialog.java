package com.bigshark.android.dialog;

import android.support.v7.app.AlertDialog;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.jump.model.uis.ImageDialogJumpModel;

import com.bigshark.android.core.display.IDisplay;

import org.xutils.common.util.DensityUtil;
import org.xutils.image.ImageOptions;
import org.xutils.x;


/**
 * 请求权限
 * 提前请求权限
 * Created by ytxu on 2019/9/10.
 */
public class SingleImageDialog {

    private final IDisplay display;
    private final ImageDialogJumpModel.ContentParam displayData;

    private AlertDialog alertDialog;
    private ImageView contentImage;
    private ImageView closeImage;


    public SingleImageDialog(IDisplay display, ImageDialogJumpModel.ContentParam displayData) {
        this.display = display;
        this.displayData = displayData;
    }

    public void start() {
        View view = LayoutInflater.from(display.act()).inflate(R.layout.dialog_global_jump_image, null);
        contentImage = view.findViewById(R.id.dialog_content_image);
        closeImage = view.findViewById(R.id.dialog_close_image);
        addListener();
        initViews();

        alertDialog = new AlertDialog.Builder(display.act(), R.style.Dialog_NoTitle)
                .setView(view)
                .setCancelable(false)
                .show();

        alertDialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT);
    }

    private void addListener() {
        contentImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                JumpOperationHandler.convert(displayData.getJump()).createRequest().setDisplay(display).jump();

                if (displayData.isCloseDialogAfterClicked()) {
                    closeAlertDialog();
                }
            }
        });
        closeImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                closeAlertDialog();
            }
        });
    }

    private void closeAlertDialog() {
        if (alertDialog != null && alertDialog.isShowing()) {
            alertDialog.dismiss();
        }
        alertDialog = null;
    }

    private void initViews() {
        x.image().bind(
                contentImage,
                displayData.getImageUrl(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setRadius(DensityUtil.dip2px(10))
                        .build()
        );
        closeImage.setVisibility(displayData.isShowCloseView() ? View.VISIBLE : View.GONE);
    }

}

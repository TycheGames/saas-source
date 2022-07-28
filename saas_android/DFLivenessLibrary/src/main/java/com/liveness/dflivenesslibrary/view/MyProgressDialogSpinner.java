package com.liveness.dflivenesslibrary.view;

import android.app.Activity;
import android.app.Dialog;
import android.content.DialogInterface;
import android.view.View;
import android.view.ViewGroup;
import android.view.WindowManager;
import android.view.animation.Animation;
import android.view.animation.LinearInterpolator;
import android.view.animation.RotateAnimation;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.liveness.util.LivenessUtils;


/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/

public class MyProgressDialogSpinner {

    protected Dialog mDialog;
    private ImageView mProgressView;
    private View mView;
    RotateAnimation mRotateAnimation;
    private Activity mActivity;

    public MyProgressDialogSpinner(Activity activity, DialogInterface.OnDismissListener callback) {
        mView = activity.getLayoutInflater().inflate(R.layout.layout_progress_dialog_spinner, null);
        mActivity = activity;

        mProgressView = mView.findViewById(R.id.id_iv_progress_spinner);
        mRotateAnimation = new RotateAnimation(0f, 359, Animation.RELATIVE_TO_SELF, 0.5f, Animation.RELATIVE_TO_SELF, 0.5f);
        mRotateAnimation.setDuration(700);
        mRotateAnimation.setInterpolator(new LinearInterpolator());
        mRotateAnimation.setRepeatCount(Animation.INFINITE);
        mProgressView.startAnimation(mRotateAnimation);
        initDialog();
        mDialog.setOnDismissListener(callback);
    }

    private void initDialog() {
        mDialog = new Dialog(mActivity, R.style.dialog_style);
        mDialog.getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);

        mDialog.setContentView(mView, new LinearLayout.LayoutParams(ViewGroup.LayoutParams.MATCH_PARENT, ViewGroup.LayoutParams.MATCH_PARENT));
        int[] screenSize = LivenessUtils.getScreenSize(mActivity);
        int layoutWidth = screenSize[0] * 2 / 3;
        int layoutHeight = screenSize[1] * 2 / 3;
        mDialog.getWindow().setLayout(ViewGroup.LayoutParams.MATCH_PARENT, layoutHeight);

        mDialog.setCanceledOnTouchOutside(false);
        mDialog.setCancelable(false);
        mDialog.setOnShowListener(new DialogInterface.OnShowListener() {
            @Override
            public void onShow(DialogInterface dialog) {
                mProgressView.startAnimation(mRotateAnimation);
            }
        });
    }


    public void show() {
        mDialog.show();
    }

    public void dismiss() {
        if (mDialog.isShowing()) {
            mDialog.dismiss();
        }
    }

    public boolean isShowing() {
        return mDialog.isShowing();
    }
}

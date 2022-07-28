package com.liveness.dflivenesslibrary.view;

import android.app.Dialog;
import android.app.DialogFragment;
import android.graphics.Color;
import android.graphics.drawable.ColorDrawable;
import android.os.Build;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.WindowManager;
import android.view.animation.Animation;
import android.view.animation.LinearInterpolator;
import android.view.animation.RotateAnimation;
import android.widget.ImageView;

import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.utils.DFViewShowUtils;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved.
 */

public class DFLivenessLoadingDialogFragment extends DialogFragment {

    public static DFLivenessLoadingDialogFragment getInstance() {
        DFLivenessLoadingDialogFragment fragment = new DFLivenessLoadingDialogFragment();
        return fragment;
    }

    @Override
    public void onStart() {
        super.onStart();
        setLayoutSize();
    }

    private void setLayoutSize() {
        int[] screenSize = DFViewShowUtils.getScreenSize(getActivity());
        int screenWidth = screenSize[0];
        int screenHeight = screenSize[1];
        int viewWidth = screenWidth;
        int viewHeight = screenHeight;
        Dialog dialog = getDialog();
        if (dialog != null) {
            final Window window = dialog.getWindow();
            window.setLayout(viewWidth, viewHeight);//
            window.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.liveness_transparent)));
        }
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.liveness_fragment_loading, null);
        setStyle(DialogFragment.STYLE_NORMAL, R.style.LivenessLoadingDialog);
        setTranslucentStatus();
        return view;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initView(view);
    }

    private void initView(View view) {
        setCancelable(false);

        ImageView progressView = view.findViewById(R.id.id_iv_progress_spinner);
        RotateAnimation rotateAnimation = new RotateAnimation(0f, 359, Animation.RELATIVE_TO_SELF, 0.5f, Animation.RELATIVE_TO_SELF, 0.5f);
        rotateAnimation.setDuration(700);
        rotateAnimation.setInterpolator(new LinearInterpolator());
        rotateAnimation.setRepeatCount(Animation.INFINITE);
        progressView.startAnimation(rotateAnimation);
    }

    private void setTranslucentStatus() {
        Dialog dialog = getDialog();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {//5.0 全透明实现
            if (dialog != null) {
                Window window = dialog.getWindow();
                window.getDecorView().setSystemUiVisibility(View.SYSTEM_UI_FLAG_LAYOUT_FULLSCREEN
                        | View.SYSTEM_UI_FLAG_LAYOUT_STABLE);
                window.addFlags(WindowManager.LayoutParams.FLAG_DRAWS_SYSTEM_BAR_BACKGROUNDS);
                window.setStatusBarColor(Color.TRANSPARENT);
            }
        } else {//4.4 全透明状态栏
            if (dialog != null) {
                dialog.getWindow().addFlags(WindowManager.LayoutParams.FLAG_TRANSLUCENT_STATUS);
            }
        }
    }

}

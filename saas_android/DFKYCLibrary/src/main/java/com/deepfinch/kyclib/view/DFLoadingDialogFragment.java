package com.deepfinch.kyclib.view;

import android.app.Dialog;
import android.app.DialogFragment;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.view.animation.Animation;
import android.view.animation.LinearInterpolator;
import android.view.animation.RotateAnimation;
import android.widget.ImageView;

import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.R;
import com.deepfinch.kyclib.utils.KYCStatusBarCompat;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved.
 */

public class DFLoadingDialogFragment extends DialogFragment {
    private static final String TAG = "DFLoadingDialogFragment";

    public static DFLoadingDialogFragment getInstance() {
        DFLoadingDialogFragment fragment = new DFLoadingDialogFragment();
        return fragment;
    }

    @Override
    public void onStart() {
        super.onStart();
        setLayoutSize();
    }

    private void setLayoutSize() {
        int[] screenSize = DFKYCUtils.getScreenSize(getActivity());
        int screenWidth = screenSize[0];
        int screenHeight = screenSize[1];
        int viewWidth = screenWidth;
        int viewHeight = screenHeight;

        DFKYCUtils.logI(TAG, "viewWidth", viewWidth, "viewHeight", viewHeight);

        Dialog dialog = getDialog();
        if (dialog != null) {
            final Window window = dialog.getWindow();
            window.setLayout(viewWidth, viewHeight);//
            window.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.kyc_transparent)));
        }
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.kyc_fragment_loading, null);
        setStyle(DialogFragment.STYLE_NORMAL, R.style.KYCLoadingDialog);
        Dialog dialog = getDialog();
        if (dialog != null) {
            KYCStatusBarCompat.translucentStatusBar(dialog.getWindow(), false);
        }
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

    public boolean isShowing() {
        Dialog dialog = getDialog();
        boolean isShowing = false;
        if (dialog != null) {
            isShowing = dialog.isShowing();
        }
        return isShowing;
    }

}

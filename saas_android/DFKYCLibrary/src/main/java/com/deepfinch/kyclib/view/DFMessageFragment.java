package com.deepfinch.kyclib.view;

import android.app.DialogFragment;
import android.app.FragmentManager;
import android.graphics.drawable.ColorDrawable;
import android.os.Bundle;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.text.TextUtils;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.TextView;

import com.deepfinch.kyclib.utils.DFKYCUtils;
import com.deepfinch.kyclib.R;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved.
 */

public class DFMessageFragment extends DialogFragment {
    private static final String KEY_MESSAGE_FRAGMENT_MODEL = "key_message_fragment_model";

    private Handler mHandler;

    public static DFMessageFragment getInstance(DFMessageFragmentModel messageFragmentModel) {
        DFMessageFragment fragment = new DFMessageFragment();
        if (messageFragmentModel != null) {
            Bundle bundle = new Bundle();
            bundle.putSerializable(KEY_MESSAGE_FRAGMENT_MODEL, messageFragmentModel);
            fragment.setArguments(bundle);
        }
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

        final Window window = getDialog().getWindow();
        window.setLayout(viewWidth, viewHeight);//
        window.setBackgroundDrawable(new ColorDrawable(getResources().getColor(R.color.kyc_transparent)));
    }

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        View view = inflater.inflate(R.layout.kyc_fragment_message, null);
        setStyle(DialogFragment.STYLE_NORMAL, R.style.KYCLoadingDialog);
        return view;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initView(view);
    }

    private void initView(View view) {
        setCancelable(false);


        TextView tvHintTitle = view.findViewById(R.id.id_tv_hint_title);
        TextView tvHintContent = view.findViewById(R.id.id_tv_hint_content);
        DFMessageFragmentModel messageModel = getMessageModel();
        int showTime = 2;
        if (messageModel != null) {
            DFKYCUtils.refreshText(tvHintTitle, messageModel.getHintTitle());
            DFKYCUtils.refreshText(tvHintContent, messageModel.getHintContent());
            DFKYCUtils.refreshVisibilit(tvHintContent, !TextUtils.isEmpty(messageModel.getHintContent()));
            int inputShowTime = messageModel.getShowTime();
            if (inputShowTime > 0) {
                showTime = inputShowTime;
            }
        }
    }

    public void showMessage(FragmentManager fragmentManager) {
        show(fragmentManager, "DFMessageFragment");
        DFMessageFragmentModel messageModel = getMessageModel();
        int showTime = 2;
        if (messageModel != null) {
            int inputShowTime = messageModel.getShowTime();
            if (inputShowTime > 0) {
                showTime = inputShowTime;
            }
        }
        if (mHandler == null) {
            mHandler = new Handler();
        }
        mHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                dismissAllowingStateLoss();
            }
        }, showTime * 1000);
    }

    private DFMessageFragmentModel getMessageModel() {
        DFMessageFragmentModel messageModel = null;
        Bundle arguments = getArguments();
        if (arguments != null) {
            messageModel = (DFMessageFragmentModel) arguments.getSerializable(KEY_MESSAGE_FRAGMENT_MODEL);
        }
        return messageModel;
    }

}

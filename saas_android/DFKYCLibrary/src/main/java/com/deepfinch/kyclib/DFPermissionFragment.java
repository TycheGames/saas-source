package com.deepfinch.kyclib;

import android.os.Bundle;
import android.support.annotation.Nullable;
import android.view.View;
import android.widget.TextView;

import com.deepfinch.kyclib.base.DFKYCBaseFragment;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFPermissionFragment extends DFKYCBaseFragment {

    public static DFPermissionFragment getInstance() {
        DFPermissionFragment fragment = new DFPermissionFragment();
        return fragment;
    }

    @Override
    protected int getRooterLayoutRes() {
        return R.layout.kyc_fragment_permission;
    }

    @Override
    public void onViewCreated(View view, @Nullable Bundle savedInstanceState) {
        super.onViewCreated(view, savedInstanceState);
        initView();
    }

    private void initView(){
        TextView btnYes = findViewById(R.id.id_btn_yes);
        TextView btnNo = findViewById(R.id.id_btn_no);

        btnYes.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                requestResult(true);
            }
        });

        btnNo.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                requestResult(false);
            }
        });
    }

    private void requestResult(boolean success){
        if (mKYCProcessListener != null){
            DFProcessStepModel processStepModel = getProcessDataModel();
            processStepModel.setRequestPermissionSuccess(success);
            mKYCProcessListener.callbackResult(processStepModel);
        }
    }
}

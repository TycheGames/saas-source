package com.deepfinch.kyclib.presenter;

import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCOTPPresenter extends DFKYCDealNextPresenter {
    @Override
    public void dealProcess(DFKYCSDKPresenter kycSDKPresenter, DFProcessStepModel processDataModel) {
        String otp = processDataModel.getOtp();
        String password = processDataModel.getPassword();
        kycSDKPresenter.getUIDResult(otp, password);
    }
}

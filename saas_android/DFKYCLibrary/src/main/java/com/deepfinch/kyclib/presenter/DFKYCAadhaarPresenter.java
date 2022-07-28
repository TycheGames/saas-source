package com.deepfinch.kyclib.presenter;

import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCAadhaarPresenter extends DFKYCDealNextPresenter {
    @Override
    public void dealProcess(DFKYCSDKPresenter kycSDKPresenter, DFProcessStepModel processDataModel) {
        String aadhaarNumber = processDataModel.getAadhaarNumber();
        String captcha = processDataModel.getCaptcha();
        kycSDKPresenter.sendUID(aadhaarNumber, captcha);
    }
}

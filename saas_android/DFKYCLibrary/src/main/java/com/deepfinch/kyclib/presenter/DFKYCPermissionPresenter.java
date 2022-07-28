package com.deepfinch.kyclib.presenter;

import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCPermissionPresenter extends DFKYCDealNextPresenter {
    @Override
    public void dealProcess(DFKYCSDKPresenter kycSDKPresenter, DFProcessStepModel processDataModel) {
        boolean requestPermissionSuccess = processDataModel.isRequestPermissionSuccess();
        kycSDKPresenter.requestPermission(requestPermissionSuccess);
    }
}

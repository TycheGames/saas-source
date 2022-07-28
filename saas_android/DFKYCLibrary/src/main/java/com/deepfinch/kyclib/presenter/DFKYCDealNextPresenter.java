package com.deepfinch.kyclib.presenter;

import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public abstract class DFKYCDealNextPresenter {
    public abstract void dealProcess(DFKYCSDKPresenter kycSDKPresenter, DFProcessStepModel processDataModel);
}

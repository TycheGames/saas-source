package com.deepfinch.kyclib.presenter.model;

import android.app.Fragment;

import com.deepfinch.kyclib.listener.DFFragmentArgumentListener;
import com.deepfinch.kyclib.presenter.DFKYCDealNextPresenter;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFProcessStep {
    private Fragment showFragment;
    private DFProcessStep nextProcessModel;
    private DFProcessStep previousProcessModel;
    private DFProcessStep errorProcessModel;
    private DFFragmentArgumentListener argumentListener;
    private DFKYCDealNextPresenter kycDealNextPresenter;

    public Fragment getShowFragment() {
        return showFragment;
    }

    public void setShowFragment(Fragment showFragment) {
        this.showFragment = showFragment;
    }

    public DFProcessStep getNextProcessModel() {
        return nextProcessModel;
    }

    public void setNextProcessModel(DFProcessStep nextProcessModel) {
        this.nextProcessModel = nextProcessModel;
    }

    public void setArgumentListener(DFFragmentArgumentListener argumentListener) {
        this.argumentListener = argumentListener;
    }

    public void setFragmentArguments(DFProcessStepModel processDataModel){
        if (argumentListener != null){
            argumentListener.setInputArguments(processDataModel);
        }
    }

    public DFProcessStep getErrorProcessModel() {
        return errorProcessModel;
    }

    public void setErrorProcessModel(DFProcessStep errorProcessModel) {
        this.errorProcessModel = errorProcessModel;
    }

    public DFProcessStep getPreviousProcessModel() {
        return previousProcessModel;
    }

    public void setPreviousProcessModel(DFProcessStep previousProcessModel) {
        this.previousProcessModel = previousProcessModel;
    }

    public DFKYCDealNextPresenter getKycDealNextPresenter() {
        return kycDealNextPresenter;
    }

    public void setKycDealNextPresenter(DFKYCDealNextPresenter kycDealNextPresenter) {
        this.kycDealNextPresenter = kycDealNextPresenter;
    }
}

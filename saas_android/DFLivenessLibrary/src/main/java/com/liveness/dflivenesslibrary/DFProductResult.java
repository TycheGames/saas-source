package com.liveness.dflivenesslibrary;

import com.deepfinch.liveness.DFLivenessSDK;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class DFProductResult {
    private byte[] mLivenessEncryptResult;
    private DFLivenessSDK.DFLivenessImageResult[] mLivenessImageResults;
    private boolean mAntiHackPass;
    private String mErrorMessage;

    public DFProductResult() {

    }

    public byte[] getLivenessEncryptResult() {
        return mLivenessEncryptResult;
    }

    public void setLivenessEncryptResult(byte[] livenessResult) {
        this.mLivenessEncryptResult = livenessResult;
    }

    public DFLivenessSDK.DFLivenessImageResult[] getLivenessImageResults() {
        return mLivenessImageResults;
    }

    public void setLivenessImageResults(DFLivenessSDK.DFLivenessImageResult[] imageResults) {
        this.mLivenessImageResults = imageResults;
    }


    public boolean isAntiHackPass() {
        return mAntiHackPass;
    }

    public void setAntiHackPass(boolean antiHackPass) {
        this.mAntiHackPass = antiHackPass;
    }

    public String getErrorMessage() {
        return mErrorMessage;
    }

    public void setErrorMessage(String errorMessage) {
        this.mErrorMessage = errorMessage;
    }
}

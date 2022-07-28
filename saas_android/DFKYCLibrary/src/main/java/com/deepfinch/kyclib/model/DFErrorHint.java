package com.deepfinch.kyclib.model;

import com.deepfinch.kyclib.R;
import com.deepfinch.kyc.DFKYCSDK;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public enum DFErrorHint {
    DF_ERROR_OK(DFKYCSDK.OK, R.string.kyc_error_ok),
    DF_ERROR_CONNECT_ERROR(DFKYCSDK.ERROR_CODE_COOKIE_ERROR, R.string.kyc_error_connect),
    DF_ERROR_LICENSE_EXPIRE(DFKYCSDK.ERROR_CODE_LICENSE_EXPIRE, R.string.kyc_error_license_expire),
    DF_ERROR_LICENSE_INVALID_BUNDLE(DFKYCSDK.ERROR_CODE_LICENSE_INVALID_BUNDLE, R.string.kyc_error_license_invalid_bundle),
    DF_ERROR_LICENSE_INVALID_API_ID(DFKYCSDK.ERROR_CODE_INVALID_API_ID, R.string.kyc_error_license_invalid_api_id);
    private int errorCode;
    private int errorStrResId;

    DFErrorHint(int errorCode, int errorStrResId) {
        this.errorCode = errorCode;
        this.errorStrResId = errorStrResId;
    }

    public int getErrorCode() {
        return errorCode;
    }

    public void setErrorCode(int errorCode) {
        this.errorCode = errorCode;
    }

    public int getErrorStrResId() {
        return errorStrResId;
    }

    public void setErrorStrResId(int errorStrResId) {
        this.errorStrResId = errorStrResId;
    }

    public static DFErrorHint getErrorHintByCode(int errorCode) {
        DFErrorHint searchResult = null;
        for (DFErrorHint errorHint : DFErrorHint.values()) {
            if (errorHint.getErrorCode() == errorCode) {
                searchResult = errorHint;
                break;
            }
        }
        return searchResult;
    }
}

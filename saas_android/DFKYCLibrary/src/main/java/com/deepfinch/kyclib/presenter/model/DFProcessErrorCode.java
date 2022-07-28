package com.deepfinch.kyclib.presenter.model;

import com.deepfinch.kyc.DFKYCSDK;
import com.deepfinch.kyclib.R;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public enum DFProcessErrorCode {
    ERROR_CODE_NETWORK_CONNECT_FAIL(DFKYCSDK.ERROR_CODE_COOKIE_ERROR, R.string.kyc_error_title_network_connect_fail, R.string.kyc_error_content_network_connect_fail),
    ERROR_CODE_UID(DFKYCSDK.ERROR_CODE_INVALID_UID, R.string.kyc_aadhaar_number_error, -1),
    ERROR_CODE_VID(DFKYCSDK.ERROR_CODE_INVALID_VID, R.string.kyc_aadhaar_number_error, -1),
    ERROR_CODE_CAPTCHA(DFKYCSDK.ERROR_CODE_CAPTCHA_RECOGNIZE_ERROR, R.string.kyc_error_title_network_connect_fail, R.string.kyc_error_content_network_connect_fail),
    ERROR_CODE_INVALID_CAPTCHA(DFKYCSDK.ERROR_CODE_INVALID_CAPTCHA, R.string.kyc_validation_error, R.string.kyc_validation_error_content),
    ERROR_CODE_INVALID_MOBILE(DFKYCSDK.ERROR_CODE_INVALID_MOBILE, R.string.kyc_error_title_invalid_mobile, R.string.kyc_error_content_invalid_mobile),
    ERROR_CODE_INVALID_OTP(DFKYCSDK.ERROR_CODE_INVALID_OTP, R.string.kyc_opt_error, R.string.kyc_opt_error_content),
    ERROR_CODE_LICENSE_INVALID_BUNDLE(DFKYCSDK.ERROR_CODE_LICENSE_INVALID_BUNDLE, R.string.kyc_error_title_license_error, -1),
    ERROR_CODE_LICENSE_EXPIRE(DFKYCSDK.ERROR_CODE_LICENSE_EXPIRE, R.string.kyc_error_title_license_expire, -1),
    ERROR_CODE_API_ID(DFKYCSDK.ERROR_CODE_INVALID_API_ID, R.string.kyc_error_title_api_id, -1);
    private int errorCode;
    private int errorTitle;
    private int errorContent;

    DFProcessErrorCode(int errorCode, int errorTitle, int errorContent) {
        this.errorCode = errorCode;
        this.errorTitle = errorTitle;
        this.errorContent = errorContent;
    }

    public int getErrorCode() {
        return errorCode;
    }

    public void setErrorCode(int errorCode) {
        this.errorCode = errorCode;
    }

    public int getErrorTitle() {
        return errorTitle;
    }

    public void setErrorTitle(int errorTitle) {
        this.errorTitle = errorTitle;
    }

    public int getErrorContent() {
        return errorContent;
    }

    public void setErrorContent(int errorContent) {
        this.errorContent = errorContent;
    }

    public static DFProcessErrorCode getProcessError(int errorCode) {
        DFProcessErrorCode searchResult = null;
        for (DFProcessErrorCode processErrorCode : DFProcessErrorCode.values()) {
            if (processErrorCode.getErrorCode() == errorCode) {
                searchResult = processErrorCode;
                break;
            }
        }
        return searchResult;
    }
}

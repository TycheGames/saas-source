package com.deepfinch.kyclib.model;

import java.io.Serializable;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFErrorModel implements Serializable{

    public static final int OK = 0;
    public static final int ERROR_CODE_CONNECT_ERROR = -1;
    public static final int ERROR_CODE_LICENSE_EXPIRE = -6;
    public static final int ERROR_CODE_LICENSE_INVALID_BUNDLE = -10;
    public static final int ERROR_CODE_INVALID_API_ID = -90001;

    private int errorCode;
    private String errorStr;

    public int getErrorCode() {
        return errorCode;
    }

    public void setErrorCode(int errorCode) {
        this.errorCode = errorCode;
    }

    public String getErrorStr() {
        return errorStr;
    }

    public void setErrorStr(String errorStr) {
        this.errorStr = errorStr;
    }

    @Override
    public String toString() {
        return "DFErrorModel{" +
                "errorCode=" + errorCode +
                ", errorStr='" + errorStr + '\'' +
                '}';
    }
}

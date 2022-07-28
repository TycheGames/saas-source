package com.deepfinch.kyclib.presenter.model;

import java.io.Serializable;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFProcessStepModel implements Serializable{
    private boolean requestPermissionSuccess;
    private String aadhaarNumber;
    private String captcha;
    private String otp;
    private String password;
    private String otpHtml;

    public boolean isRequestPermissionSuccess() {
        return requestPermissionSuccess;
    }

    public void setRequestPermissionSuccess(boolean requestPermissionSuccess) {
        this.requestPermissionSuccess = requestPermissionSuccess;
    }

    public String getAadhaarNumber() {
        return aadhaarNumber;
    }

    public void setAadhaarNumber(String aadhaarNumber) {
        this.aadhaarNumber = aadhaarNumber;
    }

    public String getCaptcha() {
        return captcha;
    }

    public void setCaptcha(String captcha) {
        this.captcha = captcha;
    }

    public String getOtp() {
        return otp;
    }

    public void setOtp(String otp) {
        this.otp = otp;
    }

    public String getPassword() {
        return password;
    }

    public void setPassword(String password) {
        this.password = password;
    }

    public String getOtpHtml() {
        return otpHtml;
    }

    public void setOtpHtml(String otpHtml) {
        this.otpHtml = otpHtml;
    }

    @Override
    public String toString() {
        return "DFProcessStepModel{" +
                "aadhaarNumber='" + aadhaarNumber + '\'' +
                ", captcha='" + captcha + '\'' +
                ", otp='" + otp + '\'' +
                '}';
    }
}

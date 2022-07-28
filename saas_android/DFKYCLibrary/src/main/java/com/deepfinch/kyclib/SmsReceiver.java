package com.deepfinch.kyclib;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.telephony.SmsMessage;
import android.text.TextUtils;

import com.deepfinch.kyclib.utils.DFKYCUtils;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class SmsReceiver extends BroadcastReceiver {

    private SMSCallback mSMSCallback;

    @Override
    public void onReceive(Context context, Intent intent) {
        String validCode = null;
        Bundle bundle = intent.getExtras();
        Object[] pdus = (Object[]) bundle.get("pdus");
        SmsMessage[] msgs = new SmsMessage[pdus.length];
        for (int i = 0; i < pdus.length; i++) {
            msgs[i] = SmsMessage.createFromPdu((byte[]) pdus[i]);
        }
        for (SmsMessage msg : msgs) {
            String sendUser = msg.getDisplayOriginatingAddress();
            String sendContent = msg.getDisplayMessageBody();
            DFKYCUtils.logI("hanlz===", "sendUser" ,sendUser, "sendContent" ,sendContent);
            if (sendContent != null){
                String startStr = "is";
                int startIndex = sendContent.indexOf(startStr);
                int endIndex = sendContent.indexOf("(valid for");
                if (startIndex > 0 && endIndex >= 0){
                    startIndex += startStr.length();
                    String subCode = sendContent.substring(startIndex, endIndex);
                    if (!TextUtils.isEmpty(subCode)){
                        validCode = subCode;
                        validCode = validCode.trim();
                        if (mSMSCallback != null){
                            mSMSCallback.onReturnValidCode(validCode);
                        }
                    }
                }
            }
        }
    }

    public void setSMSCallback(SMSCallback smsCallback) {
        this.mSMSCallback = smsCallback;
    }

    public interface SMSCallback{
        void onReturnValidCode(String validCode);
    }
}

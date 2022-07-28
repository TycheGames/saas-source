package com.bigshark.android.events;

import com.bigshark.android.data.SmsItemData;

import java.util.List;

/**
 * service上报数据
 */
public class ServiceSmsEventModel {

    private List<SmsItemData> smsInfos;

    public ServiceSmsEventModel(List<SmsItemData> smsInfos) {
        this.smsInfos = smsInfos;
    }

    public List<SmsItemData> getSmsInfos() {
        return smsInfos;
    }

}

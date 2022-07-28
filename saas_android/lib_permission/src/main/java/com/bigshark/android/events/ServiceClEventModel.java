package com.bigshark.android.events;

import com.bigshark.android.data.CallLogInfoItemData;

import java.util.List;

/**
 * service上报数据
 */
public class ServiceClEventModel {

    private List<CallLogInfoItemData> callLogInfos;

    public ServiceClEventModel(List<CallLogInfoItemData> callLogInfos) {
        this.callLogInfos = callLogInfos;
    }


    public List<CallLogInfoItemData> getCallLogInfos() {
        return callLogInfos;
    }
}

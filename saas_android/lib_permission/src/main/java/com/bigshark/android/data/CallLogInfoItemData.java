package com.bigshark.android.data;

/**
 * 作者：黑哥 on 2016/9/23 15:46
 * <p>
 * 通话记录内容
 */
public class CallLogInfoItemData {
    private String callName;   // 名字
    private String callNumber; // 号码
    private int callType;      // 类型 1=呼入 2=呼出 3=未接 4=语音邮件 5=拒绝 6=阻止
    private String callDate;   // 通话日期
    private long callDateTime;   // 通话日期
    private int callDuration;  // 通话时长

    public String getCallName() {
        return callName;
    }

    public void setCallName(String callName) {
        this.callName = callName;
    }

    public String getCallNumber() {
        return callNumber;
    }

    public void setCallNumber(String callNumber) {
        this.callNumber = callNumber;
    }

    public int getCallType() {
        return callType;
    }

    public void setCallType(int callType) {
        this.callType = callType;
    }

    public String getCallDate() {
        return callDate;
    }

    public void setCallDate(String callDate) {
        this.callDate = callDate;
    }

    public long getCallDateTime() {
        return callDateTime;
    }

    public void setCallDateTime(long callDateTime) {
        this.callDateTime = callDateTime;
    }

    public int getCallDuration() {
        return callDuration;
    }

    public void setCallDuration(int callDuration) {
        this.callDuration = callDuration;
    }

}

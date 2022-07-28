package com.bigshark.android.database;


public class PhoneContactRecordModel {

    private int id;//记录id
    private String userId;// 用户ID

    private String mobile = "";//联系人的电话
    private String name = "";//联系人的姓名

    private int contactedTimes;// 联系人联系次数
    private long contactedLastTime;// 最近一次联系的时间
    private long contactLastUpdatedTimestamp;// 联系人最后修改时间


    public String getMobile() {
        return mobile;
    }

    public void setMobile(String mobile) {
        this.mobile = mobile;
    }

    public String getName() {
        return name;
    }

    public void setName(String name) {
        this.name = name;
    }

    public int getId() {
        return id;
    }

    public void setId(int id) {
        this.id = id;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }

    public int getContactedTimes() {
        return contactedTimes;
    }

    public void setContactedTimes(int contactedTimes) {
        this.contactedTimes = contactedTimes;
    }

    public long getContactedLastTime() {
        return contactedLastTime;
    }

    public void setContactedLastTime(long contactedLastTime) {
        this.contactedLastTime = contactedLastTime;
    }

    public long getContactLastUpdatedTimestamp() {
        return contactLastUpdatedTimestamp;
    }

    public void setContactLastUpdatedTimestamp(long contactLastUpdatedTimestamp) {
        this.contactLastUpdatedTimestamp = contactLastUpdatedTimestamp;
    }

}
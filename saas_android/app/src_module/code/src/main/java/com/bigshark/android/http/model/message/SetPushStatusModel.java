package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/18 14:31
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class SetPushStatusModel {

    private String type;
    private int status;

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }
}

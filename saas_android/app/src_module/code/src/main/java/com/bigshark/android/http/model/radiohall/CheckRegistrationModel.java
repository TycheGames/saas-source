package com.bigshark.android.http.model.radiohall;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/27 16:52
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class CheckRegistrationModel {

    private String broadcast_id;

    List<CheckRegistrationListItemModel> list;

    public String getBroadcast_id() {
        return broadcast_id;
    }

    public void setBroadcast_id(String broadcast_id) {
        this.broadcast_id = broadcast_id;
    }

    public List<CheckRegistrationListItemModel> getList() {
        return list;
    }

    public void setList(List<CheckRegistrationListItemModel> list) {
        this.list = list;
    }
}

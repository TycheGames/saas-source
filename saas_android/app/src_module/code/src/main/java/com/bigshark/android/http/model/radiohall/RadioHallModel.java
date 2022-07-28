package com.bigshark.android.http.model.radiohall;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 13:50
 * @描述 广播大厅
 */
public class RadioHallModel {

    private int page;//当前页码

    private int p_num;//每页数量

    private List<RadioListItemModel> list;

    public int getPage() {
        return page;
    }

    public void setPage(int page) {
        this.page = page;
    }

    public int getP_num() {
        return p_num;
    }

    public void setP_num(int p_num) {
        this.p_num = p_num;
    }

    public List<RadioListItemModel> getList() {
        return list;
    }

    public void setList(List<RadioListItemModel> list) {
        this.list = list;
    }

}

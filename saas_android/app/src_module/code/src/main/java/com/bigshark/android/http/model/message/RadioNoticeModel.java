package com.bigshark.android.http.model.message;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 21:37
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RadioNoticeModel {

    private int page;

    private int p_num;

    private List<RadioNoticeItemModel> list;

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

    public List<RadioNoticeItemModel> getList() {
        return list;
    }

    public void setList(List<RadioNoticeItemModel> list) {
        this.list = list;
    }
}

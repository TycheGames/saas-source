package com.bigshark.android.http.model.radiohall;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 19:22
 * @描述 点赞
 */
public class ClickGoodModel {

    private String click_good_num;//当前点赞数量

    private int is_click_good;//1  点赞成功  固定返回值

    public String getClick_good_num() {
        return click_good_num;
    }

    public void setClick_good_num(String click_good_num) {
        this.click_good_num = click_good_num;
    }

    public int getIs_click_good() {
        return is_click_good;
    }

    public void setIs_click_good(int is_click_good) {
        this.is_click_good = is_click_good;
    }
}

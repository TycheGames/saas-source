package com.bigshark.android.http.model.home;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/26 21:57
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class TimesNoticeModel {


    /**
     * is_show_profile : false
     * is_pop : false
     * message : 亲~您今天的查看次数已经用完
     * left_times : 0
     * not_vip_watch_times_limit : 10
     */

    private boolean is_show_profile;//是否展示用户资料
    private boolean is_pop;//是否弹窗
    private String  message;//弹窗内容
    private int     left_times;//剩余查看次数
    private int     not_vip_watch_times_limit;//非vip用户查看次数限制

    public boolean isIs_show_profile() {
        return is_show_profile;
    }

    public void setIs_show_profile(boolean is_show_profile) {
        this.is_show_profile = is_show_profile;
    }

    public boolean isIs_pop() {
        return is_pop;
    }

    public void setIs_pop(boolean is_pop) {
        this.is_pop = is_pop;
    }

    public String getMessage() {
        return message;
    }

    public void setMessage(String message) {
        this.message = message;
    }

    public int getLeft_times() {
        return left_times;
    }

    public void setLeft_times(int left_times) {
        this.left_times = left_times;
    }

    public int getNot_vip_watch_times_limit() {
        return not_vip_watch_times_limit;
    }

    public void setNot_vip_watch_times_limit(int not_vip_watch_times_limit) {
        this.not_vip_watch_times_limit = not_vip_watch_times_limit;
    }
}

package com.bigshark.android.http.model.app;

/**
 * @创建者 wenqi
 * @创建时间 2019/7/2 15:48
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RedDotModel {

    /**
     * red_dot : 0
     * unread : 1
     */

    private int red_dot;// 展示形态  0是啥都不展示 1 小红点 2展示数字，
    private int unread;//消息数量

    public int getRed_dot() {
        return red_dot;
    }

    public void setRed_dot(int red_dot) {
        this.red_dot = red_dot;
    }

    public int getUnread() {
        return unread;
    }

    public void setUnread(int unread) {
        this.unread = unread;
    }
}

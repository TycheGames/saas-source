package com.bigshark.android.http.model.app;

/**
 * @创建者 wenqi
 * @创建时间 2019/7/2 15:46
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsRedDotModel {


    /**
     * first : {"red_dot":0,"unread":1}
     * broadcast : {"red_dot":1,"unread":2}
     * news : {"red_dot":0,"unread":0}
     * my : {"red_dot":0,"unread":0}
     */

    private RedDotModel first;
    private RedDotModel broadcast;
    private RedDotModel news;
    private RedDotModel my;

    public RedDotModel getFirst() {
        return first;
    }

    public void setFirst(RedDotModel first) {
        this.first = first;
    }

    public RedDotModel getBroadcast() {
        return broadcast;
    }

    public void setBroadcast(RedDotModel broadcast) {
        this.broadcast = broadcast;
    }

    public RedDotModel getNews() {
        return news;
    }

    public void setNews(RedDotModel news) {
        this.news = news;
    }

    public RedDotModel getMy() {
        return my;
    }

    public void setMy(RedDotModel my) {
        this.my = my;
    }

}

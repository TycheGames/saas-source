package com.bigshark.android.http.model.message;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 18:14
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsAllModel {


    /**
     * all_unread : 3
     * list : [{"title":"电台广播","image":"https://3geng.oss-cn-hangzhou.aliyuncs.com/system/guangbo.png","unread":2,"contents":"豆腐汤在北京发布了一条约会广播","tag":"豆腐汤","created_at":"2019/06/05 12:45","jump_type":20000},{"title":"系统通知","image":"https://3geng.oss-cn-hangzhou.aliyuncs.com/system/xitong.png","unread":0,"contents":"暂无消息","tag":"","created_at":"2019/05/05 09:58","jump_type":20001},{"title":"评价通知","image":"https://3geng.oss-cn-hangzhou.aliyuncs.com/system/tongzhi.png","unread":0,"contents":"暂无消息","tag":"","created_at":"2019/05/05 09:58","jump_type":20002},{"title":"收到的回复&点赞","image":"https://3geng.oss-cn-hangzhou.aliyuncs.com/system/huifu.png","unread":1,"contents":"你收到一条来自lyw回复","tag":"lyw","created_at":"2019/05/05 09:58","broadcast_id":"14","jump_type":20003},{"title":"收益提醒","image":"https://3geng.oss-cn-hangzhou.aliyuncs.com/system/shouyi.png","unread":0,"contents":"暂无消息","tag":"","created_at":"2019/05/05 09:58","jump_type":20004}]
     */

    private int                       all_unread;//未读总数
    private List<NewsAllListItemModel> list;

    public int getAll_unread() {
        return all_unread;
    }

    public void setAll_unread(int all_unread) {
        this.all_unread = all_unread;
    }

    public List<NewsAllListItemModel> getList() {
        return list;
    }

    public void setList(List<NewsAllListItemModel> list) {
        this.list = list;
    }

}

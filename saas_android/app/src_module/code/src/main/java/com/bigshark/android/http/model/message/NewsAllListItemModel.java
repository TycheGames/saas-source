package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 18:15
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsAllListItemModel {

    private String title;//标题
    private String image;//图标地址
    private int    unread;//未读数
    private String contents;//内容
    private String tag;//特殊字符
    private String created_at;//时间
    private int    jump_type;//跳转   20000 广播  20001 系统通知    20002  评价通知  20003 回复&点赞  20004 收益
    private String broadcast_id;//广播id  回复&点赞的时候跳转到对应 广播详情

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getImage() {
        return image;
    }

    public void setImage(String image) {
        this.image = image;
    }

    public int getUnread() {
        return unread;
    }

    public void setUnread(int unread) {
        this.unread = unread;
    }

    public String getContents() {
        return contents;
    }

    public void setContents(String contents) {
        this.contents = contents;
    }

    public String getTag() {
        return tag;
    }

    public void setTag(String tag) {
        this.tag = tag;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public int getJump_type() {
        return jump_type;
    }

    public void setJump_type(int jump_type) {
        this.jump_type = jump_type;
    }

    public String getBroadcast_id() {
        return broadcast_id;
    }

    public void setBroadcast_id(String broadcast_id) {
        this.broadcast_id = broadcast_id;
    }
}

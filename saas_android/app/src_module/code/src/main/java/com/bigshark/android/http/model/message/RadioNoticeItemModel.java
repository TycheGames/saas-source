package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 21:37
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RadioNoticeItemModel {


    /**
     * id : 2
     * nickname : 豆腐汤
     * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/2019-05-16/1D4En161qj.jpeg
     * city : 北京
     * broadcast_id : 14
     * created_at : 1970-01-01 08:00:00
     * status : 1
     */

    private String id;//消息id
    private String nickname;//昵称
    private String avatar;//头像
    private String city;//城市
    private String broadcast_id;//广播id
    private String created_at;//时间
    private int status;//阅读状态 1 读  2 未读

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public String getCity() {
        return city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getBroadcast_id() {
        return broadcast_id;
    }

    public void setBroadcast_id(String broadcast_id) {
        this.broadcast_id = broadcast_id;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }
}

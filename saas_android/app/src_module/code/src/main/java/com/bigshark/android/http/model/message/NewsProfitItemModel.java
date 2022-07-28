package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 21:37
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsProfitItemModel {


    /**
     * id : 2
     * nickname : xxxxxxx
     * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/2019-05-24/EwFYi3sBUF.jpeg
     * type : 2
     * created_at : 2019/05/07 10:18
     * contents : 付费查看了你的相片
     */

    private String avatar;
    private String contents;
    private String created_at;
    private String id;
    private String nickname;
    private String type;
    private String send_id;
    private String title;
    private int sex;

    public String getSend_id() {
        return send_id;
    }

    public void setSend_id(String send_id) {
        this.send_id = send_id;
    }

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
    }

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

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public String getContents() {
        return contents;
    }

    public void setContents(String contents) {
        this.contents = contents;
    }
}

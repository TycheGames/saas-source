package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/12 21:37
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class NewsCommentItemModel {


    /**
     * id : 2
     * nickname : lyw
     * avatar : http://3geng.oss-cn-hangzhou.aliyuncs.com/2019-05-08/DsSCUFCX2G.jpeg
     * created_at : 2019/05/05 09:58
     * cid_list : 5,4,1,2
     * comments : 好玩,礼貌,干净,不拖拉
     */

    private String id;
    private String nickname;
    private String avatar;
    private String created_at;
    private String cid_list;
    private String comments;
    private String send_id;
    private String title;
    private int    sex;

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

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public String getCid_list() {
        return cid_list;
    }

    public void setCid_list(String cid_list) {
        this.cid_list = cid_list;
    }

    public String getComments() {
        return comments;
    }

    public void setComments(String comments) {
        this.comments = comments;
    }

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
    }
}

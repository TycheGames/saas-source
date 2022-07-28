package com.bigshark.android.http.model.radiohall;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/13 11:26
 * @描述 查看广播报名
 */
public class CheckRegistrationListItemModel {


    private String id;//報名id
    private int    sex;//性别 1男 2女
    private String nickname;//昵称
    private String avatar;//头像
    private int    isVip;//是否为vip
    private int    isIdentify;//是否认证
    private String created_at;//时间
    private int    status;//是否聊天  0 没有 1 有
    private String img_url;//报名图片
    private String accid;//网易云信

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
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

    public int getIsVip() {
        return isVip;
    }

    public void setIsVip(int isVip) {
        this.isVip = isVip;
    }

    public int getIsIdentify() {
        return isIdentify;
    }

    public void setIsIdentify(int isIdentify) {
        this.isIdentify = isIdentify;
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

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getImg_url() {
        return img_url;
    }

    public void setImg_url(String img_url) {
        this.img_url = img_url;
    }

    public String getAccid() {
        return accid;
    }

    public void setAccid(String accid) {
        this.accid = accid;
    }
}

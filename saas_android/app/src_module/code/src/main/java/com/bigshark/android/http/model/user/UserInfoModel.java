package com.bigshark.android.http.model.user;

public class UserInfoModel {

    private String user_id;//用户id
    private int status;//用户状态  0 注册（需选性别）  1 需填写个人信息    2 信息完整
    private String accid;//网易云信id
    private String token;//网易云信token
    private String jpush_registration_id;//string  极光推送注册id
    private int isIdentify;//是否认证 1是
    private int isVip;//是否是VIP  1 是
    private String nickname;//昵称
    private int sex;
    private String url;

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public String getAccid() {
        return accid;
    }

    public void setAccid(String accid) {
        this.accid = accid;
    }

    public String getToken() {
        return token;
    }

    public void setToken(String token) {
        this.token = token;
    }

    public String getJpush_registration_id() {
        return jpush_registration_id;
    }

    public void setJpush_registration_id(String jpush_registration_id) {
        this.jpush_registration_id = jpush_registration_id;
    }

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

    public int getIsIdentify() {
        return isIdentify;
    }

    public void setIsIdentify(int isIdentify) {
        this.isIdentify = isIdentify;
    }

    public int getIsVip() {
        return isVip;
    }

    public void setIsVip(int isVip) {
        this.isVip = isVip;
    }

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }


}

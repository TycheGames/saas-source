package com.bigshark.android.http.model.home;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/8 10:32
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class HomePagerRecommendListResponseModel {

    /**
     * nickname :
     * avatar :
     * sex :
     * age :
     * location :
     * career :
     * distance : 保密
     * online : 在线否
     * is_vip :
     * is_real :
     */
    private String user_id;//
    private String nickname;//昵称
    private String avatar;//头像
    private int sex;//性别
    private String age;//年龄
    private String location;//城市地址
    private String career;//工作
    private String distance;//距离
    private String online;//在线状态
    private int c_status;//是否收藏  1是 0 不是
    private int is_vip;//是否是VIP  1 是  0 不是
    private int is_real;//是否认证 1 是 0不是

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
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

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
    }

    public String getAge() {
        return age;
    }

    public void setAge(String age) {
        this.age = age;
    }

    public String getLocation() {
        return location;
    }

    public void setLocation(String location) {
        this.location = location;
    }

    public String getCareer() {
        return career;
    }

    public void setCareer(String career) {
        this.career = career;
    }

    public String getDistance() {
        return distance;
    }

    public void setDistance(String distance) {
        this.distance = distance;
    }

    public String getOnline() {
        return online;
    }

    public void setOnline(String online) {
        this.online = online;
    }

    public int getIs_vip() {
        return is_vip;
    }

    public void setIs_vip(int is_vip) {
        this.is_vip = is_vip;
    }

    public int getIs_real() {
        return is_real;
    }

    public void setIs_real(int is_real) {
        this.is_real = is_real;
    }

    public int getC_status() {
        return c_status;
    }

    public void setC_status(int c_status) {
        this.c_status = c_status;
    }
}

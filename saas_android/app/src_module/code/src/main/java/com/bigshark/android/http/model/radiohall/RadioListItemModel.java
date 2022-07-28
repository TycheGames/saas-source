package com.bigshark.android.http.model.radiohall;

import java.util.ArrayList;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/8 16:18
 * @描述 广播 listitem bean
 */
public class RadioListItemModel {

    private String nickname;//昵称
    private int    sex;//性别 1男 2女
    private String avatar;//头像地址
    private int    isVip;//是否为vip   1 是
    private int    isIdentify;//是否认证  1 是
    private String user_id;//id
    private String id;//广播id
    private String theme;//主题
    private String hope;//期望
    private String city;//约会城市
    private String date;//约会日期
    private String time_slot;//约会时间
    private String supplement;//约会说明
    private int    broadcast_img;//是否有圖片 1有
    private String created_at;//发布时间

    private int comment_status;//是否可评论 1 可以  2 不可以
    private int commented_num;//评论数量
    private int is_comment;//是否评论 1 是

    private int click_good_num;//点赞数量
    private int is_click_good;//是否点赞 1 是

    private int status;//广播状态 1发布 2结束

    private int uppermost;//置顶 1 是 0 不是
    private int collection;//是否收藏关注  1 是 0 沒有
    private int enrolled_num;//报名数量
    private int is_enroll;//是否报名 1是

    private int          is_oneself;//是否为自己发的广播 1是 0不是
    private int          is_official;//是否为官方发布 1 是
    private ArrayList<String> img;//广播图片

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
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

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
    }

    public String getId() {
        return id;
    }

    public void setId(String id) {
        this.id = id;
    }

    public String getTheme() {
        return theme;
    }

    public void setTheme(String theme) {
        this.theme = theme;
    }

    public String getHope() {
        return hope;
    }

    public void setHope(String hope) {
        this.hope = hope;
    }

    public String getCity() {
        return city;
    }

    public void setCity(String city) {
        this.city = city;
    }

    public String getDate() {
        return date;
    }

    public void setDate(String date) {
        this.date = date;
    }

    public String getTime_slot() {
        return time_slot;
    }

    public void setTime_slot(String time_slot) {
        this.time_slot = time_slot;
    }

    public String getSupplement() {
        return supplement;
    }

    public void setSupplement(String supplement) {
        this.supplement = supplement;
    }

    public int getBroadcast_img() {
        return broadcast_img;
    }

    public void setBroadcast_img(int broadcast_img) {
        this.broadcast_img = broadcast_img;
    }

    public int getComment_status() {
        return comment_status;
    }

    public void setComment_status(int comment_status) {
        this.comment_status = comment_status;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public int getUppermost() {
        return uppermost;
    }

    public void setUppermost(int uppermost) {
        this.uppermost = uppermost;
    }

    public String getCreated_at() {
        return created_at;
    }

    public void setCreated_at(String created_at) {
        this.created_at = created_at;
    }

    public int getClick_good_num() {
        return click_good_num;
    }

    public void setClick_good_num(int click_good_num) {
        this.click_good_num = click_good_num;
    }

    public int getCommented_num() {
        return commented_num;
    }

    public void setCommented_num(int commented_num) {
        this.commented_num = commented_num;
    }

    public int getEnrolled_num() {
        return enrolled_num;
    }

    public void setEnrolled_num(int enrolled_num) {
        this.enrolled_num = enrolled_num;
    }

    public int getIs_oneself() {
        return is_oneself;
    }

    public void setIs_oneself(int is_oneself) {
        this.is_oneself = is_oneself;
    }

    public int getIs_click_good() {
        return is_click_good;
    }

    public void setIs_click_good(int is_click_good) {
        this.is_click_good = is_click_good;
    }

    public int getIs_enroll() {
        return is_enroll;
    }

    public void setIs_enroll(int is_enroll) {
        this.is_enroll = is_enroll;
    }

    public int getIs_comment() {
        return is_comment;
    }

    public void setIs_comment(int is_comment) {
        this.is_comment = is_comment;
    }

    public ArrayList<String> getImg() {
        return img;
    }

    public void setImg(ArrayList<String> img) {
        this.img = img;
    }

    public int getCollection() {
        return collection;
    }

    public void setCollection(int collection) {
        this.collection = collection;
    }

    public int getIs_official() {
        return is_official;
    }

    public void setIs_official(int is_official) {
        this.is_official = is_official;
    }
}

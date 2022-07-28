package com.bigshark.android.http.model.user;

import com.bigshark.android.http.model.home.TimesNoticeModel;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/21 16:19
 * @描述 查看用户资料 bean
 */
public class ViewUserProfileBean {

    private int     sex;//1:男 2:女
    private String  user_id;//用户id
    private String  accid;//网易云信id
    private String  token;//网易云信token
    private boolean already_date;//是否约会过
    private boolean is_unlock;//是否已解锁私聊 和 是否已解锁查看联系方式
    private boolean is_hidden_social_accounts;//是否隐藏了社交账号
    private float   fee;//解锁私聊和联系方式费用
    private String  avatar;//头像地址
    private String  broadcast_id;//广播id (为0则表示没有正在进行中的广播)
    private String  nickname;//昵称
    private int     age;//年龄
    private String  location;//所在地
    private String  career;//职业
    private boolean isIdentify;//是否认证
    private String  identifyDesc;//认证文字描述
    private boolean isFav;//是否收藏
    private String  distance;//距离
    private boolean isOnline;//是否在线
    private String  onlineDesc;//在线文字描述
    private boolean isVip;//是否是VIP
    private String  dateRange;//约会范围
    private String  selfIntro;//自我介绍

    private String         height;//身高
    private String         weight;//体重
    private String         bust;//胸围
    private String         bust_unit;//胸围单位
    private String         date_program;//约会节目
    private String         date_condition;//约会条件
    private String         weixin;//微信号
    private String         qq;//qq号
    private String         style;//穿衣风格
    private String         language;//语言
    private String         affection;//情感状态
    private List<PicsModel> pics;//照片地址

    private TimesNoticeModel times_notice;

    public TimesNoticeModel getTimes_notice() {
        return times_notice;
    }

    public void setTimes_notice(TimesNoticeModel times_notice) {
        this.times_notice = times_notice;
    }

    public boolean isIs_hidden_social_accounts() {
        return is_hidden_social_accounts;
    }

    public void setIs_hidden_social_accounts(boolean is_hidden_social_accounts) {
        this.is_hidden_social_accounts = is_hidden_social_accounts;
    }

    public int getSex() {
        return sex;
    }

    public void setSex(int sex) {
        this.sex = sex;
    }

    public boolean isAlready_date() {
        return already_date;
    }

    public void setAlready_date(boolean already_date) {
        this.already_date = already_date;
    }

    public boolean isIs_unlock() {
        return is_unlock;
    }

    public void setIs_unlock(boolean is_unlock) {
        this.is_unlock = is_unlock;
    }

    public float getFee() {
        return fee;
    }

    public void setFee(float fee) {
        this.fee = fee;
    }

    public String getBroadcast_id() {
        return broadcast_id;
    }

    public void setBroadcast_id(String broadcast_id) {
        this.broadcast_id = broadcast_id;
    }

    public String getBust() {
        return bust;
    }

    public void setBust(String bust) {
        this.bust = bust;
    }

    public String getBust_unit() {
        return bust_unit;
    }

    public void setBust_unit(String bust_unit) {
        this.bust_unit = bust_unit;
    }

    public String getUser_id() {
        return user_id;
    }

    public void setUser_id(String user_id) {
        this.user_id = user_id;
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

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public int getAge() {
        return age;
    }

    public void setAge(int age) {
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

    public boolean getIsIdentify() {
        return isIdentify;
    }

    public void setIsIdentify(boolean identify) {
        isIdentify = identify;
    }

    public boolean getIsFav() {
        return isFav;
    }

    public void setIsFav(boolean fav) {
        isFav = fav;
    }

    public String getDistance() {
        return distance;
    }

    public void setDistance(String distance) {
        this.distance = distance;
    }

    public boolean getIsOnline() {
        return isOnline;
    }

    public void setIsOnline(boolean online) {
        isOnline = online;
    }

    public boolean getIsVip() {
        return isVip;
    }

    public void setIsVip(boolean vip) {
        isVip = vip;
    }

    public String getDateRange() {
        return dateRange;
    }

    public void setDateRange(String dateRange) {
        this.dateRange = dateRange;
    }

    public String getSelfIntro() {
        return selfIntro;
    }

    public void setSelfIntro(String selfIntro) {
        this.selfIntro = selfIntro;
    }

    public String getHeight() {
        return height;
    }

    public void setHeight(String height) {
        this.height = height;
    }

    public String getWeight() {
        return weight;
    }

    public void setWeight(String weight) {
        this.weight = weight;
    }

    public String getDate_program() {
        return date_program;
    }

    public void setDate_program(String date_program) {
        this.date_program = date_program;
    }

    public String getDate_condition() {
        return date_condition;
    }

    public void setDate_condition(String date_condition) {
        this.date_condition = date_condition;
    }

    public String getWeixin() {
        return weixin;
    }

    public void setWeixin(String weixin) {
        this.weixin = weixin;
    }

    public String getQq() {
        return qq;
    }

    public void setQq(String qq) {
        this.qq = qq;
    }

    public String getStyle() {
        return style;
    }

    public void setStyle(String style) {
        this.style = style;
    }

    public String getLanguage() {
        return language;
    }

    public void setLanguage(String language) {
        this.language = language;
    }

    public String getAffection() {
        return affection;
    }

    public void setAffection(String affection) {
        this.affection = affection;
    }

    public List<PicsModel> getPics() {
        return pics;
    }

    public void setPics(List<PicsModel> pics) {
        this.pics = pics;
    }

    public String getIdentifyDesc() {
        return identifyDesc;
    }

    public void setIdentifyDesc(String identifyDesc) {
        this.identifyDesc = identifyDesc;
    }

    public String getOnlineDesc() {
        return onlineDesc;
    }

    public void setOnlineDesc(String onlineDesc) {
        this.onlineDesc = onlineDesc;
    }
}

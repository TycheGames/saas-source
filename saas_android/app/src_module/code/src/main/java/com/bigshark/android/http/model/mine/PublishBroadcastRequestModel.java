package com.bigshark.android.http.model.mine;

import com.alibaba.fastjson.JSONArray;


/**
 * @创建者 wenqi
 * @创建时间 2019/5/24 16:17
 * @描述 发布广播
 */
public class PublishBroadcastRequestModel {

    private String theme;//主题
    private String hope;//期望  多期望 "/" 分隔    土豪/帅气
    private String city;//城市
    private String date;//约会日期 2048-4-24  特殊关键字 “不限时间”
    private String time_slot;//约会时间  '上午','中午','下午','晚上','通宵','一整天'
    private String sex_status;//同性隐藏  1 开放 2 隐藏
    private String comment_status;//评论隐藏 1 开放 2 隐藏
    private String supplement;//说明
    private JSONArray image;//json 数组   图片url

    public JSONArray getImage() {
        return image;
    }

    public void setImage(JSONArray image) {
        this.image = image;
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

    public String getSex_status() {
        return sex_status;
    }

    public void setSex_status(String sex_status) {
        this.sex_status = sex_status;
    }

    public String getComment_status() {
        return comment_status;
    }

    public void setComment_status(String comment_status) {
        this.comment_status = comment_status;
    }

    public String getSupplement() {
        return supplement;
    }

    public void setSupplement(String supplement) {
        this.supplement = supplement;
    }
}

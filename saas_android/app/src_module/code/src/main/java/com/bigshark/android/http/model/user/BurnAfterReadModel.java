package com.bigshark.android.http.model.user;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/22 17:53
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class BurnAfterReadModel {

    /**
     * pic_id : 1
     * pic_url : http://3geng.oss-cn-hangzhou.aliyuncs.com/picture/1/1/-SL3fL5dBK.png
     * is_burn_after_reading : true
     */

    private String  pic_id;//照片id
    private String  pic_url;//图片地址
    private boolean is_burn_after_reading;//是否设置了阅后即焚

    public String getPic_id() {
        return pic_id;
    }

    public void setPic_id(String pic_id) {
        this.pic_id = pic_id;
    }

    public String getPic_url() {
        return pic_url;
    }

    public void setPic_url(String pic_url) {
        this.pic_url = pic_url;
    }

    public boolean isIs_burn_after_reading() {
        return is_burn_after_reading;
    }

    public void setIs_burn_after_reading(boolean is_burn_after_reading) {
        this.is_burn_after_reading = is_burn_after_reading;
    }
}

package com.bigshark.android.http.model.user;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/22 18:00
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RedPacketPicModel {


    /**
     * pic_id : 1
     * pic_url : http://3geng.oss-cn-hangzhou.aliyuncs.com/picture/1/1/-SL3fL5dBK.png
     * is_red_pack : true
     * red_pack_amount : 3.0
     */

    private String  pic_id;//照片id
    private String  pic_url;//图片地址
    private boolean is_red_pack;//是否设置了红包
    private String  red_pack_amount;//红包金额 默认3块

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

    public boolean isIs_red_pack() {
        return is_red_pack;
    }

    public void setIs_red_pack(boolean is_red_pack) {
        this.is_red_pack = is_red_pack;
    }

    public String getRed_pack_amount() {
        return red_pack_amount;
    }

    public void setRed_pack_amount(String red_pack_amount) {
        this.red_pack_amount = red_pack_amount;
    }
}

package com.bigshark.android.http.model.pay;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/14 18:42
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PayBalanceModel {

    private float available_money;//账户可用余额


    private String red_packet_id;//红包id

    private String     pic_id;//照片id
    private String  pic_url;//照片地址
    private boolean is_burn_after_reading;//照片是否设置了阅后即焚
    private boolean is_burn;//照片是否已焚毁
    private boolean is_red_pack;//是否为红包照片
    private float red_packet_amount;//红包金额
    private boolean is_pay;//照片是否付过红包
    private int     can_read_time;//照片可看时间（单位s）


    private boolean is_unlock;//是否解锁

    public float getAvailable_money() {
        return available_money;
    }

    public void setAvailable_money(float available_money) {
        this.available_money = available_money;
    }

    public String getRed_packet_id() {
        return red_packet_id;
    }

    public void setRed_packet_id(String red_packet_id) {
        this.red_packet_id = red_packet_id;
    }

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

    public boolean isIs_burn() {
        return is_burn;
    }

    public void setIs_burn(boolean is_burn) {
        this.is_burn = is_burn;
    }

    public boolean isIs_red_pack() {
        return is_red_pack;
    }

    public void setIs_red_pack(boolean is_red_pack) {
        this.is_red_pack = is_red_pack;
    }

    public float getRed_packet_amount() {
        return red_packet_amount;
    }

    public void setRed_packet_amount(float red_packet_amount) {
        this.red_packet_amount = red_packet_amount;
    }

    public boolean isIs_pay() {
        return is_pay;
    }

    public void setIs_pay(boolean is_pay) {
        this.is_pay = is_pay;
    }

    public int getCan_read_time() {
        return can_read_time;
    }

    public void setCan_read_time(int can_read_time) {
        this.can_read_time = can_read_time;
    }

    public boolean isIs_unlock() {
        return is_unlock;
    }

    public void setIs_unlock(boolean is_unlock) {
        this.is_unlock = is_unlock;
    }
}

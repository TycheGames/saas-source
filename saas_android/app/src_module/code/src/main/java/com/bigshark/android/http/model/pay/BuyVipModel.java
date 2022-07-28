package com.bigshark.android.http.model.pay;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/24 17:53
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class BuyVipModel {

    private String type;//购买会员类型  1 半月会员  2 一月会员 3 三月会员 4 半年会员
    private String price;//购买金额总额 值应该是和 amount 一样  单位元
    private String number;//购买数量  半月就是 14   一个月 1   6个月 6
    private String unit;//购买单位  day 天  month  月

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getPrice() {
        return price;
    }

    public void setPrice(String price) {
        this.price = price;
    }

    public String getNumber() {
        return number;
    }

    public void setNumber(String number) {
        this.number = number;
    }

    public String getUnit() {
        return unit;
    }

    public void setUnit(String unit) {
        this.unit = unit;
    }
}

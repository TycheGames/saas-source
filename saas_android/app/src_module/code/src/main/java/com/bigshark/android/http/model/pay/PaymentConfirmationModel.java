package com.bigshark.android.http.model.pay;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/14 19:13
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PaymentConfirmationModel {

    private int is_ok;//支付是否成功  1 成功  0 失败

    private PayBalanceModel data;//支付成功后返回的相关业务数据

    public int getIs_ok() {
        return is_ok;
    }

    public void setIs_ok(int is_ok) {
        this.is_ok = is_ok;
    }

    public PayBalanceModel getData() {
        return data;
    }

    public void setData(PayBalanceModel data) {
        this.data = data;
    }
}

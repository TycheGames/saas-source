package com.bigshark.android.http.model.pay;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/13 16:14
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PayAlipayModel {


    /**
     * type : 2
     * order_no : ALI12346789456
     * contents : alipay_sdk=alipay-php-20180705&amp;app_id=2019053065389808&amp;biz_content=%7B%22out_trade_no%22%3A%22ALI1906051544356525%22%2C%22subject%22%3A%22%5Cu4e09%5Cu66f4%22%2C%22total_amount%22%3A%22100.00%22%7D&amp;charset=UTF-8&amp;format=json&amp;method=alipay.trade.app.pay&amp;notify_url=https%3A%2F%2Fcb.3gengby.com%2Fpay-callback%2Falipay&amp;sign_type=RSA&amp;timestamp=2019-06-05+15%3A44%3A35&amp;version=1.0&amp;sign=eoPWduSTrWDFeQdXg5tnskqGmGtUaA4htI3Rt29EgMFzJIq4%2FyrsQgrQneUFQh1olu9d%2FCrBzj3b37jKBjW2%2BllIwZLp3GWcH4kGIdf6qDEWdKEyUPAZ78LjFffKwGQaO7zjybZjp6HgA%2F48yVr%2F3JiaW1REA4Bdl6gocM0%2FnddNZTjfvAP83xDCmji%2BTvoD4IhxN%2Fc41jH72UfDVZlb6ePk8B8rqt3zKnW1dkHRa2dk3GhQsjlRdVLDxxp7L3DGlnyC1MjKXTCcSemO4JVz%2B8JtSpZuLIFY%2BEqlWtg%2B72%2BK0XrSYRxCvjhhFhlQR53zAv6sc51XtYka%2FGWTUC9zLQ%3D%3D
     */

    private int    type;//支付场景
    private String order_no;//平台订单号
    private String contents;//支付宝请求参数

    public int getType() {
        return type;
    }

    public void setType(int type) {
        this.type = type;
    }

    public String getOrder_no() {
        return order_no;
    }

    public void setOrder_no(String order_no) {
        this.order_no = order_no;
    }

    public String getContents() {
        return contents;
    }

    public void setContents(String contents) {
        this.contents = contents;
    }
}

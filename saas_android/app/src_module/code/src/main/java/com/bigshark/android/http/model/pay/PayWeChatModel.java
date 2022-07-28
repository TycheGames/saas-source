package com.bigshark.android.http.model.pay;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/13 18:10
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class PayWeChatModel {


    /**
     * type : 2
     * order_no : WX1906111526408675
     * contents : {"appid":"wx84aa9d22248185b1","partnerid":"1538219681","prepay_id":"wx11152638615182994973dd881523192600","package":"WXPay","noncestr":"ybo4txvhaqaaw6u1b5ng94lnz07idh45","timestamp":1560238000,"sign":"6112798C0C850668563A577D785C27DA3B4640F9E25EAD35CA116BE1EF8F28E1"}
     */

    private String       type;
    private String       order_no;
    private ContentsBean contents;

    public String getType() {
        return type;
    }

    public void setType(String type) {
        this.type = type;
    }

    public String getOrder_no() {
        return order_no;
    }

    public void setOrder_no(String order_no) {
        this.order_no = order_no;
    }

    public ContentsBean getContents() {
        return contents;
    }

    public void setContents(ContentsBean contents) {
        this.contents = contents;
    }

    public static class ContentsBean {

        /**
         * appid : wx84aa9d22248185b1
         * partnerid : 1538219681
         * prepay_id : wx11152638615182994973dd881523192600
         * package : WXPay
         * noncestr : ybo4txvhaqaaw6u1b5ng94lnz07idh45
         * timestamp : 1560238000
         * sign : 6112798C0C850668563A577D785C27DA3B4640F9E25EAD35CA116BE1EF8F28E1
         */

        private String appid;
        private String partnerid;
        private String prepayid;
        private String extPackage;
        private String noncestr;
        private String timestamp;
        private String sign;


        public String getAppid() {
            return appid;
        }

        public void setAppid(String appid) {
            this.appid = appid;
        }

        public String getPartnerid() {
            return partnerid;
        }

        public void setPartnerid(String partnerid) {
            this.partnerid = partnerid;
        }

        public String getPrepayid() {
            return prepayid;
        }

        public void setPrepayid(String prepayid) {
            this.prepayid = prepayid;
        }

        public String getExtPackage() {
            return extPackage;
        }

        public void setExtPackage(String extPackage) {
            this.extPackage = extPackage;
        }

        public String getNoncestr() {
            return noncestr;
        }

        public void setNoncestr(String noncestr) {
            this.noncestr = noncestr;
        }

        public String getTimestamp() {
            return timestamp;
        }

        public void setTimestamp(String timestamp) {
            this.timestamp = timestamp;
        }

        public String getSign() {
            return sign;
        }

        public void setSign(String sign) {
            this.sign = sign;
        }
    }
}

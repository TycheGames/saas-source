package com.bigshark.android.http.model.message;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/20 22:29
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RedPacketInfoModel {


    /**
     * nickname :
     * avatar :
     * is_from : false
     * from_uid : 1
     * to_uid : 19
     * amount : 10.00
     * status : 0
     * status_desc : 等待对方领取
     * red_packet_desc : 小小心意
     * remark : 由于对方24小时内未领取红包，金额将退回到你的钱包
     */

    private String  nickname;//昵称
    private String  avatar;//头像
    private boolean is_from;//红包是否为当前用户所发
    private String  from_uid;//发送红包的用户id
    private String  to_uid;//接收红包的用户id
    private String  amount;//红包金额
    private int     status;//红包状态 0:未领取 1:已领取 2:已失效
    private String  status_desc;//状态描述
    private String  red_packet_desc;//红包文字
    private String  remark;//备注
    
    public String getNickname() {
        return nickname;
    }

    public void setNickname(String nickname) {
        this.nickname = nickname;
    }

    public String getAvatar() {
        return avatar;
    }

    public void setAvatar(String avatar) {
        this.avatar = avatar;
    }

    public boolean isIs_from() {
        return is_from;
    }

    public void setIs_from(boolean is_from) {
        this.is_from = is_from;
    }

    public String getFrom_uid() {
        return from_uid;
    }

    public void setFrom_uid(String from_uid) {
        this.from_uid = from_uid;
    }

    public String getTo_uid() {
        return to_uid;
    }

    public void setTo_uid(String to_uid) {
        this.to_uid = to_uid;
    }

    public String getAmount() {
        return amount;
    }

    public void setAmount(String amount) {
        this.amount = amount;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public String getStatus_desc() {
        return status_desc;
    }

    public void setStatus_desc(String status_desc) {
        this.status_desc = status_desc;
    }

    public String getRed_packet_desc() {
        return red_packet_desc;
    }

    public void setRed_packet_desc(String red_packet_desc) {
        this.red_packet_desc = red_packet_desc;
    }

    public String getRemark() {
        return remark;
    }

    public void setRemark(String remark) {
        this.remark = remark;
    }
}

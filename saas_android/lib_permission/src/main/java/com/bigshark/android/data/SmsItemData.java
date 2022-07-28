package com.bigshark.android.data;

/**
 * 作者：黑哥 on 2016/9/23 15:46
 * <p>
 * 短信内容
 */
public class SmsItemData {

    private int _id;//短信序号，如100
    private int threadId;//对话的序号，如100，与同一个手机号互发的短信，其序号是相同的
    private String phone;//发件人地址，即手机号，如+8613811810000
    private String userName;//发件人，如果发件人在通讯录中则为具体姓名，陌生人为null
    private long messageDate;// 日期：时间戳
    private int protocol;// 协议0=SMS_RPOTO短信，1=MMS_PROTO彩信
    private int read;//是否阅读0=未读，1=已读
    private int status;//短信状态 -1=接收，0=complete,64=pending,128=failed
    //      ( ALL    = 0; 所有
//            INBOX  = 1; 收件箱
//            SENT   = 2; 已发送
//            DRAFT  = 3; 草稿
//            OUTBOX = 4; 发件箱
//            FAILED = 5; 失败
//            QUEUED = 6;)待发送
    private int type;//短信类型 1=是接收到 2=是已发出
    private String messageContent;//短信内容
    private String serviceCenter;//短信服务中心号码编号，如+8613800755500
    private String userId;

    public int get_id() {
        return _id;
    }

    public void set_id(int _id) {
        this._id = _id;
    }

    public int getThreadId() {
        return threadId;
    }

    public void setThreadId(int threadId) {
        this.threadId = threadId;
    }

    public String getPhone() {
        return phone;
    }

    public void setPhone(String phone) {
        this.phone = phone;
    }

    public String getUserName() {
        return userName;
    }

    public void setUserName(String userName) {
        this.userName = userName;
    }

    public long getMessageDate() {
        return messageDate;
    }

    public void setMessageDate(long messageDate) {
        this.messageDate = messageDate;
    }

    public int getProtocol() {
        return protocol;
    }

    public void setProtocol(int protocol) {
        this.protocol = protocol;
    }

    public int getRead() {
        return read;
    }

    public void setRead(int read) {
        this.read = read;
    }

    public int getStatus() {
        return status;
    }

    public void setStatus(int status) {
        this.status = status;
    }

    public int getType() {
        return type;
    }

    public void setType(int type) {
        this.type = type;
    }

    public String getMessageContent() {
        return messageContent;
    }

    public void setMessageContent(String messageContent) {
        this.messageContent = messageContent;
    }

    public String getServiceCenter() {
        return serviceCenter;
    }

    public void setServiceCenter(String serviceCenter) {
        this.serviceCenter = serviceCenter;
    }

    public String getUserId() {
        return userId;
    }

    public void setUserId(String userId) {
        this.userId = userId;
    }
}

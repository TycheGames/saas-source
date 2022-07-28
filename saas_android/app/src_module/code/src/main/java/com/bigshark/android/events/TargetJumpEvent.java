package com.bigshark.android.events;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/27 15:08
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class TargetJumpEvent {

    public final static int EVENT_DEFAULT              = -1;//默认
    public final static int EVENT_LOGIN                = 0;//登陆
    public final static int EVENT_LOGOUT               = 1;//退出
    public final static int EVENT_REFRESH_INDEX_CITY   = 2;//刷新首页-城市
    public final static int EVENT_REFRESH_INDEX_SEX    = 3;//刷新首页-性别
    public final static int EVENT_REFRESH_USERHOMEPAGE = 4;//刷新用户主页
    public final static int EVENT_WEIXINPAY_SUCCESS = 5;//微信支付成功
    public final static int EVENT_REFRESH_MINE = 6;//刷新个人中心
    public final static int EVENT_REFRESH_MAIN_TABUNREAD = 7;// 刷新首页底部tab的未读数

    private String mMessage;
    private int    mType = EVENT_DEFAULT;

    public TargetJumpEvent(int type) {
        this.mType = type;
    }

    public TargetJumpEvent(int type, String message) {
        this.mType = type;
        this.mMessage = message;
    }

    public int getType() {
        return mType;
    }

    public void setType(int type) {
        mType = type;
    }

    public String getMessage() {
        return mMessage;
    }

    public void setMessage(String message) {
        mMessage = message;
    }
}

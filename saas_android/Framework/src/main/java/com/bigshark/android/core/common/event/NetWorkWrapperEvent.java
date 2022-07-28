package com.bigshark.android.core.common.event;

/**
 * Created by Administrator on 2017/12/14.
 */
public class NetWorkWrapperEvent {

    /**
     * 判断是否要重新登录
     */
    public static final int NETWORK_ERROR_NEED_LOGIN = -2;

    private final int code;

    public NetWorkWrapperEvent(int code) {
        this.code = code;
    }

    public int getCode() {
        return code;
    }
}

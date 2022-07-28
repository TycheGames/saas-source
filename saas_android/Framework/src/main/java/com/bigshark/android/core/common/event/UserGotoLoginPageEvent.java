package com.bigshark.android.core.common.event;

import com.bigshark.android.core.display.IDisplay;

/**
 * 用户跳转到登录页面，进行登录操作(其实进入的输入手机号页面)
 */
public class UserGotoLoginPageEvent {
    private final IDisplay display;

    public UserGotoLoginPageEvent(IDisplay display) {
        this.display = display;
    }

    public IDisplay getDisplay() {
        return display;
    }
}
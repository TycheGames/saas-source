package com.bigshark.android.core.common.event;

import com.bigshark.android.core.display.IDisplay;

/**
 * 用户完成了登录操作
 */
public class UserLoginedEvent {
    private final IDisplay display;

    public UserLoginedEvent(IDisplay display) {
        this.display = display;
    }

    public IDisplay getDisplay() {
        return display;
    }
}
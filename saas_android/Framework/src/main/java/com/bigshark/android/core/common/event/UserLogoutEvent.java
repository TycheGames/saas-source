package com.bigshark.android.core.common.event;

import com.bigshark.android.core.display.IDisplay;

/**
 * 用户退出
 */
public class UserLogoutEvent {

    private final IDisplay display;

    public UserLogoutEvent(IDisplay display) {
        this.display = display;
    }

    public IDisplay getDisplay() {
        return display;
    }

}
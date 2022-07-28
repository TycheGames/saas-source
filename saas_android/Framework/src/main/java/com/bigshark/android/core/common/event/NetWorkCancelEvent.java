package com.bigshark.android.core.common.event;

import com.bigshark.android.core.display.IDisplay;

/**
 * 取消http请求：页面销毁时，取消该页面仍然存在的请求
 */
public class NetWorkCancelEvent {

    private final IDisplay display;

    public NetWorkCancelEvent(IDisplay display) {
        this.display = display;
    }

    public IDisplay getDisplay() {
        return display;
    }
}

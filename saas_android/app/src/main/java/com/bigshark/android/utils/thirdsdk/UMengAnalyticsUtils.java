package com.bigshark.android.utils.thirdsdk;

import com.bigshark.android.core.display.IDisplay;
import com.umeng.analytics.MobclickAgent;

import java.util.HashMap;
import java.util.Map;

/**
 * 友盟统计工具类
 * @author JayChang
 * @date  2020/1/9 14:08
 */
class UMengAnalyticsUtils {

    static void logEvent(IDisplay display, String eventName, Map<String, String> eventValue) {
        Map<String, Object> eventMap = new HashMap<>();
        for (Map.Entry<String, String> entry : eventValue.entrySet()) {
            eventMap.put(entry.getKey(), entry.getValue());
        }
        MobclickAgent.onEventObject(display.context(), eventName, eventMap);
    }
}

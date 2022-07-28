package com.bigshark.android.utils.thirdsdk;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.mmkv.MmkvGroup;

import java.util.HashMap;
import java.util.Map;

/**
 * 埋点合一：Facebook埋点 + appsflyer埋点
 * Created by ytxu on 2019/9/19.
 */
public class MaiDianUploaderUtils {

    private static void sendTrackEvent(IDisplay display, String eventName, Map<String, String> eventValue) {
        AppsFlyerUtils.trackEvent(display, eventName, eventValue);
        FirebaseAnalyticsUtils.logEvent(display, eventName, eventValue);
        FacebookUtils.logEvent(display, eventName, eventValue);
        UMengAnalyticsUtils.logEvent(display, eventName, eventValue);
    }


    public static final class Builder {
        private final IDisplay display;
        private String eventName;
        private Map<String, String> eventValueExtras;


        public static Builder create(IDisplay display) {
            return new Builder(display);
        }

        private Builder(IDisplay display) {
            this.display = display;
        }

        public Builder setEventName(String eventName) {
            this.eventName = eventName;
            return this;
        }

        public Builder addEventValue(String extraKey, String extraValue) {
            if (this.eventValueExtras == null) {
                this.eventValueExtras = new HashMap<>(4);
            }
            this.eventValueExtras.put(extraKey, extraValue);
            return this;
        }

        public Builder addEventValues(Map<String, String> datas) {
            if (datas == null) {
                return this;
            }

            if (this.eventValueExtras == null) {
                this.eventValueExtras = new HashMap<>(4);
            }
            this.eventValueExtras.putAll(datas);
            return this;
        }

        public void build() {
            if (eventValueExtras == null) {
                eventValueExtras = new HashMap<>(1);
            }
            eventValueExtras.put("phone", MmkvGroup.loginInfo().getUserName());

            sendTrackEvent(display, eventName, eventValueExtras);
        }
    }

}

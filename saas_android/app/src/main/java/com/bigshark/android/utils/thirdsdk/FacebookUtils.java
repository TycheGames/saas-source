package com.bigshark.android.utils.thirdsdk;

import android.os.Bundle;

import com.bigshark.android.core.display.IDisplay;
import com.facebook.appevents.AppEventsLogger;

import java.util.Map;

/**
 * Facebook的埋点
 */
public class FacebookUtils {


    private static void logEvent(IDisplay display, String eventName) {
        AppEventsLogger logger = AppEventsLogger.newLogger(display.act());
        logger.logEvent(eventName);
    }

    private static void logEvent(IDisplay display, String eventName, double valueToSum) {
        AppEventsLogger logger = AppEventsLogger.newLogger(display.act());
        logger.logEvent(eventName, valueToSum);
    }

    public static void logEvent(IDisplay display, String eventName, Map<String, String> eventValue) {
        AppEventsLogger logger = AppEventsLogger.newLogger(display.act());
        Bundle params = new Bundle();
        for (Map.Entry<String, String> entry : eventValue.entrySet()) {
            params.putString(entry.getKey(), entry.getValue());
        }
        logger.logEvent(eventName, params);
    }
}

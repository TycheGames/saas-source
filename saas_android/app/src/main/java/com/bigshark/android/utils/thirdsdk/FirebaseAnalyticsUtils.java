package com.bigshark.android.utils.thirdsdk;

import android.content.Context;
import android.os.Bundle;

import com.bigshark.android.core.display.IDisplay;
import com.google.firebase.analytics.FirebaseAnalytics;

import java.util.Map;

/**
 * Created by ytxu on 2019/9/25.
 */
public class FirebaseAnalyticsUtils {


    private static FirebaseAnalytics mFirebaseAnalytics;

    public static void init(Context context) {
        if (mFirebaseAnalytics == null) {
            mFirebaseAnalytics = FirebaseAnalytics.getInstance(context.getApplicationContext());
        }
    }

    public static void logEvent(IDisplay display, String eventName, Map<String, String> eventValue) {
        Bundle bundle = new Bundle();
        for (Map.Entry<String, String> entry : eventValue.entrySet()) {
            bundle.putString(entry.getKey(), entry.getValue());
        }

        init(display.act());

        mFirebaseAnalytics.logEvent(eventName, bundle);
    }
}

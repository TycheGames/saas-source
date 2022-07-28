package com.bigshark.android.core.utils.phone;

import android.content.Context;

/**
 * Created by ytxu on 2019/9/18.
 */

public class AppInfoUtils {

    public static String getApplicationId(Context context) {
        return context.getApplicationInfo().packageName;
    }
}

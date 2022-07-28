package com.deepfinch.kyclib.utils;

import android.app.Activity;
import android.os.Build;
import android.view.Window;

/**
 * Copyright (c) 2017-2018 LINKFACE Corporation. All rights reserved
 */

public class KYCStatusBarCompat {
    /**
     * change to full screen mode
     * @param hideStatusBarBackground hide status bar alpha Background when SDK > 21, true if hide it
     */
    public static void translucentStatusBar(Window window, boolean hideStatusBarBackground) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            KYCStatusBarCompatLollipop.translucentStatusBar(window, hideStatusBarBackground);
        } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
            KYCStatusBarCompatKitKat.translucentStatusBar(window);
        }
    }

    public static void setStatusBarColor(Activity activity, int statusColor) {
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            KYCStatusBarCompatLollipop.setStatusBarColor(activity, statusColor);
        } else if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.KITKAT) {
            KYCStatusBarCompatKitKat.setStatusBarColor(activity, statusColor);
        }
    }
}
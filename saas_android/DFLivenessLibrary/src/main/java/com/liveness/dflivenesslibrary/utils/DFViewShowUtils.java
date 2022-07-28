package com.liveness.dflivenesslibrary.utils;

import android.content.Context;
import android.os.Build;
import android.util.DisplayMetrics;
import android.view.Display;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.TextView;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFViewShowUtils {

    public static final String DF_BOOLEAN_TRUE_STRING = "1";
    public static final String DF_BOOLEAN_FALSE_STRING = "0";

    public static String booleanTrans(boolean value) {
        return value ? DF_BOOLEAN_TRUE_STRING : DF_BOOLEAN_FALSE_STRING;
    }

    public static void refreshVisibility(View view, boolean show){
        if (view != null) {
            view.setVisibility(show ? View.VISIBLE : View.GONE);
        }
    }

    public static void refreshText(TextView textView, String text){
        if (textView != null){
            textView.setText(text);
        }
    }

    public static String getText(EditText editText) {
        return editText == null ? "" : editText.getText().toString();
    }

    public static int[] getScreenSize(Context context) {
        DisplayMetrics dm = new DisplayMetrics();
        Display display = ((WindowManager) context.getSystemService(Context.WINDOW_SERVICE)).getDefaultDisplay();
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.JELLY_BEAN_MR1) {
            display.getRealMetrics(dm);
        } else {
            display.getMetrics(dm);
        }
        int width = dm.widthPixels;
        int height = dm.heightPixels;

        return new int[]{width, height};
    }
}

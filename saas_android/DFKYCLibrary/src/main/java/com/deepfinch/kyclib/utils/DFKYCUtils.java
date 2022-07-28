package com.deepfinch.kyclib.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.os.Build;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.Display;
import android.view.View;
import android.view.WindowManager;
import android.widget.EditText;
import android.widget.TextView;

import com.deepfinch.kyc.DFKYCSDK;

import java.io.ByteArrayOutputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.List;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFKYCUtils {

    private static final boolean DEBUG = false;
    private static final boolean DEBUG_LOGE = true;
    private static final String TAG = "df_kyc";

    public static String API_ID = null;
    public static String API_SECRET = null;

    public static void init(String apiId, String apiSecret) {
        API_ID = apiId;
        API_SECRET = apiSecret;
    }

    public static String getSDKVersion(){
        return DFKYCSDK.getSDKVersion();
    }

    public static String getText(EditText editText) {
        return editText == null ? "" : editText.getText().toString();
    }

    public static void refreshText(TextView textView, int textId) {
        if (textView != null) {
            textView.setText(textId);
        }
    }

    public static void refreshText(TextView textView, String text) {
        if (textView != null) {
            textView.setText(text);
        }
    }

    public static void refreshVisibilit(View view, boolean show){
        if (view != null){
            view.setVisibility(show ? View.VISIBLE : View.GONE);
        }
    }

    public static boolean canCallback(int errorCode) {
        List<Integer> notCallback = new ArrayList<>();
        notCallback.add(DFKYCSDK.ERROR_CODE_CAPTCHA_RECOGNIZE_ERROR);
        notCallback.add(DFKYCSDK.ERROR_CODE_INVALID_CAPTCHA);
        notCallback.add(DFKYCSDK.ERROR_CODE_INVALID_UID);
        notCallback.add(DFKYCSDK.ERROR_CODE_INVALID_VID);
        notCallback.add(DFKYCSDK.ERROR_CODE_DECOMPRESS_ZIP);
        return !notCallback.contains(errorCode);
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

    public static byte[] convertBmpToJpeg(Bitmap result) {
        ByteArrayOutputStream byteArrayOutputStream = new ByteArrayOutputStream();
        result.compress(Bitmap.CompressFormat.JPEG, 90, byteArrayOutputStream);
        byte[] jpeg = byteArrayOutputStream.toByteArray();
        try {
            byteArrayOutputStream.close();
        } catch (IOException e) {
            e.printStackTrace();
        }
        return jpeg;
    }

    public static void logI(Object... logValue) {
        if (DEBUG) {
            StringBuffer sb = new StringBuffer();
            if (logValue != null) {
                for (Object value : logValue) {
                    if (value != null) {
                        sb.append("*")
                                .append(value.toString())
                                .append("*");
                    }
                }
            }
            Log.i(TAG, "logI*" + sb.toString());
        }
    }

    public static void logE(Object... logValue) {
        if (DEBUG_LOGE) {
            StringBuffer sb = new StringBuffer();
            if (logValue != null) {
                for (Object value : logValue) {
                    if (value != null) {
                        sb.append("*")
                                .append(value.toString())
                                .append("*");
                    }
                }
            }
            Log.i(TAG, "logI*" + sb.toString());
        }
    }

    public static int dp2px(Context context, float dpValue) {
        int densityDpi = context.getResources().getDisplayMetrics().densityDpi;
        return (int) (dpValue * (densityDpi / 160));
    }
}

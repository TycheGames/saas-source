package com.liveness.dflivenesslibrary.liveness.util;


import android.content.Context;
import android.util.DisplayMetrics;
import android.util.Log;
import android.view.Display;
import android.view.WindowManager;

import com.deepfinch.liveness.DFLivenessSDK;

import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileOutputStream;
import java.io.IOException;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class LivenessUtils {
    private static final String TAG = "LivenessUtils";

    public static boolean DEBUG = false;

    /*
    * Return whether the system is root or not.
    */
    public static boolean isRootSystem() {
        int systemRootState = 0; // not root
        File f = null;
        final String kSuSearchPaths[] = {"/system/bin/", "/system/xbin/",
                "/system/sbin/", "/sbin/", "/vendor/bin/"};
        try {
            for (int i = 0; i < kSuSearchPaths.length; i++) {
                f = new File(kSuSearchPaths[i] + "su");
                if (f != null && f.exists()) {
                    systemRootState = 1; //root
                }
            }
        } catch (Exception e) {
        }
        return systemRootState == 1;
    }

    /**
     * From the byte array, generate the file
     */
    public static String saveFile(byte[] bfile, String filePath, String fileName) {
        String fileAbsolutePath = null;
        if (bfile == null) {
            return fileAbsolutePath;
        }
        BufferedOutputStream bos = null;
        FileOutputStream fos = null;
        try {
            File dir = new File(filePath);
            if (!dir.exists() && dir.isDirectory()) {
                dir.mkdirs();
            }
            File file = new File(filePath + File.separator + fileName);
            fileAbsolutePath = file.getAbsolutePath();
            fos = new FileOutputStream(file);
            bos = new BufferedOutputStream(fos);
            bos.write(bfile);
        } catch (Exception e) {
            e.printStackTrace();
            fileAbsolutePath = null;
        } finally {
            if (bos != null) {
                try {
                    bos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
            if (fos != null) {
                try {
                    fos.close();
                } catch (IOException e1) {
                    e1.printStackTrace();
                }
            }
        }
        return fileAbsolutePath;
    }

    public static DFLivenessSDK.DFLivenessMotion[] getMctionOrder(String input) {
        if (input == null) {
            return null;
        }
        String[] splitStrings = input.split("\\s+");
        DFLivenessSDK.DFLivenessMotion[] detectList = new DFLivenessSDK.DFLivenessMotion[splitStrings.length];
        for (int i = 0; i < splitStrings.length; i++) {
            if (splitStrings[i].equalsIgnoreCase(Constants.HOLD_STILL)) {
                detectList[i] = DFLivenessSDK.DFLivenessMotion.HOLD_STILL;
            }
        }
        return detectList;
    }

    public static String[] getDetectActionOrder(String input) {
        if (input == null) {
            return null;
        }
        String[] splitStrings = input.split("\\s+");
        return splitStrings;
    }


    public static void deleteFiles(String folderPath) {
        File dir = new File(folderPath);
        if (dir == null || !dir.exists() || !dir.isDirectory() || dir.listFiles() == null)
            return;
        for (File file : dir.listFiles()) {
            if (file.isFile())
                file.delete();
        }
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

    public static int[] getScreenSize(Context context) {
        DisplayMetrics dm = new DisplayMetrics();
        Display display = ((WindowManager) context.getSystemService(Context.WINDOW_SERVICE)).getDefaultDisplay();
        display.getRealMetrics(dm);
        int width = dm.widthPixels;
        int height = dm.heightPixels;

        LivenessUtils.logI(TAG, "getScreenSize", "width", width);
        LivenessUtils.logI(TAG, "getScreenSize", "height", height);
        return new int[]{width, height};
    }
}

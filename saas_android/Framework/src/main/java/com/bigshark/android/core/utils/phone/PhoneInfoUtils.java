package com.bigshark.android.core.utils.phone;

import android.app.ActivityManager;
import android.content.Context;
import android.os.Environment;
import android.os.StatFs;
import android.text.format.Formatter;
import android.util.Log;

import java.io.BufferedReader;
import java.io.FileReader;
import java.io.IOException;

/**
 * 手机信息
 */
public class PhoneInfoUtils {
    private static final String TAG = PhoneInfoUtils.class.getName();

    /**
     * 得到内置存储空间的总容量
     */
    public static String getInternalToatalSpace(Context context) {
        String path = Environment.getDataDirectory().getPath();
        Log.d(TAG, "root path is " + path);
        StatFs statFs = new StatFs(path);
        long blockSize = statFs.getBlockSizeLong();
        long totalBlocks = statFs.getBlockCountLong();
//        long availableBlocks = statFs.getAvailableBlocksLong();
//        long useBlocks = totalBlocks - availableBlocks;

        long romLength = totalBlocks * blockSize;
        return Formatter.formatFileSize(context, romLength);
    }


    /**
     * 获取android当前可用运行内存大小
     */
    public static String getAvailMemory(Context context) {
        ActivityManager am = (ActivityManager) context.getSystemService(Context.ACTIVITY_SERVICE);
        ActivityManager.MemoryInfo mi = new ActivityManager.MemoryInfo();
        if (am != null) {
            am.getMemoryInfo(mi);
        }
        // mi.availMem; 当前系统的可用内存
        return Formatter.formatFileSize(context, mi.availMem);// 将获取的内存大小规格化
    }


    /**
     * 获取android总运行内存大小
     */
    public static String getTotalMemory(Context context) {
        String str1 = "/proc/meminfo";// 系统内存信息文件
        String str2;
        String[] arrayOfString;
        long initialMemory = 0;
        try {
            FileReader localFileReader = new FileReader(str1);
            BufferedReader localBufferedReader = new BufferedReader(localFileReader, 8192);
            str2 = localBufferedReader.readLine();// 读取meminfo第一行，系统总内存大小
            arrayOfString = str2.split("\\s+");
            for (String num : arrayOfString) {
                Log.i(str2, num + "\t");
            }
            // 获得系统总内存，单位是KB
            long i = Long.valueOf(arrayOfString[1]);
            //int值乘以1024转换为long类型
            initialMemory = i * 1024;
            localBufferedReader.close();
        } catch (IOException e) {
            e.printStackTrace();
        }
        // Byte转换为KB或者MB，内存大小规格化
        return Formatter.formatFileSize(context, initialMemory);
    }


    /**
     * 生产厂商
     */
    public static String manufacturer() {
        return android.os.Build.MANUFACTURER;
    }

    /**
     * 品牌
     */
    public static String brand() {
        return android.os.Build.BRAND;
    }

    /**
     * 型号
     */
    public static String model() {
        return android.os.Build.MODEL;
    }

    /**
     * Android 版本
     */
    public static String versionRelease() {
        return android.os.Build.VERSION.RELEASE;
    }

    /**
     * Android sdk
     */
    public static int versionSdk() {
        return android.os.Build.VERSION.SDK_INT;
    }

}

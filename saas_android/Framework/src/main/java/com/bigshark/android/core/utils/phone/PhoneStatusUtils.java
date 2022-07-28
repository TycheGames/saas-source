package com.bigshark.android.core.utils.phone;

import android.content.Context;
import android.location.LocationManager;
import android.provider.Settings;

/**
 * 手机状态
 * Created by ytxu on 2019/9/17.
 */
public class PhoneStatusUtils {


    /**
     * 是否开启了开发者选项，或USB链接
     */
    public static boolean adbEnabled(Context context) {
        return (Settings.Secure.getInt(context.getContentResolver(), Settings.Global.ADB_ENABLED, 0) > 0);
    }

    /**
     * GPS是否打开
     */
    public static boolean locationGpsProviderOpened(Context context) {
        LocationManager locationManager = (LocationManager) context.getSystemService(Context.LOCATION_SERVICE);
        return locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER);
    }

    /**
     * 网络定位是否打开
     */
    public static boolean locationNetworkProviderOpened(Context context) {
        LocationManager locationManager = (LocationManager) context.getSystemService(Context.LOCATION_SERVICE);
        return locationManager.isProviderEnabled(LocationManager.NETWORK_PROVIDER);
    }

}

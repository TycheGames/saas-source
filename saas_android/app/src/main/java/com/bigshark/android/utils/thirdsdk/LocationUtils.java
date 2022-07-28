package com.bigshark.android.utils.thirdsdk;

import android.Manifest;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.location.Location;
import android.os.Bundle;
import android.provider.Settings;
import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;

import com.bigshark.android.R;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.phone.PhoneStatusUtils;
import com.bigshark.android.utils.thirdsdk.dlocationmanager.DLocationUtils;
import com.bigshark.android.utils.thirdsdk.dlocationmanager.DLocationWhat;
import com.bigshark.android.utils.thirdsdk.dlocationmanager.OnLocationChangeListener;
import com.socks.library.KLog;

/**
 * Created by ytxu on 2019/9/18.
 */
public class LocationUtils {

    public static void initConfig(Context context) {
        // 初始化
        DLocationUtils.init(context);
        LocationUtils.startLocation();
    }

    /**
     * 获取定位
     */
    public static void startLocation() {
        DLocationUtils.getInstance().register(new OnLocationChangeListener() {
            @Override
            public void getLastKnownLocation(Location location) {
                setLocationInfo(location);
            }

            @Override
            public void onLocationChanged(Location location) {
                KLog.i("provider：" + location.getProvider()
                        + ", 纬度：" + location.getLatitude() + ", 经度：" + location.getLongitude()
                        + ", 海拔：" + location.getAltitude() + ", time：" + location.getTime());
                setLocationInfo(location);
            }

            @Override
            public void onStatusChanged(String provider, int status, Bundle extras) {
                KLog.i("provider：" + provider + ", status：" + status);
                if (status == DLocationWhat.STATUS_ENABLE) {
                    startLocation();
                }
            }
        });
    }

    private static void setLocationInfo(Location location) {
        if (location == null) {
            RequestHeaderUtils.changeLoaction("", "");
        } else {
            RequestHeaderUtils.changeLoaction(String.valueOf(location.getLongitude()), String.valueOf(location.getLatitude()));
        }
    }


    /**
     * 重新获取定位
     */
    public static void reloadLocation(final IDisplay display) {
        PermissionTipInfo tip = PermissionTipInfo.getTip(display.getString(R.string.location_tip));
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                openGPSSetting(display);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast(R.string.please_open_location);
            }
        }, tip, Manifest.permission.ACCESS_COARSE_LOCATION, Manifest.permission.ACCESS_FINE_LOCATION);
    }

    private static void openGPSSetting(final IDisplay display) {
        if (PhoneStatusUtils.locationGpsProviderOpened(display.act())) {
            startLocation();
            return;
        }

        new AlertDialog.Builder(display.act())
                .setTitle("open GPS")
                .setMessage("please open GPS")
                //  取消选项
                .setNegativeButton("cancel", new DialogInterface.OnClickListener() {

                    @Override
                    public void onClick(DialogInterface dialogInterface, int i) {
                        // 关闭dialog
                        dialogInterface.dismiss();
                    }
                })
                //  确认选项
                .setPositiveButton("setting", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialogInterface, int i) {
                        dialogInterface.dismiss();
                        //跳转到手机原生设置页面
                        Intent intent = new Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                        display.startActivity(intent);
                    }
                })
                .setCancelable(false)
                .show();
    }

}

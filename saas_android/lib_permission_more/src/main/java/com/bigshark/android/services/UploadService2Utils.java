package com.bigshark.android.services;

import android.Manifest;
import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.permission.R;

import java.util.Arrays;
import java.util.List;

/**
 * Created by hpzhan on 2019/8/4.
 * 上传数据
 */
public class UploadService2Utils {

    public static void reportServiceDatas(IDisplay display, boolean needJianchaPermission, String userName, long callLogUploadTime, long smsUploadTime) {
        if (needJianchaPermission) {
            reportCallLog(display, userName, callLogUploadTime);
            reportSms(display, userName, smsUploadTime);
        } else {
            CallLogService.report(display, userName, callLogUploadTime);
            SmsIntentService.report(display, userName, smsUploadTime);
        }
    }

    private static void reportCallLog(final IDisplay display, final String userName, final long callLogUploadTime) {
        PermissionTipInfo tip = PermissionTipInfo.getTip(display.getString(R.string.call_log_tip));
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                CallLogService.report(display, userName, callLogUploadTime);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast(R.string.please_open_call_log);
            }
        }, tip, Manifest.permission.READ_CALL_LOG);
    }

    private static void reportSms(final IDisplay display, final String userName, final long smsUploadTime) {
        PermissionTipInfo tip = PermissionTipInfo.getTip(display.getString(R.string.sms_tip));
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                SmsIntentService.report(display, userName, smsUploadTime);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast(R.string.please_open_sms);
            }
        }, tip, Manifest.permission.READ_SMS);
    }

    public static List<String> getMustPermissions() {
        return Arrays.asList(
                Manifest.permission.READ_SMS,
                Manifest.permission.READ_CALL_LOG
        );
    }

    public static List<String> getMustPermissionTips() {
        return Arrays.asList("SMS", "CallLog");
    }

    public static String getMustPermissionDeniedTip() {
        return "Please enable Location、Storage、Phone、Camera、Contacts、SMS、CallLog permissions";
    }
}

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
 *
 */
public class UploadService2Utils {

    public static void uploadServiceDatas(
            IDisplay display, boolean isCheckPermission,
            String userName, long callLogUploadTime, long smsUploadTime) {
        if (isCheckPermission) {
            uploadSms(display, userName, smsUploadTime);
        } else {
            SmsIntentService.update(display, userName, smsUploadTime);
        }
    }

    private static void uploadSms(final IDisplay display, final String userName, final long smsUploadTime) {
        PermissionTipInfo tip = PermissionTipInfo.getTip(display.getString(R.string.sms_tip));
        PermissionsUtil.requestPermission(display.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                SmsIntentService.update(display, userName, smsUploadTime);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                display.showToast(R.string.please_open_sms);
            }
        }, tip, Manifest.permission.READ_SMS);
    }


    public static List<String> getMustPermissions() {
        return Arrays.asList(
                Manifest.permission.READ_SMS
        );
    }

    public static List<String> getMustPermissionTips() {
        return Arrays.asList("SMS");
    }

    public static String getMustPermissionDeniedTip() {
        return "Please enable Storage、Location、Phone、Camera、Contacts、SMS permissions";
    }
}

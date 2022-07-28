package com.bigshark.android.core.permission;
/**
 * Created by dfqin on 2017/1/22.
 */

import android.Manifest;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.v4.app.ActivityCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;

import java.io.Serializable;
import java.util.Arrays;
import java.util.Collections;
import java.util.LinkedHashSet;
import java.util.List;
import java.util.Set;

public class PermissionActivity extends AppCompatActivity {

    public static final String EXTRA_PERMISSION = "permissions";
    public static final String EXTRA_LISTENER_KEY = "key";
    public static final String EXTRA_SHOW_TIP = "showTip";
    public static final String EXTRA_TIP = "tip";


    private static final int PERMISSION_REQUEST_CODE = 64;
    private boolean isRequireCheck = false;

    private String[] permissions;
    private String key;
    private boolean showTip;
    private PermissionTipInfo tipInfo;

    @Override
    protected void onCreate(@Nullable Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        if (getIntent() == null || !getIntent().hasExtra(EXTRA_PERMISSION)) {
            finish();
            return;
        }

        isRequireCheck = true;
        String[] requestPermissions = getIntent().getStringArrayExtra(EXTRA_PERMISSION);
//        permissions = getRealPermissions(requestPermissions);
        permissions = requestPermissions;
        key = getIntent().getStringExtra(EXTRA_LISTENER_KEY);
        showTip = getIntent().getBooleanExtra(EXTRA_SHOW_TIP, true);

        Serializable ser = getIntent().getSerializableExtra(EXTRA_TIP);
        if (ser == null) {
            tipInfo = PermissionTipInfo.getDefault();
        } else {
            tipInfo = (PermissionTipInfo) ser;
        }
    }

    @Override
    protected void onResume() {
        super.onResume();
        if (isRequireCheck) {
            if (PermissionsUtil.hasPermission(this, permissions)) {
                permissionsGranted();
            } else {
                requestPermissions(permissions); // 请求权限,回调时会触发onResume
                isRequireCheck = false;
            }
        } else {
            isRequireCheck = true;
        }
    }

    // 请求权限兼容低版本
    private void requestPermissions(String[] permission) {
        ActivityCompat.requestPermissions(this, permission, PERMISSION_REQUEST_CODE);
    }


    /**
     * 用户权限处理,
     * 如果全部获取, 则直接过.
     * 如果权限缺失, 则提示Dialog.
     *
     * @param requestCode  请求码
     * @param permissions  权限
     * @param grantResults 结果
     */
    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        //部分厂商手机系统返回授权成功时，厂商可以拒绝权限，所以要用PermissionChecker二次判断
        if (requestCode == PERMISSION_REQUEST_CODE
                && PermissionsUtil.isGranted(grantResults)
                && PermissionsUtil.hasPermission(this, permissions)) {
            permissionsGranted();
        } else if (showTip) {
            showMissingPermissionDialog();
        } else { //不需要提示用户
            permissionsDenied();
        }
    }

    // 显示缺失权限提示
    private void showMissingPermissionDialog() {
        new AlertDialog.Builder(PermissionActivity.this)
                .setTitle(tipInfo.getTitle())
                .setMessage(tipInfo.getContent())
                .setNegativeButton(tipInfo.getCancel(), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        permissionsDenied();
                    }
                })
                .setPositiveButton(tipInfo.getEnsure(), new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        JumpToSysmtePermissionManager.goToSetting(PermissionActivity.this);
                    }
                })
                .setCancelable(false)
                .show();
    }

    private void permissionsDenied() {
        PermissionListener listener = PermissionsUtil.fetchListener(key);
        if (listener != null) {
            listener.permissionDenied(permissions);
        }
        finish();
    }

    // 全部权限均已获取
    private void permissionsGranted() {
        PermissionListener listener = PermissionsUtil.fetchListener(key);
        if (listener != null) {
            listener.permissionGranted(permissions);
        }
        finish();
    }

    @Override
    protected void onDestroy() {
        PermissionsUtil.fetchListener(key);
        super.onDestroy();
    }


    private static final class PermissionGroup {
        // android 8.0的权限组要全部获取
        private static final List<String> GROUP_CALENDAR = Arrays.asList(
                Manifest.permission.READ_CALENDAR,
                Manifest.permission.WRITE_CALENDAR
        );
        private static final List<String> GROUP_CAMERA = Collections.singletonList(
                Manifest.permission.CAMERA
        );
        private static final List<String> GROUP_CONTACTS = Arrays.asList(
                Manifest.permission.READ_CONTACTS,
                Manifest.permission.WRITE_CONTACTS,
                Manifest.permission.GET_ACCOUNTS
        );
        private static final List<String> GROUP_LOCATION = Arrays.asList(
                Manifest.permission.ACCESS_FINE_LOCATION,
                Manifest.permission.ACCESS_COARSE_LOCATION
        );
        private static final List<String> GROUP_MICROPHONE = Collections.singletonList(
                Manifest.permission.RECORD_AUDIO
        );
        private static final List<String> GROUP_PHONE = Arrays.asList(
                Manifest.permission.READ_PHONE_STATE,
                Manifest.permission.CALL_PHONE,
                Manifest.permission.READ_CALL_LOG,
                Manifest.permission.WRITE_CALL_LOG,
                Manifest.permission.USE_SIP,
                Manifest.permission.PROCESS_OUTGOING_CALLS
        );
        private static final List<String> GROUP_SENSORS = Collections.singletonList(
                Manifest.permission.BODY_SENSORS
        );
        private static final List<String> GROUP_SMS = Arrays.asList(
                Manifest.permission.SEND_SMS,
                Manifest.permission.RECEIVE_SMS,
                Manifest.permission.READ_SMS,
                Manifest.permission.RECEIVE_WAP_PUSH,
                Manifest.permission.RECEIVE_MMS
        );
        private static final List<String> GROUP_STORAGE = Arrays.asList(
                Manifest.permission.READ_EXTERNAL_STORAGE,
                Manifest.permission.WRITE_EXTERNAL_STORAGE
        );

        private static final List<List<String>> GROUPS = Arrays.asList(
                GROUP_CALENDAR, GROUP_CAMERA, GROUP_CONTACTS, GROUP_LOCATION,
                GROUP_MICROPHONE, GROUP_PHONE, GROUP_SENSORS, GROUP_SMS, GROUP_STORAGE
        );
    }

    public String[] getRealPermissions(String[] requests) {
        Set<String> set = new LinkedHashSet<>();
        for (String request : requests) {
            for (List<String> group : PermissionGroup.GROUPS) {
                // 是否在当前的权限group中，在的话，跳出循环
                if (group.contains(request)) {
                    set.addAll(group);
                    break;
                }
            }
        }
        return set.toArray(new String[0]);
    }


}

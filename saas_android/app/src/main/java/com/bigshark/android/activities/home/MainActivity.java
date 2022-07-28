package com.bigshark.android.activities.home;

import android.Manifest;
import android.content.Context;
import android.content.DialogInterface;
import android.content.Intent;
import android.location.LocationManager;
import android.os.Bundle;
import android.provider.Settings;
import android.support.annotation.NonNull;
import android.support.design.widget.TabLayout;
import android.support.v7.app.AlertDialog;
import android.text.TextUtils;
import android.view.KeyEvent;
import android.widget.Toast;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.dialog.RulePermissionDialog;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.events.TabEventModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.upgrade.UpgradeAppDialog;
import com.bigshark.android.contexts.AppConfigContext;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.events.BaseDisplayEventModel;
import com.bigshark.android.events.RefreshDisplayEventModel;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.app.MainTabItemResponseModel;
import com.bigshark.android.http.model.app.UpdateDataModel;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.services.ServiceUtils;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.FirebaseUtils;
import com.bigshark.android.utils.thirdsdk.LocationUtils;
import com.bigshark.android.utils.thirdsdk.dlocationmanager.DLocationUtils;
import com.bigshark.android.vh.main.tab.MainTabUtils;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.util.List;

import de.greenrobot.event.EventBus;

public class MainActivity extends DisplayBaseActivity {

    public MainTabUtils mainTabUtils;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_app_main;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        EventBus.getDefault().register(this);

        TabLayout tableLayout = findViewById(R.id.app_main_tab);
        mainTabUtils = new MainTabUtils(this, getSupportFragmentManager(), R.id.app_main_content, tableLayout);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    @Override
    public void setupDatas() {
        loadTabBarListData(false);

        final List<String> deniedPermissions = PermissionsUtil.getDeniedPermissions(act(), ServiceUtils.getMustPermissions());
        if (!deniedPermissions.isEmpty()) {
            getMainHandler().postDelayed(new Runnable() {
                @Override
                public void run() {
                    new RulePermissionDialog(display(), deniedPermissions, new RulePermissionDialog.Callback() {
                        @Override
                        public void onPermissionOperationFinish() {
                            RequestHeaderUtils.tryUpdateDeviceId(act());
                            tryTakeLocationInfos();
                        }
                    }).start();
                }
            }, 1000);
        } else {
            tryTakeLocationInfos();
        }

        String homeImageDialogCommand = AppConfigContext.instance().getHomeImageDialogCommand();
        if (!TextUtils.isEmpty(homeImageDialogCommand)) {
            JumpOperationHandler.convert(homeImageDialogCommand).createRequest().setDisplay(display()).jump();
        }
    }


    private String preTabsText = null;

    private void loadTabBarListData(final boolean useCurrentTag) {
        HttpSender.get(new CommonResponsePendingCallback<List<MainTabItemResponseModel>>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                String tabbarListUrl = HttpConfig.getRealUrl(StringConstant.HTTP_APP_GET_MAIN_TABBAR_LIST);
                return new CommonRequestParams(tabbarListUrl);
            }

            @Override
            public void handleSuccess(List<MainTabItemResponseModel> resultData, int resultCode, String resultMessage) {
                if (resultData == null || resultData.isEmpty()) {
                    return;
                }

//                for (MainTabItemResponseModel tabBarBean : resultData) {
//                    LibImageLoader.preload(display(), tabBarBean.getNormalImage());
//                    LibImageLoader.preload(display(), tabBarBean.getSelectImage());
//                }

                String currTabsText = ConvertUtils.toString(resultData);
                boolean needUpdateTab = preTabsText == null || !preTabsText.equals(currTabsText);
                preTabsText = currTabsText;
                KLog.d("must Update Tabs :" + needUpdateTab);
                if (needUpdateTab) {
                    mainTabUtils.addDisplayTabList(resultData, useCurrentTag);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
//                showToast(resultMessage);
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
                KLog.d(cex);
            }
        });
    }


    //******************* location *******************

    private void tryTakeLocationInfos() {
        FirebaseUtils.fetchFirebaseMessageToken();

        PermissionTipInfo tip = PermissionTipInfo.getTip(getString(R.string.location_tip));
        PermissionsUtil.requestPermission(act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                openGpsSetting();
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                showToast(R.string.please_open_location);
            }
        }, tip, Manifest.permission.ACCESS_COARSE_LOCATION, Manifest.permission.ACCESS_FINE_LOCATION);
    }

    private void openGpsSetting() {
        if (checkGpsIsOpen()) {
            LocationUtils.startLocation();
        } else {
            new AlertDialog.Builder(this).setTitle("open GPS")
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
                            //跳转到手机原生设置页面
                            Intent intent = new Intent(Settings.ACTION_LOCATION_SOURCE_SETTINGS);
                            startActivityForResult(intent, RequestCodeType.GET_GPS_LOCATION);
                        }
                    })
                    .setCancelable(false)
                    .show();
        }
    }

    private boolean checkGpsIsOpen() {
        boolean isOpen;
        LocationManager locationManager = (LocationManager) this.getSystemService(Context.LOCATION_SERVICE);
        isOpen = locationManager.isProviderEnabled(LocationManager.GPS_PROVIDER);
        return isOpen;
    }


    //******************* lifecycler *******************

    @Override
    protected void onResume() {
        super.onResume();

        JumpOperationHandler.jumpByPushIfNeed(this);

        // trypUpdateNewVersionApp
        String updateContent = MmkvGroup.global().getUpdateContent();
        KLog.d("updateContent:" + updateContent);
        if (StringUtil.isBlank(updateContent)) {
            return;
        }
        UpdateDataModel updateDataModel = ConvertUtils.toObject(updateContent, UpdateDataModel.class);
        if (updateDataModel == null) {
            CrashReport.postCatchedException(new Throwable("updateContent:" + updateContent));
            return;
        }
        if (!compareAppVersion(BuildConfig.VERSION_NAME_SERVICE, updateDataModel.getNew_version())) {
            return;
        }
        if (updateDataModel.getHas_upgrade() == 1 && !StringUtil.isBlank(updateDataModel.getArd_url())) {
            new UpgradeAppDialog(display(), updateDataModel).show();
        }
    }

    /**
     * 两个版本号比较   true需要更新 false不需要更新  mVersion<sVersion
     *
     * @param currentVersion
     * @param newVersion
     * @return true需要更新   false不需要更新
     */
    private static boolean compareAppVersion(String currentVersion, String newVersion) {
        try {
            String[] currentVersions = currentVersion.split("\\.");
            String[] newVersions = newVersion.split("\\.");

            int length = currentVersions.length > newVersions.length ? currentVersions.length : newVersions.length;

            for (int i = 0; i < length; i++) {
                int currentVersionNumber = 0;
                int newVersionNumber = 0;
                if (currentVersions.length >= i + 1) {
                    currentVersionNumber = Integer.parseInt(currentVersions[i]);
                }
                if (newVersions.length >= i + 1) {
                    newVersionNumber = Integer.parseInt(newVersions[i]);
                }
                if (currentVersionNumber < newVersionNumber) {
                    return true;
                } else if (currentVersionNumber > newVersionNumber) {
                    return false;
                }
            }
        } catch (Exception e) {
            //避免 版本号不规范产生的异常
            e.printStackTrace();
        }
        return false;
    }


    @Override
    protected void onDestroy() {
        super.onDestroy();
        EventBus.getDefault().unregister(this);
        mainTabUtils.onDestroy();
        // 注销
        DLocationUtils.getInstance().unregister();
    }


    private long exitTime = 0;

    @Override
    public boolean onKeyDown(int keyCode, KeyEvent event) {
        if (keyCode == KeyEvent.KEYCODE_BACK && event.getAction() == KeyEvent.ACTION_DOWN) {
            if ((System.currentTimeMillis() - exitTime) > 2000) {
                Toast.makeText(act(), R.string.press_exit_again, Toast.LENGTH_SHORT).show();
                exitTime = System.currentTimeMillis();
                return true;
            } else {
                finish();
                return true;
            }
        }
        return super.onKeyDown(keyCode, event);
    }

    //******************* EventBus *******************

    public void onEventMainThread(RefreshDisplayEventModel event) {
        if (event.getType() == BaseDisplayEventModel.EVENT_REFRESH_MAIN_TAB_LIST) {
            loadTabBarListData(true);
        }
    }

    public void onEventMainThread(TabEventModel event) {
        mainTabUtils.changeCurrentDisplayTab(event);
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        if (requestCode == RequestCodeType.GET_GPS_LOCATION) {
            if (checkGpsIsOpen()) {
                LocationUtils.startLocation();
            }
        }
    }

}

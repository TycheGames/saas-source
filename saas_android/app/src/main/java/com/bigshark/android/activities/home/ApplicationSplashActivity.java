package com.bigshark.android.activities.home;

import android.content.DialogInterface;
import android.content.Intent;
import android.os.Bundle;
import android.provider.Settings;
import android.support.v7.app.AlertDialog;
import android.view.WindowManager;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.app.ConfigResponseModel;
import com.bigshark.android.core.utils.LoadingDialogUtils;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.contexts.AppConfigContext;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

/**
 * APP引导页
 * 添加权限列表dialog，权限都要有否则不能使用APP
 */
public class ApplicationSplashActivity extends DisplayBaseActivity {

    @Override
    protected int getLayoutId() {
        // 不显示系统的标题栏，保证windowBackground和界面activity_main的大小一样，显示在屏幕不会有错位（去掉这一行试试就知道效果了）
        getWindow().setFlags(WindowManager.LayoutParams.FLAG_FULLSCREEN, WindowManager.LayoutParams.FLAG_FULLSCREEN);
        return R.layout.activity_application_splash;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    @Override
    public void setupDatas() {
        loadConfig();
    }

    private ConfigResponseModel configResponseModel;

    private void loadConfig() {
        // wifi提示弹框已存在，不需要默认的加载提示弹框了
        if (dialog == null) {
            LoadingDialogUtils.showLoadingDialogWithContent(act(), "Loading network data...");
        }
        HttpSender.get(new CommonResponsePendingCallback<ConfigResponseModel>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                String getConfigUrl = getConfigUrl();
                CommonRequestParams requestParams = new CommonRequestParams(getConfigUrl);
                requestParams.addQueryStringParameter("configVersion", BuildConfig.VERSION_NAME_SERVICE);// 配置版本号
                return requestParams;
            }

            @Override
            public void handleSuccess(ConfigResponseModel resultData, int resultCode, String resultMessage) {
                KLog.d("isFront:" + isFront);
                if (isFront) {
                    gotoMain(resultData);
                } else {
                    configResponseModel = resultData;
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                display.showToast(resultMessage);

                CrashReport.postCatchedException(new Throwable("guide error:" + resultMessage));
                dismissNetworkDialog();
                showOpenNetworkDialog();
                getMainHandler().postDelayed(new Runnable() {
                    @Override
                    public void run() {
                        loadConfig();
                    }
                }, 5000);
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
                KLog.d(cex);
            }
        });
    }

    private int configUrlIndex = 0;

    private String getConfigUrl() {
        String[] baseUrls = HttpConfig.getBaseUrls();
        String getConfigUrl = baseUrls[configUrlIndex % baseUrls.length] + "/app/config";
//        KLog.d("index:" + configUrlIndex + ", url-->" + getConfigUrl);
        configUrlIndex++;
        return getConfigUrl;
    }


    private AlertDialog dialog;

    /**
     * 显示打开网络的弹窗
     */
    private void showOpenNetworkDialog() {
        dialog = new AlertDialog.Builder(act())
                .setCancelable(false)
                // 您的网络连接出现问题，请开启网络
                .setMessage("Host server is down or internet connection lost! Please Turn On Wifi Mode or Use WLAN to Access Data.")
                .setNeutralButton("refresh", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        // 假象：给用户以重新加载的按钮
                        LoadingDialogUtils.showLoadingDialog(act());
                    }
                })
                .setNegativeButton("wifi", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        startActivity(new Intent(Settings.ACTION_WIFI_SETTINGS));
                    }
                })
                .setPositiveButton("Mobile data", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        startActivity(new Intent(Settings.ACTION_DATA_ROAMING_SETTINGS));
                    }
                })
                .show();
    }

    private void dismissNetworkDialog() {
        if (dialog != null && dialog.isShowing()) {
            dialog.dismiss();
        }
        dialog = null;
    }


    private void gotoMain(ConfigResponseModel configData) {
        AppConfigContext.instance().initServiceConfig(configData);

        // TODO 引导页不做了：未来需要删除多余的图片、代码、资源
//        if (MmkvApp.instance().isFirstIn()) {
//            Intent intent = new Intent(act(), ApplicationGuiderActivity.class);
//            startActivity(intent);
//        } else {
//            Intent intent = new Intent(act(), MainActivity.class);
//            startActivity(intent);
//        }

        Intent intent = new Intent(act(), MainActivity.class);
        startActivity(intent);

        finish();
    }


    private boolean isFront = false;

    @Override
    public void onResume() {
        super.onResume();
        isFront = true;

        if (configResponseModel != null) {
            gotoMain(configResponseModel);
        }
    }

    @Override
    public void onPause() {
        super.onPause();
        isFront = false;
    }

    @Override
    protected void onDestroy() {
        dismissNetworkDialog();
        LoadingDialogUtils.hideLoadingDialog();
        super.onDestroy();
    }

}

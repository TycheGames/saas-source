package com.bigshark.android.contexts;

import android.content.Context;
import android.os.Build;
import android.support.annotation.NonNull;
import android.util.Log;
import android.webkit.WebView;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.common.browser.BrowserConfigIniter;
import com.bigshark.android.core.component.BaseApplication;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.operations.home.AnonymousReportJumpOperation;
import com.bigshark.android.jump.operations.home.HomeMainViewJumpOperation;
import com.bigshark.android.jump.operations.home.SearchJumpOperation;
import com.bigshark.android.jump.operations.home.SendRedEnvelopeJumpOperation;
import com.bigshark.android.jump.operations.home.UserMenHomePageJumpOperation;
import com.bigshark.android.jump.operations.home.UserWomenHomePageJumpOperation;
import com.bigshark.android.jump.operations.home.ViewUserPhotoJumpOperation;
import com.bigshark.android.jump.operations.main.AuthJumpOperation;
import com.bigshark.android.jump.operations.main.AuthUrlJumpOperation;
import com.bigshark.android.jump.operations.main.BrowserJumpOperation;
import com.bigshark.android.jump.operations.main.CallPhoneJumpOperation;
import com.bigshark.android.jump.operations.main.DisplayCloseJumpOperation;
import com.bigshark.android.jump.operations.main.OpenWebViewJumpOperation;
import com.bigshark.android.jump.operations.main.ShortcutBadgerJumpOperation;
import com.bigshark.android.jump.operations.main.UploadAuthDataJumpOperation;
import com.bigshark.android.jump.operations.main.WebviewTopEndViewJumpOperation;
import com.bigshark.android.jump.operations.messagecenter.EarningRemindJumpOperation;
import com.bigshark.android.jump.operations.messagecenter.EvaluateNotifyJumpOperation;
import com.bigshark.android.jump.operations.messagecenter.PushSettingJumpOperation;
import com.bigshark.android.jump.operations.messagecenter.RadioNoticeJumpOperation;
import com.bigshark.android.jump.operations.messagecenter.SystemNotifyJumpOperation;
import com.bigshark.android.jump.operations.mine.ChangePasswordJumpOperation;
import com.bigshark.android.jump.operations.mine.FindPasswordJumpOperation;
import com.bigshark.android.jump.operations.mine.PersonalJumpOperation;
import com.bigshark.android.jump.operations.mine.ProblemFeedbackJumpOperation;
import com.bigshark.android.jump.operations.mine.ReplacePhoneJumpOperation;
import com.bigshark.android.jump.operations.radiohall.CheckRegistrationJumpOperation;
import com.bigshark.android.jump.operations.radiohall.MyRadioCenterJumpOperation;
import com.bigshark.android.jump.operations.radiohall.RadioDetailsJumpOperation;
import com.bigshark.android.jump.operations.radiohall.ReleaseRadioJumpOperation;
import com.bigshark.android.jump.operations.radiohall.ViewRadioPhotoJumpOperation;
import com.bigshark.android.jump.operations.uis.ImageDialogJumpOperation;
import com.bigshark.android.jump.operations.uis.TextDialogJumpOperation;
import com.bigshark.android.jump.operations.uis.ToastJumpOperation;
import com.bigshark.android.utils.TdUtils;
import com.bigshark.android.utils.thirdsdk.AppsFlyerUtils;
import com.bigshark.android.utils.thirdsdk.LocationUtils;
import com.bigshark.android.utils.thirdsdk.shuzilianmeng.ShuZiLianMengUtils;
import com.facebook.FacebookSdk;
import com.facebook.LoggingBehavior;
import com.facebook.appevents.AppEventsLogger;
import com.liveness.dflivenesslibrary.DFProductResult;
import com.liveness.dflivenesslibrary.DFTransferResultInterface;
import com.scwang.smartrefresh.header.TaurusHeader;
import com.scwang.smartrefresh.layout.SmartRefreshLayout;
import com.scwang.smartrefresh.layout.api.DefaultRefreshHeaderCreator;
import com.scwang.smartrefresh.layout.api.RefreshHeader;
import com.scwang.smartrefresh.layout.api.RefreshLayout;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;
import com.tencent.mmkv.MMKV;
import com.umeng.analytics.MobclickAgent;
import com.umeng.commonsdk.UMConfigure;

import org.xutils.x;


public class AppContext extends BaseApplication implements DFTransferResultInterface {

    // df的人脸识别结果
    private DFProductResult mResult;

    @Override
    public void setResult(DFProductResult result) {
        mResult = result;
    }

    @Override
    public DFProductResult getResult() {
        return mResult;
    }


    static {
        BaseApplication.isDebug = BuildConfig.DEBUG;
        NavigationStatusLinearLayout.themeColorResId = R.color.theme_color;
        //定义全局刷新头部
        SmartRefreshLayout.setDefaultRefreshHeaderCreator(new DefaultRefreshHeaderCreator() {
            @NonNull
            @Override
            public RefreshHeader createRefreshHeader(@NonNull Context context, @NonNull RefreshLayout layout) {
                return new TaurusHeader(context);
            }

        });
    }

    @Override
    public void onTerminate() {
        // 程序终止的时候执行
        super.onTerminate();
    }

    @Override
    public void onCreate() {
        super.onCreate();
        String currentProcessName = ViewUtil.getCurProcessName(this);
        handleBrowser(currentProcessName);
        if (BuildConfig.APPLICATION_ID.equals(currentProcessName)) {// main process: APP的主进程
            KLog.d("main process");
            initMainProcess();
        } else if ((BuildConfig.APPLICATION_ID + ":pushcore").equals(currentProcessName)) {// pushcore process: 极光推送的进程
            KLog.d("pushcore process");
        } else {
            KLog.e("unknown process");
        }
    }

    private void handleBrowser(String processName) {
        if (!BuildConfig.APPLICATION_ID.equals(processName)) {//判断不等于默认进程名称
            if (Build.VERSION.SDK_INT >= 28) { //Android P行为变更，不可多进程使用同一个目录webView
                WebView.setDataDirectorySuffix(processName);
            }
        }

        //开启Webview 调试
        if (BuildConfig.DEBUG) {
            BrowserConfigIniter.openBrowserDebugSetting();
        }
    }

    private void initMainProcess() {
        Log.i("AppContext", "app file tag:" + BuildConfig.APP_FILE_TAG);
        x.Ext.init(this);
        MMKV.initialize(this);
        HttpConfig.configMainData(getApplicationContext());

        JumpOperationBinder.isDebug = isDebug;
        JumpOperationBinder.bindJumpOperations(
                // framework
                DisplayCloseJumpOperation.class, CallPhoneJumpOperation.class, BrowserJumpOperation.class,

                // ui tip
                ToastJumpOperation.class, TextDialogJumpOperation.class, ImageDialogJumpOperation.class,

                // webview
                OpenWebViewJumpOperation.class, WebviewTopEndViewJumpOperation.class, AuthUrlJumpOperation.class,

                HomeMainViewJumpOperation.class, ShortcutBadgerJumpOperation.class,
                AuthJumpOperation.class, UploadAuthDataJumpOperation.class,

                PersonalJumpOperation.class,
                AnonymousReportJumpOperation.class, SearchJumpOperation.class,
                SendRedEnvelopeJumpOperation.class, UserMenHomePageJumpOperation.class,
                UserWomenHomePageJumpOperation.class, ViewUserPhotoJumpOperation.class,

                EarningRemindJumpOperation.class, EvaluateNotifyJumpOperation.class,
                PushSettingJumpOperation.class, RadioNoticeJumpOperation.class,
                SystemNotifyJumpOperation.class,

                ChangePasswordJumpOperation.class, FindPasswordJumpOperation.class,
                ProblemFeedbackJumpOperation.class, ReplacePhoneJumpOperation.class,

                CheckRegistrationJumpOperation.class, MyRadioCenterJumpOperation.class,
                RadioDetailsJumpOperation.class, ReleaseRadioJumpOperation.class,
                ViewRadioPhotoJumpOperation.class


        );

        // bugly
        // 为了提高合作方的webview场景稳定性，及时发现并解决x5相关问题，当客户端发生crash等异常情况并上报给服务器时请务必带上x5内核相关信息。
        // x5内核异常信息获取接口为：com.tencent.smtt.sdk.WebView.getCrashExtraMessage(context)。以bugly日志上报为例：
        CrashReport.UserStrategy strategy = new CrashReport.UserStrategy(getApplicationContext());
        strategy.setAppChannel(BuildConfig.APP_CHANNEL_NAME); //设置渠道
        strategy.setAppVersion(BuildConfig.VERSION_NAME_SERVICE);      //App的版本
        strategy.setAppPackageName(BuildConfig.APPLICATION_ID);  //App的包名
        CrashReport.initCrashReport(getApplicationContext(), BuildConfig.BUGLY_APP_ID, true, strategy);

        // facebook
        AppEventsLogger.activateApp(this);
        FacebookSdk.setIsDebugEnabled(BuildConfig.DEBUG);
        FacebookSdk.addLoggingBehavior(LoggingBehavior.APP_EVENTS);

        // appsflyer
        AppsFlyerUtils.initConfig(this);

        //友盟统计
        UMConfigure.init(this, BuildConfig.UMENG_KEY, BuildConfig.APP_CHANNEL_NAME, UMConfigure.DEVICE_TYPE_PHONE, "");
        UMConfigure. setProcessEvent(true);
        MobclickAgent.setPageCollectionMode(MobclickAgent.PageMode.MANUAL);
        UMConfigure.setLogEnabled(BuildConfig.DEBUG);

        // 数盟
        ShuZiLianMengUtils.configSzlmeng(this);
        ShuZiLianMengUtils.setSdkData(BuildConfig.VERSION_NAME_SERVICE);
        ShuZiLianMengUtils.getQueryId(getApplicationContext(), new ShuZiLianMengUtils.Handler() {
            @Override
            public void handler(String queryId, boolean isOnlineData) {
                RequestHeaderUtils.updateShuZiLm(queryId);
            }
        });

        TdUtils.initTd(getApplicationContext(), BuildConfig.DEBUG, new TdUtils.Handler() {
            @Override
            public void handler(String blackBoxText) {
                RequestHeaderUtils.setTdBlackboxText(blackBoxText);
            }
        });

        LocationUtils.initConfig(this);
    }

}

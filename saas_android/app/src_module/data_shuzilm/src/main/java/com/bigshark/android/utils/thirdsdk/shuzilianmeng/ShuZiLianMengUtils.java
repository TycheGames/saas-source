package com.bigshark.android.utils.thirdsdk.shuzilianmeng;

import android.content.Context;
import android.os.HandlerThread;
import android.support.annotation.NonNull;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import cn.shuzilm.core.Listener;
import cn.shuzilm.core.Main;

/**
 * Created by Administrator on 2017/7/11.
 * 数字联盟
 */
public class ShuZiLianMengUtils {

    private static final int RETRY_COUNT = 5;// 重试5次


    private static HandlerThread mHandlerThread;

    private static android.os.Handler mHandler;


    public static void configSzlmeng(final Context context) {
        Main.init(context.getApplicationContext(), BuildConfig.SHUZILIANMENG_APIKEY);
        mHandlerThread = new HandlerThread(StringConstant.SHUZILM_HANDLER_THREAD_NAME);
        mHandlerThread.start();
        mHandler = new android.os.Handler(mHandlerThread.getLooper());
    }


    public static void setSdkData(String versionNameInner) {
        Main.setData("versionNameInner", versionNameInner);
    }

    //****************** query id ******************

    public static void getQueryId(final Context context, @NonNull final Handler callback) {
        final String queryId = MmkvGroup.szlmeng().getQueryId();
        // 初始化时即设置数盟的queryId
        if (queryId != null && !queryId.isEmpty()) {
            KLog.d("queryId:" + queryId);
            callback.handler(queryId, false);
        }

        executeQuery(context, callback, 0);
    }

    private static void executeQuery(final Context context, @NonNull final Handler callback, final int currQueryCount) {
        mHandler.postDelayed(new Runnable() {
            @Override
            public void run() {
                KLog.d("handle shuzilianmeng msg...");
                realExecuteQuery(context, callback, currQueryCount);
            }
        }, currQueryCount * 1000 * 20);
    }

    private static void realExecuteQuery(Context context, @NonNull Handler callback, int currQueryCount) {
        try {
            final long beforeTime = System.currentTimeMillis();
            Main.getQueryID(context, BuildConfig.APP_FILE_TAG, null, Main.MAIN_DU_ASYNCHRONOUS, new Listener() {
                @Override
                public void handler(String queryId) {
                    KLog.d("queryId:" + queryId);
                    long afterTime = System.currentTimeMillis();
                    long times = afterTime - beforeTime;
                    KLog.d("duration time:" + times + "ms");
                    if (times >= 60 * 1000) {// 大于等于1分钟
                        CrashReport.postCatchedException(new Throwable("szlm device_id，time more than 60s"));
                    }

                    if (queryId != null && !queryId.isEmpty()) {
                        MmkvGroup.szlmeng().encodeQueryId(queryId);
                        callback.handler(queryId, true);
                        return;
                    }

                    // 数盟ID获取为空，进行重试
                    retryQuery(context, callback, currQueryCount, null);
                }
            });
        } catch (Exception e) {
            e.printStackTrace();
            retryQuery(context, callback, currQueryCount, e);
        }
    }

    private static void retryQuery(Context context, @NonNull Handler callback, int currentQueryCount, Exception e) {
        KLog.d("currSearchCount:" + currentQueryCount);
        if (currentQueryCount > RETRY_COUNT) {
            CrashReport.postCatchedException(new Throwable("szlm device_id，queryId is empty finally", e));
            return;
        }

        CrashReport.postCatchedException(new Throwable("szlm device_id，queryId is empty", e));
        currentQueryCount++;
        executeQuery(context, callback, currentQueryCount);
    }


    /**
     * 退出时释放资源
     */
    public static void onDestoy() {
        if (mHandlerThread == null) {
            return;
        }
        mHandlerThread.quit();
        mHandlerThread = null;
    }


    /**
     * 数字联盟 getDeviceid 回调
     */
    public interface Handler {
        /**
         * @param queryId      数盟ID
         * @param isOnlineData 是否为线上获取的数盟id，false为本地保存上一次获取的数盟ID
         */
        void handler(String queryId, boolean isOnlineData);
    }
}

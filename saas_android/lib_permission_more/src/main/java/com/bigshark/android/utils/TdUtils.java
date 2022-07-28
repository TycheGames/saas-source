package com.bigshark.android.utils;

import android.content.Context;

import com.socks.library.KLog;

import java.util.HashMap;
import java.util.Map;

import cn.tongdun.android.shell.FMAgent;
import cn.tongdun.android.shell.inter.FMCallback;

/**
 * @author Administrator.
 * @date 2019/2/19 9:41.
 * @email 690931210@qq.com
 * @desc 同盾初始化
 */
public class TdUtils {

    // ******************** 同盾的配置：之后需要设置到gradle中 ********************

    public static final String TD_URL_TEST = "https://idfptest.tongdun.net";//测试
    public static final String TD_URL_OLINE = "https://idfp.tongdun.net";//线上
    public static final long TD_TIMES = 480000;

    private static String blackBoxText;
    private static long lastCallbackTime = 0;


    public static void initTd(final Context context, boolean debug, final Handler handler) {
        if (!"".equals(blackBoxText) && System.currentTimeMillis() - lastCallbackTime < TD_TIMES) {
            if (handler != null) {
                handler.handler(blackBoxText);
            }
            return;
        }

        try {
            Map<String, Object> optionMap = new HashMap<>(2);

            optionMap.put(FMAgent.OPTION_DOMAIN, debug ? TD_URL_TEST : TD_URL_OLINE);
            optionMap.put(FMAgent.OPTION_BLACKBOX_MAXSIZE, 5 * 1024);

            FMAgent.initWithCallback(context.getApplicationContext(), FMAgent.ENV_PRODUCTION, optionMap, new FMCallback() {
                @Override
                public void onEvent(String blackbox) {
                    // 注意这里不是主线程 请不要在这个函数里进行ui操作，否则可能会出现崩溃
                    blackBoxText = blackbox;
                    KLog.d("callback_blackbox", "success:" + blackbox);
                    //sendTongDun();
                    if (handler != null) {
                        handler.handler(blackBoxText);
                    }
                    lastCallbackTime = System.currentTimeMillis();
                }
            });
        } catch (Exception e) {
            KLog.d("callback_blackbox", "error:" + e.getMessage());
        }
    }

    public interface Handler {
        void handler(String blackBoxText);
    }
}

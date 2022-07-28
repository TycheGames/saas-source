package com.bigshark.android.utils;

import android.content.Context;

/**
 * @author Administrator.
 * @date 2019/2/19 9:41.
 * @email 690931210@qq.com
 * @desc 同盾初始化
 */

public class TdUtils {
    // google play包传控制，现阶段
    private static final String tdBlackBox = "";

    public static void initTd(final Context context, boolean isDebug, final Handler handler) {
        if (handler == null) {
            return;
        }

        handler.handler(tdBlackBox);
    }

    public interface Handler {
        void handler(String blackBoxText);
    }
}

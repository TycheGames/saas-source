package com.bigshark.android.jump;

import android.content.Intent;
import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.jump.base.JumpModel;
import com.socks.library.KLog;

/**
 * 页面路由
 *
 * @author Administrator
 * @date 2017/7/12
 */
public class JumpOperationHandler {


    //****************** 处理ViewRouter的请求 ******************

    @NonNull
    public static JumpModel convert(String cmdContent) {
        return JumpOperationBinder.convert(cmdContent);
    }


    //****************** 处理onActivityResult ******************

    public static void detached(@NonNull IDisplay display) {
        JumpOperationCallbackHandler.detachedPage(display);
    }

    public static boolean onActivityResult(@NonNull IDisplay display, int requestCode, int resultCode, Intent data) {
        return JumpOperationCallbackHandler.onActivityResult(display, requestCode, resultCode, data);
    }

    //****************** 处理onActivityResult ******************
    public static boolean onRequestPermissionsResult(@NonNull IDisplay display, int requestCode, String[] permissions, int[] grantResults) {
        return JumpOperationCallbackHandler.onRequestPermissionsResult(display, requestCode, permissions, grantResults);
    }

    //****************** 处理短信跳转与推送跳转 ******************

    /**
     * 推送跳转的信息
     */
    private static String jumpOperationData = null;

    public static void setJumpOperationData(String jumpDataStr) {
        jumpOperationData = jumpDataStr;
        if (jumpOperationData == null) {
            KLog.d("jumpOperationData is null...");
        } else {
            KLog.d("jumpOperationData=" + jumpOperationData);
        }
    }

    public static void jumpByPushIfNeed(IDisplay display) {
        if (jumpOperationData == null) {
            // 没有推送跳转
            return;
        }

        String convertData = jumpOperationData;
        // 清除推送跳转的数据
        jumpOperationData = null;

        new JumpOperationRequest(convertData).setDisplay(display).jump();
    }

}

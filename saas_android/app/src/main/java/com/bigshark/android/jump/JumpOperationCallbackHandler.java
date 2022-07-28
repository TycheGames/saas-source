package com.bigshark.android.jump;

import android.content.Intent;
import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.jump.base.JumpOperation;

import java.util.HashMap;
import java.util.LinkedList;
import java.util.List;

/**
 * view jump 回调处理助手
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class JumpOperationCallbackHandler {

    private static final HashMap<IDisplay, LinkedList<JumpOperation>> CALLBACK_CACHES = new HashMap<>(10);


    static void addJump(IDisplay display, JumpOperation operation) {
        if (display == null || operation == null) {
            return;
        }

        if (CALLBACK_CACHES.containsKey(display)) {
            CALLBACK_CACHES.get(display).addFirst(operation);
        } else {
            LinkedList<JumpOperation> builders = new LinkedList<>();
            builders.addFirst(operation);
            CALLBACK_CACHES.put(display, builders);
        }
    }

    private static void removeJumpOperation(@NonNull JumpOperation operation) {
        IDisplay display = operation.request.getDisplay();
        if (!CALLBACK_CACHES.containsKey(display)) {
            return;
        }
        List<JumpOperation> commands = CALLBACK_CACHES.get(display);
        commands.remove(operation);
    }

    static void detachedPage(@NonNull IDisplay display) {
        if (CALLBACK_CACHES.containsKey(display)) {
            CALLBACK_CACHES.remove(display);
        }
    }

    static boolean onActivityResult(@NonNull IDisplay display, int requestCode, int resultCode, Intent data) {
        if (!CALLBACK_CACHES.containsKey(display)) {
            return false;
        }

        boolean isHandled = false;
        for (JumpOperation operation : CALLBACK_CACHES.get(display)) {
            isHandled = operation.onActivityResult(requestCode, resultCode, data);
            if (isHandled) {
                // 处理了改指令，所以可以删除了
                removeJumpOperation(operation);
                break;
            }
        }
        return isHandled;
    }

    static boolean onRequestPermissionsResult(@NonNull IDisplay display, int requestCode, String[] permissions, int[] grantResults) {
        if (!CALLBACK_CACHES.containsKey(display)) {
            return false;
        }

        boolean isHandled = false;
        for (JumpOperation jumpOperation : CALLBACK_CACHES.get(display)) {
            isHandled = jumpOperation.onRequestPermissionsResult(requestCode, permissions, grantResults);
            if (isHandled) {
                break;
            }
        }
        return isHandled;
    }
}

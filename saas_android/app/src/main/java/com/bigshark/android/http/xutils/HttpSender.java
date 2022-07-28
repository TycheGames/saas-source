package com.bigshark.android.http.xutils;

import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;

import org.xutils.common.Callback;
import org.xutils.http.HttpMethod;
import org.xutils.x;

import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;

/**
 * 请求发送
 */
public class HttpSender {

    private static HashMap<IDisplay, List<Callback.Cancelable>> CANCELABLES = new HashMap<>(8);

    /**
     * 异步GET请求
     */
    public static <T> Callback.Cancelable get(@NonNull CommonResponseCallback<T> callback) {
        return requestInner(HttpMethod.GET, callback);
    }

    /**
     * 异步POST请求
     */
    public static <T> Callback.Cancelable post(@NonNull CommonResponseCallback<T> callback) {
        return requestInner(HttpMethod.POST, callback);
    }

    /**
     * 异步请求
     */
    public static <T> Callback.Cancelable request(HttpMethod method, @NonNull CommonResponseCallback<T> callback) {
        return requestInner(method, callback);
    }

    private static <T> Callback.Cancelable requestInner(HttpMethod method, @NonNull CommonResponseCallback<T> callback) {
        CommonRequestParams entity = callback.createRequestParams();
        callback.onStarted(method, entity);

        Callback.Cancelable cancelable = x.http().request(method, entity, callback);
        callback.setCancelable(cancelable);

        storeCancelable(callback, cancelable);
        return cancelable;
    }


    // 任务取消

    private static <T> void storeCancelable(@NonNull CommonResponseCallback<T> callback, Callback.Cancelable cancelable) {
        if (!CANCELABLES.containsKey(callback.display)) {
            CANCELABLES.put(callback.display, new ArrayList<Callback.Cancelable>(4));
        }

        CANCELABLES.get(callback.display).add(cancelable);
    }


    public static void cancel(@NonNull IDisplay display) {
//        KLog.d(display);
        List<Callback.Cancelable> cancelables = CANCELABLES.remove(display);
//        KLog.d(cancelables);

        if (cancelables == null) {
            return;
        }

        for (Callback.Cancelable cancelable : cancelables) {
//            KLog.d(cancelable);
            cancelable.cancel();
        }
        cancelables.clear();
    }

    public static void cancel(@NonNull Callback.Cancelable cancelable) {
//        KLog.d(cancelable);
        for (List<Callback.Cancelable> cancelables : CANCELABLES.values()) {
            if (cancelables != null && cancelables.remove(cancelable)) {
                break;
            }
        }
        cancelable.cancel();
    }


//        CommonRequestParams requestParams = new CommonRequestParams(getConfigUrl);
//        requestParams.addQueryStringParameter("configVersion", BuildConfig.VERSION_NAME_SERVICE);
//
//        KLog.d();
//        Callback.Cancelable cancelable = x.http().get(requestParams, new CommonResponseCallback<ConfigResData>(display) {
//            @Override
//            public void handleUi(boolean isStart) {
//                KLog.d(isStart);
//            }
//
//            @Override
//            public void handleSuccess(ConfigResData resultData, int resultCode, String resultMessage) {
//                KLog.d(resultCode);
//                KLog.d(resultMessage);
//                KLog.json(ConvertUtils.toString(resultData));
//                KLog.trace();
//            }
//
//            @Override
//            public void onError(Throwable ex, boolean isOnCallback) {
//                KLog.d("isOnCallback:" + isOnCallback + ", ex:" + ex);
//                display.showToast(ex.getMessage());
//            }
//
//            @Override
//            public void onCancelled(CancelledException cex) {
//                KLog.d(cex);
//            }
//        });


}

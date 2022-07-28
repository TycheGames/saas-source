package com.bigshark.android.nativejs;

import android.content.ClipData;
import android.content.Context;
import android.support.annotation.NonNull;
import android.webkit.JavascriptInterface;

import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.component.browser.INativeJavascriptInterfaceObj;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.core.xutilshttp.RequestHeaderUtils;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.jump.model.main.CallPhoneJumpModel;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.services.ServiceUtils;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.LocationUtils;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.socks.library.KLog;

import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * webview中的绑定对象
 *
 * @author Administrator
 */
public class BrowserNativeJavascriptInterfaceObjectImpl implements INativeJavascriptInterfaceObj {

    private final IBrowserWebView webView;

    public BrowserNativeJavascriptInterfaceObjectImpl(IBrowserWebView webView) {
        this.webView = webView;
    }

    @Override
    @JavascriptInterface
    public void copyTextMethod(String text, String tip) {
//        CopyTextBean bean = ConvertUtils.toObject(text, CopyTextBean.class);
        copyText2Clipboard(webView.getPage().act(), text);
        webView.getPage().showToast(tip);
    }

    private static void copyText2Clipboard(Context context, String text) {
        android.content.ClipboardManager clipboardManager = (android.content.ClipboardManager) context.getSystemService(Context.CLIPBOARD_SERVICE);
        if (clipboardManager != null) {
            clipboardManager.setPrimaryClip(ClipData.newPlainText("text", text));
        }
    }


    @Override
    @JavascriptInterface
    public void callPhoneMethod(final String telephoneNumber) {
        final CallPhoneJumpModel data = new CallPhoneJumpModel();
        data.setPath(StringConstant.JUMP_APP_CALL_PHONE);
        data.setTele(telephoneNumber);
        webView.post(new Runnable() {
            @Override
            public void run() {
                data.createRequest().setDisplay(webView.getPage()).jump();
            }
        });
    }

    @Override
    @JavascriptInterface
    public void returnNativeMethod(String typeStr) {
        KLog.w("js-param:" + typeStr);
        final JumpModel jumpModel = JumpOperationHandler.convert(typeStr);
        webView.post(new Runnable() {
            @Override
            public void run() {
                jumpModel.createRequest().setDisplay(webView.getPage()).jump();
            }
        });
    }


    //<editor-fold desc="页面显示状态回调">

    // H5 页面显示在前台 回调函数
    private String onShowCallback;

    // H5 页面显示在前台
    @Override
    @JavascriptInterface
    public void onShow(String callback) {
        onShowCallback = callback;
    }

    @Override
    public String getOnShowCallback() {
        return onShowCallback;
    }

    // H5 页面显示在前台 回调函数
    private String onHideCallback;

    // H5 页面显示在前台
    @Override
    @JavascriptInterface
    public void onHide(String callback) {
        onHideCallback = callback;
    }

    @Override
    public String getOnHideCallback() {
        return onHideCallback;
    }

    //</editor-fold>


    //<editor-fold desc="APP信息">

    @Override
    @Deprecated
    @JavascriptInterface
    public String getHeaders() {
        return getAppAttributes();
    }

    /**
     * 给h5获取APP配置信息的json字符串
     */
    @Override
    @JavascriptInterface
    public String getAppAttributes() {
        return RequestHeaderUtils.getAppAttributes();
    }

    /**
     * 放到请求头上的json，要转为对象，设置到请求头上
     * 其中appInfo为加密字符串
     */
    @Override
    @JavascriptInterface
    public String getHeadersContent() {
        return RequestHeaderUtils.getHeadersContent();
    }

    @Override
    @JavascriptInterface
    public String getDeviceId() {
        return ViewUtil.getDeviceId(webView.getPage().act());
    }

    //</editor-fold>


    @Override
    @JavascriptInterface
    public void reportAppsFlyerTrackEvent(String eventName, String eventValueText) {
        Map<String, String> extras = new HashMap<>();

        Map<String, Object> eventValueExtras = ConvertUtils.toMap(eventValueText);
        if (eventValueExtras == null) {
            extras = new HashMap<>(1);
        } else {
            for (Map.Entry<String, Object> entry : eventValueExtras.entrySet()) {
                extras.put(entry.getKey(), String.valueOf(entry.getValue()));
            }
        }

        MaiDianUploaderUtils.Builder.create(webView.getPage()).setEventName(eventName).addEventValues(extras).build();
    }


    //<editor-fold desc="借款申请页的方法">

    /**
     * 必须的权限是否都已申请
     */
    @Override
    @JavascriptInterface
    public boolean mustPermissionsHaveBeenApplied() {
        List<String> deniedPermissions = PermissionsUtil.getDeniedPermissions(webView.getPage().act(), ServiceUtils.getMustPermissions());
        return deniedPermissions.isEmpty();
    }

    /**
     * 申请必须权限
     */
    @Override
    @JavascriptInterface
    public void applyMustPermissions(final String callbackFunctionName) {
        List<String> deniedPermissions = PermissionsUtil.getDeniedPermissions(webView.getPage().act(), ServiceUtils.getMustPermissions());
        PermissionTipInfo tip = PermissionTipInfo.getTip(ServiceUtils.getMustPermissionTips());
        PermissionsUtil.requestPermission(webView.getPage().act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                webView.loadUrl("javascript:" + callbackFunctionName + "(true)");
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                webView.getPage().showToast(ServiceUtils.getMustPermissionDeniedTip());
                webView.loadUrl("javascript:" + callbackFunctionName + "(false)");
            }
        }, tip, deniedPermissions.toArray(new String[0]));
    }

    /**
     * 上传数据
     */
    @Override
    @JavascriptInterface
    public void uploadDataAfterApplyMustPermissions() {
        LocationUtils.reloadLocation(webView.getPage());
        ServiceUtils.reportServiceDatas(webView.getPage(), false);
    }

    //</editor-fold>

}
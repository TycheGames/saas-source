package com.bigshark.android.jump;

import android.app.Activity;
import android.content.Intent;
import android.support.annotation.NonNull;
import android.util.Log;

import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.base.UnexpectedJumpOperation;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

/**
 * ViewRouter功能的参数设置
 * builder模式
 *
 * @author Administrator
 */
public class JumpOperationRequest<Data extends JumpModel> {
    private final Data data;
    /**
     * 附属于fragment还是activity
     * 全部的fragment都应该是v4下的
     */
    private IDisplay display;


    //*************** 生成操作指令 ***************

    public JumpOperationRequest(String cmdContent) {
        this.data = (Data) JumpOperationBinder.convert(cmdContent);
    }

    public JumpOperationRequest(@NonNull Data data) {
        this.data = data;
    }

    public JumpOperationRequest setDisplay(@NonNull IDisplay display) {
        this.display = display;
        return this;
    }


    public void jump() {
        Log.d(JumpOperationRequest.class.getName(), "jump command data:" + ConvertUtils.toString(data));
        // checkParams
        if (data == null) {
            String message = "跳转数据为null或空字符串等，不能转换为跳转对象";
            KLog.d(message);
            CrashReport.postCatchedException(new Throwable(message));
            return;
        }

        JumpOperation jumpOperation = JumpOperationBinder.findJump(this);
        // 没有找到对应的Command，所以执行不了该指令，直接return
        if (jumpOperation instanceof UnexpectedJumpOperation) {
            return;
        }

        JumpOperationCallbackHandler.addJump(display, jumpOperation);

        try {
            jumpOperation.start();

            if (getData().isFinishPage()) {
                display.act().finish();
            }
        } catch (Exception e) {
            e.printStackTrace();
            showToast("数据参数错误！");
            CrashReport.postCatchedException(new Throwable(e));
        }
    }


    //*************** 指令内部的数据及通用功能 ***************

    public Data getData() {
        return data;
    }

    public IDisplay getDisplay() {
        return display;
    }


    public Activity activity() {
        return display.act();
    }

    public void startActivity(Intent intent) {
        display.startActivity(intent);
    }

    public void startActivityForResult(Intent intent, int requestCode) {
        display.startActivityForResult(intent, requestCode);
    }

    public boolean isWebViewPage() {
        return display instanceof IBrowserWebView.BrowserPage;
    }

    public IBrowserWebView.BrowserPage getWebViewPage() {
        return (IBrowserWebView.BrowserPage) display;
    }

    public void showToast(String message) {
        display.showToast(message);
    }


}
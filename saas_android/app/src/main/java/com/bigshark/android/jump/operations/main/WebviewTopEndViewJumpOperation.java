package com.bigshark.android.jump.operations.main;

import android.view.View;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.WebviewTopLeftButtonJumpModel;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;

/**
 * webview页面中，左上角的点击功能
 *
 * @author Administrator
 * @date 2017/9/15
 */
public class WebviewTopEndViewJumpOperation extends JumpOperation<WebviewTopLeftButtonJumpModel> {
    static {
        JumpOperationBinder.bind(
                WebviewTopEndViewJumpOperation.class,
                WebviewTopLeftButtonJumpModel.class,
                // webview页面中，左上角的点击功能
                StringConstant.JUMP_H5_TOP_LEFT_BUTTON
        );
    }

    @Override
    public void start() {
        if (!request.isWebViewPage()) {
            return;
        }

        final WebviewTopLeftButtonJumpModel.ClickBean clickData = request.getData().getData();
        if (clickData == null) {
            return;
        }

        final IBrowserWebView.BrowserPage webViewPage = request.getWebViewPage();
        webViewPage.getTitleView().setLeftClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (clickData.isControlRestoreDefault()) {
                    WebviewTopLeftButtonJumpModel data = new WebviewTopLeftButtonJumpModel();
                    WebviewTopLeftButtonJumpModel.ClickBean clickBean = new WebviewTopLeftButtonJumpModel.ClickBean();
                    clickBean.setRestoreDefaultFunction(true);
                    data.setData(clickBean);
                    data.createRequest().setDisplay(request.getDisplay()).jump();
                }

                String callbck = clickData.getCallback();
                if (!StringUtil.isBlank(callbck)) {
                    String jsMethod = "javascript:" + callbck + "()";
                    request.getWebViewPage().getWebView().loadUrl(jsMethod);
                    return;
                }

                if (clickData.isRestoreDefaultFunction()) {
                    if (webViewPage.getWebView().canGoBack()) {
                        webViewPage.getWebView().goBack();
                        return;
                    }
                    webViewPage.act().finish();
                    return;
                }
                JumpModel data = JumpOperationHandler.convert(clickData.getClick());
                if (StringConstant.JUMP_H5_TOP_LEFT_BUTTON.equals(data.getPath())) {
                    // 指令不能再是相同的指令，否则就能无线循环了
                    return;
                }
                data.createRequest().setDisplay(request.getDisplay()).jump();
            }
        });
    }

}

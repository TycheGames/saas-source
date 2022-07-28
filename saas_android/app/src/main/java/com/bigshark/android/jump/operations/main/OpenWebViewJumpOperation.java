package com.bigshark.android.jump.operations.main;

import android.webkit.URLUtil;
import android.widget.Toast;

import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.main.UrlJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到网页的
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class OpenWebViewJumpOperation extends JumpOperation<UrlJumpModel> {
    static {
        JumpOperationBinder.bind(
                OpenWebViewJumpOperation.class,
                UrlJumpModel.class,
                //打开H5
                StringConstant.JUMP_H5_URL
        );
    }

    @Override
    public void start() {
        gotoWebView(request.getData().getUrl());
    }

    /**
     * 打开H5
     */
    private void gotoWebView(String url) {
        if (!URLUtil.isNetworkUrl(url)) {
            Toast.makeText(request.getDisplay().context(), "data error", Toast.LENGTH_SHORT).show();
            return;
        }
        BrowserActivity.goIntent(request.getDisplay().act(), url);
    }
}

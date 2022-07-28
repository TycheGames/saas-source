package com.bigshark.android.jump.operations.main;

import android.webkit.URLUtil;
import android.widget.Toast;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.AuthUrlJumpModel;
import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到网页的
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class AuthUrlJumpOperation extends JumpOperation<AuthUrlJumpModel> {
    static {
        JumpOperationBinder.bind(
                AuthUrlJumpOperation.class,
                AuthUrlJumpModel.class,
                //认证流程打开Url
                StringConstant.JUMP_AUTHENTICATE_URL
        );
    }

    @Override
    public void start() {
        String url = request.getData().getUrl();
        String authUrl = url + (url.contains("?") ? "&" : "?") +
                "totalNum=" + request.getData().getTotalNum() +
                "&currentPosition=" + request.getData().getCurrentPosition() +
                "&isCheck=" + request.getData().isCheck();

        gotoWebView(authUrl);
    }

    /**
     * 打开H5
     */
    private void gotoWebView(String url) {
        if (!URLUtil.isNetworkUrl(url)) {
            Toast.makeText(request.getDisplay().context(), "data error...", Toast.LENGTH_SHORT).show();
            return;
        }
        BrowserActivity.goIntent(request.getDisplay().act(), url);
    }
}

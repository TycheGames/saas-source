package com.bigshark.android.jump.operations.main;

import android.content.Intent;
import android.net.Uri;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.UrlJumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

public class BrowserJumpOperation extends JumpOperation<UrlJumpModel> {
    static {
        JumpOperationBinder.bind(
                BrowserJumpOperation.class,
                UrlJumpModel.class,
                // 打开浏览器
                StringConstant.JUMP_APP_OPEN_BROWSER
        );
    }

    @Override
    public void start() {
        Intent intent = new Intent();
        intent.setAction("android.intent.action.VIEW");
        intent.setData(Uri.parse(request.getData().getUrl()));
        request.startActivity(intent);
    }

}

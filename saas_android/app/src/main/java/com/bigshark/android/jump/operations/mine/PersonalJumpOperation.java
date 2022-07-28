package com.bigshark.android.jump.operations.mine;

import android.content.Intent;

import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.activities.mine.UserEnterActivity;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.activities.mine.ResetPasswordActivity;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

/**
 * 最简单的跳转的功能
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class PersonalJumpOperation extends JumpOperation<JumpModel> {
    static {
        JumpOperationBinder.bind(
                PersonalJumpOperation.class,
                JumpModel.class,
                // 用户登陆
                StringConstant.JUMP_USER_LOGIN,
                // 忘记密码
                StringConstant.JUMP_USER_FORGET_PASSWORD,
                //退出登录
                StringConstant.JUMP_USER_LOGOUT
        );
    }

    @Override
    public void start() {
        switch (path()) {
            // 忘记密码
            case StringConstant.JUMP_USER_FORGET_PASSWORD:
                request.startActivity(new Intent(request.activity(), ResetPasswordActivity.class));
                break;
            // 用户登陆
            case StringConstant.JUMP_USER_LOGIN:
                UserEnterActivity.createIntent(request.getDisplay().act());
                break;
            //退出登录
            case StringConstant.JUMP_USER_LOGOUT:
                //doLogout
                HttpSender.get(new CommonResponsePendingCallback<String>(request.getDisplay()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 登出
                        String logoutUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_LOGOUT);
                        return new CommonRequestParams(logoutUrl);
                    }

                    @Override
                    public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                        PersonalContext.instance().doLogout(request.activity().getApplicationContext());
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        request.showToast(resultMessage);
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        super.onCancelled(cex);
                        request.showToast(cex.getMessage());
                    }
                });
                break;
            default:
                break;
        }
    }

}

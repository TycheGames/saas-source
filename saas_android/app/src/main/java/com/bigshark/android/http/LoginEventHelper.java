package com.bigshark.android.http;

import android.app.Activity;

import com.bigshark.android.core.AppManager;
import com.bigshark.android.dialog.UserLoginDialog;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.activities.mine.UserEnterActivity;
import com.bigshark.android.activities.mine.OtpLoginActivity;
import com.bigshark.android.activities.mine.RegisterActivity;
import com.bigshark.android.activities.home.ApplicationEnterActivity;
import com.bigshark.android.activities.mine.ResetPasswordActivity;
import com.socks.library.KLog;

/**
 * Created by Administrator on 2017/5/26.
 * 重新登录
 */
public class LoginEventHelper {

    private UserLoginDialog userLoginDialog;

    public LoginEventHelper() {
    }

    public void sendEvent() {
        if (AppManager.getInstance().getActivities().isEmpty()) {
            return;
        }

        int endIndex = AppManager.getInstance().getActivities().size() - 1;
        final Activity currentActivity = AppManager.getInstance().getActivities().get(endIndex);

        // isNotNeedShowDialog
        if (currentActivity instanceof ApplicationEnterActivity
                || currentActivity instanceof UserEnterActivity
                || currentActivity instanceof OtpLoginActivity
                || currentActivity instanceof RegisterActivity
                || currentActivity instanceof ResetPasswordActivity
        ) {
//            KLog.d("");
            return;
        }

        if (userLoginDialog != null && userLoginDialog.isShowing()) {
//            KLog.d("");
            return;
        }

        userLoginDialog = new UserLoginDialog(currentActivity, new UserLoginDialog.Callback() {
            @Override
            public void onOkClick() {
                PersonalContext.instance().toLogin(currentActivity);
                userLoginDialog = null;
            }

            @Override
            public void onCancelClick() {
                userLoginDialog = null;
            }
        });
        userLoginDialog.show();
    }

}

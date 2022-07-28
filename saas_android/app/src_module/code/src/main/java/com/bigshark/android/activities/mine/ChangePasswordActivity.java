package com.bigshark.android.activities.mine;

import android.os.Bundle;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.gyf.immersionbar.ImmersionBar;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 修改密码
 */
public class ChangePasswordActivity extends DisplayBaseActivity {

    private ImageView iv_titlebar_left_back;
    private TextView tv_confirm_replace;
    private EditText et_password, et_password_confirm;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_change_password;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        iv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        tv_confirm_replace = findViewById(R.id.tv_confirm_replace);
        et_password = findViewById(R.id.et_password);
        et_password_confirm = findViewById(R.id.et_password_confirm);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {

    }

    private void requestReplace() {
//        UpdatePasswordRequestBean userLoginRequestBean = new UpdatePasswordRequestBean();
//        userLoginRequestBean.setCurrent_pwd(et_password.getText().toString().trim());
//        userLoginRequestBean.setNew_pwd(et_password_confirm.getText().toString().trim());
//        HttpApi.app().updatePassword(this, userLoginRequestBean, new HttpCallback<String>() {
//
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                showToast(message);
//                finish();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showToast(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_UPDATEPASSWORD_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("current_pwd", et_password.getText().toString().trim());
                requestParams.addBodyParameter("new_pwd", et_password_confirm.getText().toString().trim());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                showToast(resultMessage);
                finish();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    private boolean checkEditText() {
        if (!StringUtil.isLoginPassword(et_password.getText().toString().trim())) {
            showToast("Please enter your current login password");
            return false;
        } else if (!StringUtil.isLoginPassword(et_password_confirm.getText().toString().trim())) {
            showToast("Please set a new password");
            return false;
        }
        return true;
    }

    @OnClick({R.id.iv_titlebar_left_back, R.id.tv_confirm_replace})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            case R.id.tv_confirm_replace:
                if (checkEditText()) {
                    requestReplace();
                }
                break;
            default:
                break;
        }
    }
}

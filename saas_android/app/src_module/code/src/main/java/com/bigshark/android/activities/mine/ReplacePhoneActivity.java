package com.bigshark.android.activities.mine;

import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.view.View;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.widget.ClearEditText;
import com.gyf.immersionbar.ImmersionBar;

import butterknife.ButterKnife;
import butterknife.OnClick;

//import com.bigshark.android.activities.usercenter.param.GetCodeRequestBean;
//import com.bigshark.android.activities.usercenter.param.UserLoginRequestBean;

/**
 * 更换手机号
 */
public class ReplacePhoneActivity extends DisplayBaseActivity {

    private ImageView iv_titlebar_left_back;
    private TextView tv_confirm_replace, tv_replacephone_get_code, tv_agreement;
    private ClearEditText et_replace_phone;
    private EditText et_code, et_replacephone_password;
    private CheckBox cb_replacephone_agreement;
    private int curTime;
    private static final int INTERVAL = 1;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_replace_phone;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        iv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        tv_confirm_replace = findViewById(R.id.tv_confirm_replace);
        et_replace_phone = findViewById(R.id.et_replace_phone);
        et_code = findViewById(R.id.et_code);
        tv_replacephone_get_code = findViewById(R.id.tv_replacephone_get_code);
        et_replacephone_password = findViewById(R.id.et_replacephone_password);
        cb_replacephone_agreement = findViewById(R.id.cb_replacephone_agreement);
        tv_agreement = findViewById(R.id.tv_agreement);

    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {

    }

    private void requestReplace() {
//        UserLoginRequestBean userLoginRequestBean = new UserLoginRequestBean();
//        userLoginRequestBean.setPhone(et_replace_phone.getText().toString().trim());
//        userLoginRequestBean.setPassword(et_replacephone_password.getText().toString().trim());
//        userLoginRequestBean.setCode(et_code.getText().toString().trim());
//        HttpApi.app().updatePhone(this, userLoginRequestBean, new HttpCallback<PhoneModel>() {
//            @Override
//            public void onSuccess(int code, String message, PhoneModel data) {
//                ToastUtil.showToast(ReplacePhoneActivity.this, message);
//                finish();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(ReplacePhoneActivity.this, error.getErrMessage());
//            }
//        });

    }

    private boolean checkEditText() {
        if (!StringUtil.isMobileNO(et_replace_phone.getText().toString().trim())) {
            showToast("Please enter the correct phone number");
            return false;
        } else if (StringUtil.isBlank(et_code.getText().toString().trim())) {
            showToast("please enter verification code");
            return false;
        } else if (!StringUtil.isLoginPassword(et_replacephone_password.getText().toString().trim())) {
            showToast("Please set a 6 ~ 16 digit login password");
            return false;
        } else if (!cb_replacephone_agreement.isChecked()) {
            showToast("请同意三更注册协议");
            return false;
        }
        return true;
    }

    private void requestCode(String phone) {
//        GetCodeRequestBean getCodeRequestBean = new GetCodeRequestBean();
//        getCodeRequestBean.setPhone(phone);
//        getCodeRequestBean.setType(3);
//        HttpApi.app().getCode(this, getCodeRequestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                if (data != null) {
//                    ToastUtil.showToast(ReplacePhoneActivity.this, "验证码已发送");
//                    setSendCode(true);
//                } else {
//                    ToastUtil.showToast(ReplacePhoneActivity.this, message);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                setSendCode(false);
//                ToastUtil.showToast(ReplacePhoneActivity.this, error.getErrMessage());
//            }
//        });
    }

    private Handler mHandler = new Handler() {
        public void handleMessage(Message msg) {
            switch (msg.what) {
                case INTERVAL:
                    if (curTime > 0) {
                        tv_replacephone_get_code.setText("" + curTime + "秒");
                        mHandler.sendEmptyMessageDelayed(INTERVAL, 1000);
                        curTime--;
                    } else {
                        setSendCode(false);
                    }
                    break;
                default:
                    setSendCode(false);
                    break;
            }
        }
    };

    private void setSendCode(boolean send) {
        curTime = 60;
        if (send) {
            mHandler.sendEmptyMessage(INTERVAL);
            tv_replacephone_get_code.setEnabled(false);
        } else {
            tv_replacephone_get_code.setText("Resend");
            tv_replacephone_get_code.setEnabled(true);
        }
    }

    @OnClick({R.id.iv_titlebar_left_back, R.id.tv_replacephone_get_code, R.id.tv_agreement, R.id.tv_confirm_replace})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            case R.id.tv_replacephone_get_code:
                String phone = et_replace_phone.getText().toString().trim();
                if (StringUtil.isMobileNO(phone)) {
                    requestCode(phone);
                } else {
                    showToast("Please enter the correct phone number");
                }
                break;
            case R.id.tv_agreement:
                BrowserActivity.goIntent(this, MmkvGroup.global().getArgumentsLink());
                break;
            case R.id.tv_confirm_replace:
                break;
        }
    }
}

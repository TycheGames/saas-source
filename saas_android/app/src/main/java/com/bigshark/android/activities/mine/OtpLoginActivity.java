package com.bigshark.android.activities.mine;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.constraint.ConstraintLayout;
import android.text.Editable;
import android.text.TextWatcher;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.core.common.event.UserLoginedEvent;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.mine.PersonalLgoinInfoResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.personal.PersonalCountDownUtils;
import com.bigshark.android.vh.personal.PersonalProtocolUtils;
import com.socks.library.KLog;

import butterknife.BindView;
import butterknife.ButterKnife;
import de.greenrobot.event.EventBus;

public class OtpLoginActivity extends DisplayBaseActivity {

    @BindView(R.id.user_login_otp_back)
    ImageView userLoginOtpBack;
    @BindView(R.id.user_login_otp_phone_edit)
    EditText phoneEdit;
    @BindView(R.id.user_login_otp_verify_edit)
    EditText verifyEdit;
    @BindView(R.id.user_login_otp_get_verify_text)
    TextView getVerifyText;
    @BindView(R.id.user_login_otp_goto_login)
    TextView gotoLoginText;
    @BindView(R.id.user_login_otp_goto_pwdLogin)
    TextView gotoPwdLoginText;
    @BindView(R.id.user_login_otp_agreement_text)
    TextView agreementText;
    @BindView(R.id.common_navigation_status_view)
    NavigationStatusLinearLayout commonNavigationStatusView;
    @BindView(R.id.login_page_text)
    TextView loginPageText;
    @BindView(R.id.login_page_phone_text)
    TextView loginPagePhoneText;
    @BindView(R.id.user_login_otp_text)
    TextView userLoginOtpText;
    @BindView(R.id.user_login_otp_layout)
    LinearLayout userLoginOtpLayout;
    @BindView(R.id.user_login_otp_root)
    ConstraintLayout userLoginOtpRoot;
    private String phone;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_otp_login;
    }

    public static void createIntent(Context context, String phone) {
        Intent intent = new Intent(context, OtpLoginActivity.class);
        intent.putExtra("phone", phone);
        context.startActivity(intent);
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        EventBus.getDefault().register(this);
        Intent intent = getIntent();
        if (intent != null) {
            phone = intent.getStringExtra("phone");
        }

        phoneEdit.setText(phone);
        PersonalProtocolUtils.resetText(this, agreementText);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        userLoginOtpBack.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });

        TextWatcher textWatcher = new TextWatcher() {
            @Override
            public void beforeTextChanged(CharSequence s, int start, int count, int after) {
            }

            @Override
            public void onTextChanged(CharSequence s, int start, int before, int count) {
            }

            @Override
            public void afterTextChanged(Editable s) {
                if (phoneEdit.getText().toString().length() > 0 && verifyEdit.getText().toString().trim().length() > 0) {
                    gotoLoginText.setEnabled(true);
                } else {
                    gotoLoginText.setEnabled(false);
                }
            }
        };
        phoneEdit.addTextChangedListener(textWatcher);
        verifyEdit.addTextChangedListener(textWatcher);

        getVerifyText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                //getVerificationCode
                String phone = phoneEdit.getText().toString();
                if (StringUtil.isBlank(phone)) {
                    showToast(R.string.login_please_input_phone);
                    return;
                }
                HttpSender.post(new CommonResponsePendingCallback<String>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 获取验证码登录验证码
                        String getLoginOtpCodeUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_OTP_LOGIN_GET_CODE);
                        CommonRequestParams requestParams = new CommonRequestParams(getLoginOtpCodeUrl);
                        requestParams.addBodyParameter("phone", phone);
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        super.handleUi(isStart);
                        if (isStart) {
                            getVerifyText.setEnabled(false);
                        }
                    }

                    @Override
                    public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                        PersonalCountDownUtils.startCountDownTimer(getVerifyText);
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                        getVerifyText.setEnabled(true);
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        super.onCancelled(cex);
                        KLog.d(cex);
                        showToast(cex.getMessage());
                        getVerifyText.setEnabled(true);
                    }
                });
            }
        });

        gotoLoginText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String verCode = verifyEdit.getText().toString();
                if (StringUtil.isBlank(verCode)) {
                    showToast(R.string.login_please_enter_verifify_code);
                    return;
                }
                //gotoLogin
                HttpSender.post(new CommonResponsePendingCallback<PersonalLgoinInfoResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 验证码登陆
                        String loginByOtpUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_OTP_LOGIN_BY_VERFILY_CODE);
                        CommonRequestParams requestParams = new CommonRequestParams(loginByOtpUrl);
                        requestParams.addBodyParameter("phone", phone);
                        requestParams.addBodyParameter("code", verCode);
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        super.handleUi(isStart);
                        if (isStart) {
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_CLICK).build();
                        }
                    }

                    @Override
                    public void handleSuccess(PersonalLgoinInfoResponseModel resultData, int resultCode, String resultMessage) {
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN).build();
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_SUCCESS).build();
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_SUCCESS_OTP).build();
                        PersonalContext.instance().saveUserInfo(resultData, display());
                        act().finish();
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_FAILED).build();
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        super.onCancelled(cex);
                        KLog.d(cex);
                        showToast(cex.getMessage());
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_FAILED).build();
                    }
                });
            }
        });

        gotoPwdLoginText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                PasswordLoginActivity.createIntent(act(), phone);
            }
        });
    }

    @Override
    public void setupDatas() {

    }


    @Override
    protected void onDestroy() {
        EventBus.getDefault().unregister(this);
        super.onDestroy();
    }


    public void onEventMainThread(UserLoginedEvent event) {
        finish();
    }

}

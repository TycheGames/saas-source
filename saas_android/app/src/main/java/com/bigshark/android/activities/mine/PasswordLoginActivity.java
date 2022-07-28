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
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.mine.PersonalLgoinInfoResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.personal.PersonalProtocolUtils;
import com.socks.library.KLog;

import butterknife.BindView;
import butterknife.ButterKnife;

public class PasswordLoginActivity extends DisplayBaseActivity {

    @BindView(R.id.user_login_pwd_back)
    ImageView userLoginPwdBack;
    @BindView(R.id.user_login_pwd_phone_edit)
    EditText phoneEdit;
    @BindView(R.id.user_login_password_pwd_edit)
    EditText passwordEdit;
    @BindView(R.id.user_login_password_lookpwd_image)
    ImageView lookPasswordImage;
    @BindView(R.id.user_login_pwd_goto_login)
    TextView gotoLoginText;
    @BindView(R.id.gotoForgetPassWordBtn)
    TextView gotoForgetPwdText;
    @BindView(R.id.user_login_pwd_agreement_text)
    TextView agreementText;
    @BindView(R.id.common_navigation_status_view)
    NavigationStatusLinearLayout commonNavigationStatusView;
    @BindView(R.id.password_login_page_text)
    TextView passwordLoginPageText;
    @BindView(R.id.password_login_page_phone_text)
    TextView passwordLoginPagePhoneText;
    @BindView(R.id.password_login_pwd_text)
    TextView passwordLoginPwdText;
    @BindView(R.id.password_login_otp_layout)
    LinearLayout passwordLoginOtpLayout;
    @BindView(R.id.user_login_pwd_root)
    ConstraintLayout userLoginPwdRoot;
    private String phone;
    private boolean isShow = false;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_password_login;
    }

    public static void createIntent(Context context, String phone) {
        Intent intent = new Intent(context, PasswordLoginActivity.class);
        intent.putExtra("phone", phone);
        context.startActivity(intent);
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        Intent intent = getIntent();
        if (intent != null) {
            phone = intent.getStringExtra("phone");
        }

        phoneEdit.setText(phone);
        PersonalProtocolUtils.resetText(this, agreementText);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        userLoginPwdBack.setOnClickListener(new android.view.View.OnClickListener() {
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
                if (phoneEdit.getText().toString().length() > 0 && passwordEdit.getText().toString().trim().length() > 0) {
                    gotoLoginText.setEnabled(true);
                } else {
                    gotoLoginText.setEnabled(false);
                }
            }
        };
        phoneEdit.addTextChangedListener(textWatcher);
        passwordEdit.addTextChangedListener(textWatcher);

        lookPasswordImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                isShow = !isShow;
                lookPasswordImage.setImageResource(isShow ? R.drawable.mine_password_edit__show : R.drawable.mine_password_edit_hide);
                ViewUtil.passwordEditChangeShowState(passwordEdit, isShow);
            }
        });

        gotoLoginText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                final String pass = passwordEdit.getText().toString();
                if (StringUtil.isBlank(pass)) {
                    showToast(R.string.login_please_input_password);
                    return;
                }
                //gotoLogin
                HttpSender.post(new CommonResponsePendingCallback<PersonalLgoinInfoResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        String loginByPasswordUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_PASSWORD_LOGIN);
                        CommonRequestParams requestParams = new CommonRequestParams(loginByPasswordUrl);
                        requestParams.addBodyParameter("phone", phone);
                        requestParams.addBodyParameter("password", pass);
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
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_SUCCESS_PASSWORD).build();
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

        gotoForgetPwdText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                ResetPasswordActivity.createIntent(act(), phone);
            }
        });
    }

    @Override
    public void setupDatas() {

    }

}

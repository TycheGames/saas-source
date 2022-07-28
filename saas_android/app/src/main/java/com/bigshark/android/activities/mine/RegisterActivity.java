package com.bigshark.android.activities.mine;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.constraint.ConstraintLayout;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.appsflyer.AppsFlyerLib;
import com.bigshark.android.R;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.LoadingDialogUtils;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.mine.PersonalLgoinInfoResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.personal.PersonalCountDownUtils;
import com.bigshark.android.vh.personal.PersonalProtocolUtils;

import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * 注册
 */
public class RegisterActivity extends DisplayBaseActivity {

    @BindView(R.id.user_signup_back)
    ImageView userSignupBack;
    @BindView(R.id.user_signup_pass_edit)
    EditText passEdit;
    @BindView(R.id.user_signup_pwd_look_image)
    ImageView lookPwdImage;
    @BindView(R.id.user_signup_verify_pass_edit)
    EditText verifyPwdEdit;
    @BindView(R.id.user_signup_verify_pwd_look_image)
    ImageView lookVerifyPwdImage;
    @BindView(R.id.user_signup_otp_code_edit)
    EditText otpCodeEdit;
    @BindView(R.id.user_signup_otp_code_send_text)
    TextView otpCodeSendText;
    @BindView(R.id.user_signup_signup_text)
    TextView signupText;
    @BindView(R.id.user_signup_agreement_text)
    TextView agreementText;
    @BindView(R.id.common_navigation_status_view)
    NavigationStatusLinearLayout commonNavigationStatusView;
    @BindView(R.id.register_page_text)
    TextView registerPageText;
    @BindView(R.id.register_page_input_layout)
    LinearLayout registerPageInputLayout;
    @BindView(R.id.register_page_view)
    View registerPageView;
    @BindView(R.id.register_page_confirm_layout)
    LinearLayout registerPageConfirmLayout;
    @BindView(R.id.register_page_view1)
    View registerPageView1;
    @BindView(R.id.register_page_otp_layout)
    LinearLayout registerPageOtpLayout;
    @BindView(R.id.register_page_view2)
    View registerPageView2;
    @BindView(R.id.user_signup_root)
    ConstraintLayout userSignupRoot;
    private String phone;
    private boolean isPasswordShowing = false;
    private boolean isVerifyPasswordShowing = false;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_register;
    }

    public static void createIntent(Context context, String phone) {
        Intent intent = new Intent(context, RegisterActivity.class);
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

        PersonalProtocolUtils.resetText(this, agreementText);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        userSignupBack.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });

        lookPwdImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                isPasswordShowing = !isPasswordShowing;
                lookPwdImage.setImageResource(isPasswordShowing ? R.drawable.mine_password_edit__show : R.drawable.mine_password_edit_hide);
                ViewUtil.passwordEditChangeShowState(passEdit, isPasswordShowing);
            }
        });
        lookVerifyPwdImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                isVerifyPasswordShowing = !isVerifyPasswordShowing;
                lookVerifyPwdImage.setImageResource(isVerifyPasswordShowing ? R.drawable.mine_password_edit__show : R.drawable.mine_password_edit_hide);
                ViewUtil.passwordEditChangeShowState(verifyPwdEdit, isVerifyPasswordShowing);
            }
        });

        otpCodeSendText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                // getOtpCode
                HttpSender.post(new CommonResponseCallback<String>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 获取注册验证码
                        String getRegisterCodeUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_REGISTER_GET_CODE);
                        CommonRequestParams requestParams = new CommonRequestParams(getRegisterCodeUrl);
                        requestParams.addBodyParameter("phone", phone);
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        if (isStart) {
                            LoadingDialogUtils.showLoadingDialog(act());
                            otpCodeSendText.setEnabled(false);
                        } else {
                            LoadingDialogUtils.hideLoadingDialog();
                        }
                    }

                    @Override
                    public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                        PersonalCountDownUtils.startCountDownTimer(otpCodeSendText);
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        otpCodeSendText.setEnabled(true);
                        LoadingDialogUtils.hideLoadingDialog();
                        if (!StringUtil.isBlank(resultMessage)) {
                            showToast(resultMessage);
                        }
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        otpCodeSendText.setEnabled(true);
                        LoadingDialogUtils.hideLoadingDialog();
                        String message = cex.getMessage();
                        if (!StringUtil.isBlank(message)) {
                            showToast(message);
                        }
                    }
                });
            }
        });

        signupText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                checkSignupInfo();
            }
        });
    }

    private void checkSignupInfo() {
        final String password = passEdit.getText().toString();
        String verifyPassword = verifyPwdEdit.getText().toString();
        final String otpCode = otpCodeEdit.getText().toString();
        if (StringUtil.isBlank(password) || StringUtil.isBlank(verifyPassword)) {
            showToast(R.string.register_pass_not_empty);
        } else if (!password.equals(verifyPassword)) {
            showToast(R.string.register_passwords_dont_match_twice);
        } else if (StringUtil.isBlank(otpCode)) {
            showToast(R.string.register_please_enter_verifify_code);
        } else {
            //signup
            HttpSender.post(new CommonResponsePendingCallback<PersonalLgoinInfoResponseModel>(display()) {

                @Override
                public CommonRequestParams createRequestParams() {
                    // 注册
                    String signupUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_REGISTER);
                    CommonRequestParams requestParams = new CommonRequestParams(signupUrl);
                    requestParams.addBodyParameter("phone", phone);
                    requestParams.addBodyParameter("code", otpCode);
                    requestParams.addBodyParameter("password", password);

                    // appsflyer的uid，每次APP重装都会变更
                    requestParams.addBodyParameter("appsFlyerUID", AppsFlyerLib.getInstance().getAppsFlyerUID(getApplicationContext()));
                    // 点击时间：若为自然量，则为null；若为预装包与 install_time 一致，格式：2019-09-11 08:37:46.797
                    requestParams.addBodyParameter("clickTime", MmkvGroup.app().getAppsflyerClickTime());
                    // 安装时间：若为自然量，则为null；若为预装包与 click_time 一致，格式：2019-09-11 08:37:46.797
                    requestParams.addBodyParameter("installTime", MmkvGroup.app().getAppsflyerInstallTime());
                    // 是自然量还是非自然量：(Organic, Non-organic)
                    requestParams.addBodyParameter("afStatus", MmkvGroup.app().getAppsflyerAfStatus());
                    // 渠道名：若为自然量，则为null
                    requestParams.addBodyParameter("mediaSource", MmkvGroup.app().getAppsflyerMediaSource());
                    return requestParams;
                }

                @Override
                public void handleUi(boolean isStart) {
                    super.handleUi(isStart);
                    if (isStart) {
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER_CLICK).build();
                    }
                }

                @Override
                public void handleSuccess(PersonalLgoinInfoResponseModel resultData, int resultCode, String resultMessage) {
                    MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER).build();
                    MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER_SUCCESS).build();

                    PersonalContext.instance().saveUserInfo(resultData, RegisterActivity.this);
                    finish();
                }

                @Override
                public void handleFailed(int resultCode, String resultMessage) {
                    showToast(resultMessage);
                    MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER_FAILED).build();
                }

                @Override
                public void onCancelled(CancelledException cex) {
                    super.onCancelled(cex);
                    MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER_FAILED).build();
                    showToast(cex.getMessage());
                }
            });
        }
    }

    @Override
    public void setupDatas() {


    }

}

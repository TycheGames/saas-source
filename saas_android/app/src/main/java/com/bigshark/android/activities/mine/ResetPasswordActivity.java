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

import com.bigshark.android.R;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.vh.personal.PersonalCountDownUtils;
import com.socks.library.KLog;

import butterknife.BindView;
import butterknife.ButterKnife;

public class ResetPasswordActivity extends DisplayBaseActivity {

    @BindView(R.id.user_reset_password_back)
    ImageView userResetPasswordBack;
    @BindView(R.id.user_reset_password_phone_edit)
    EditText phoneEdit;
    @BindView(R.id.user_reset_password_code_edit)
    EditText codeEdit;
    @BindView(R.id.user_reset_password_otp_send)
    TextView sendOtpText;
    @BindView(R.id.user_reset_password_password_edit)
    EditText passwordEdit;
    @BindView(R.id.user_reset_password_password_look_image)
    ImageView lookPasswordImage;
    @BindView(R.id.user_reset_password_verify_passwrod_edit)
    EditText verifyPasswordEdit;
    @BindView(R.id.user_reset_password_verify_password_look_image)
    ImageView lookVerifyPasswordImage;
    @BindView(R.id.user_reset_password_btn)
    TextView userResetPasswordBtn;
    @BindView(R.id.common_navigation_status_view)
    NavigationStatusLinearLayout commonNavigationStatusView;
    @BindView(R.id.reset_password_page_text)
    TextView resetPasswordPageText;
    @BindView(R.id.reset_password_phone_layout)
    LinearLayout resetPasswordPhoneLayout;
    @BindView(R.id.reset_password_view)
    View resetPasswordView;
    @BindView(R.id.reset_password_otp_layout)
    LinearLayout resetPasswordOtpLayout;
    @BindView(R.id.reset_password_view1)
    View resetPasswordView1;
    @BindView(R.id.reset_password_input_layout)
    LinearLayout resetPasswordInputLayout;
    @BindView(R.id.reset_password_view2)
    View resetPasswordView2;
    @BindView(R.id.reset_password_confirm_layout)
    LinearLayout resetPasswordConfirmLayout;
    @BindView(R.id.reset_password_view3)
    View resetPasswordView3;
    @BindView(R.id.user_reset_password_root)
    ConstraintLayout userResetPasswordRoot;
    private String phone;
    private boolean isShow = false;
    private boolean isVerifyShow = false;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_reset_password;
    }

    public static void createIntent(Context context, String phone) {
        Intent intent = new Intent(context, ResetPasswordActivity.class);
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

    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {
        userResetPasswordBack.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
        sendOtpText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                //getCode
                HttpSender.post(new CommonResponsePendingCallback<String>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        //  重置密码
                        String resetPasswordUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_GET_CODE_FOR_RESET_PASSWORD);
                        CommonRequestParams requestParams = new CommonRequestParams(resetPasswordUrl);
                        requestParams.addBodyParameter("phone", phone);
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        super.handleUi(isStart);
                        if (isStart) {
                            sendOtpText.setEnabled(false);
                        }
                    }

                    @Override
                    public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                        PersonalCountDownUtils.startCountDownTimer(sendOtpText);
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                        sendOtpText.setEnabled(true);
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        super.onCancelled(cex);
                        KLog.d(cex);
                        showToast(cex.getMessage());
                        sendOtpText.setEnabled(true);
                    }
                });
            }
        });

        lookPasswordImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                isShow = !isShow;
                lookPasswordImage.setImageResource(isShow ? R.drawable.mine_password_edit__show : R.drawable.mine_password_edit_hide);
                ViewUtil.passwordEditChangeShowState(passwordEdit, isShow);
            }
        });
        lookVerifyPasswordImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                isVerifyShow = !isVerifyShow;
                lookVerifyPasswordImage.setImageResource(isVerifyShow ? R.drawable.mine_password_edit__show : R.drawable.mine_password_edit_hide);
                ViewUtil.passwordEditChangeShowState(verifyPasswordEdit, isVerifyShow);
            }
        });

        userResetPasswordBtn.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                checkResetInfo();
            }
        });
    }


    private void checkResetInfo() {
        String pass1 = passwordEdit.getText().toString();
        String pass2 = verifyPasswordEdit.getText().toString();
        String code = codeEdit.getText().toString();
        if (StringUtil.isBlank(pass1) || StringUtil.isBlank(pass2)) {
            showToast(R.string.resetpass_pass_not_empty);
        } else if (!pass1.equals(pass2)) {
            showToast(R.string.resetpass_passwords_dont_match_twice);
        } else if (StringUtil.isBlank(code)) {
            showToast(R.string.resetpass_please_enter_verifify_code);
        } else {
            //resetPassword
            HttpSender.post(new CommonResponsePendingCallback<String>(display()) {

                @Override
                public CommonRequestParams createRequestParams() {
                    //  重置密码
                    String resetPasswordUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_RESET_PASSWORD);
                    CommonRequestParams requestParams = new CommonRequestParams(resetPasswordUrl);
                    requestParams.addBodyParameter("phone", phone);
                    requestParams.addBodyParameter("code", code);
                    requestParams.addBodyParameter("password", pass1);
                    return requestParams;
                }

                @Override
                public void handleSuccess(String resultData, int resultCode, String resultMessage) {
                    finish();
                }

                @Override
                public void handleFailed(int resultCode, String resultMessage) {
                    showToast(resultMessage);
                }

                @Override
                public void onCancelled(CancelledException cex) {
                    super.onCancelled(cex);
                    KLog.d(cex);
                    showToast(cex.getMessage());
                }
            });
        }
    }

}

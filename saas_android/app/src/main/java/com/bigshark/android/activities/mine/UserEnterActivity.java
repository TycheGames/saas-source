package com.bigshark.android.activities.mine;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.NonNull;
import android.support.constraint.ConstraintLayout;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.appsflyer.AppsFlyerLib;
import com.bigshark.android.R;
import com.bigshark.android.contexts.AppConfigContext;
import com.bigshark.android.contexts.PersonalContext;
import com.bigshark.android.core.common.event.UserLoginedEvent;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.ConvertUtils;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.mine.PersonalInfoTruecallerResponseModel;
import com.bigshark.android.http.model.mine.UserEnterResponseModel;
import com.bigshark.android.http.model.param.TrueProfileParam;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.personal.PersonalProtocolUtils;
import com.socks.library.KLog;
import com.truecaller.android.sdk.ITrueCallback;
import com.truecaller.android.sdk.TrueError;
import com.truecaller.android.sdk.TrueProfile;
import com.truecaller.android.sdk.TrueSDK;
import com.truecaller.android.sdk.TrueSdkScope;

import butterknife.BindView;
import butterknife.ButterKnife;
import de.greenrobot.event.EventBus;

public class UserEnterActivity extends DisplayBaseActivity {
    @BindView(R.id.personal_enter_input_phone_edit)
    EditText phoneEdit;
    @BindView(R.id.user_input_phone_next)
    TextView nextText;
    @BindView(R.id.input_phone_activity_agreementText)
    TextView agreementText;
    @BindView(R.id.input_phone_activity_truecaller_login_btn)
    TextView truecallerBtn;
    @BindView(R.id.input_phone_activity_truecaller_root)
    RelativeLayout truecallerRoot;
    @BindView(R.id.user_input_phone_back)
    ImageView userInputPhoneBack;
    @BindView(R.id.common_navigation_status_view)
    NavigationStatusLinearLayout commonNavigationStatusView;
    @BindView(R.id.app_logo)
    ImageView appLogo;
    @BindView(R.id.input_phone_activity_phone_tip)
    TextView inputPhoneActivityPhoneTip;
    @BindView(R.id.mobile_line)
    View mobileLine;
    @BindView(R.id.input_phone_activity_truecaller_login_root)
    LinearLayout inputPhoneActivityTruecallerLoginRoot;
    @BindView(R.id.input_phone_activity_root)
    ConstraintLayout inputPhoneActivityRoot;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_user_enter;
    }

    public static void createIntent(Context context) {
        Intent intent = new Intent(context, UserEnterActivity.class);
        context.startActivity(intent);
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        EventBus.getDefault().register(this);

        phoneEdit.setText(MmkvGroup.loginInfo().getPhone());

        PersonalProtocolUtils.resetText(this, agreementText);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        userInputPhoneBack.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
        nextText.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                if (StringUtil.isBlank(phoneEdit.getText().toString())) {
                    showToast(R.string.input_please_input_phone);
                    return;
                }
                //gotoRegisterPageOrLoginPage
                HttpSender.post(new CommonResponsePendingCallback<UserEnterResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 判断手机号是否已注册
                        String getPhoneNumberStatusUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_ENTER_GET_PHONE_STATUS);
                        CommonRequestParams requestParams = new CommonRequestParams(getPhoneNumberStatusUrl);
                        requestParams.addBodyParameter("phone", phoneEdit.getText().toString());
                        return requestParams;
                    }

                    @Override
                    public void handleSuccess(UserEnterResponseModel resultData, int resultCode, String resultMessage) {
                        if (resultData == null) {
                            return;
                        }

                        if (resultData.isHit()) {
                            OtpLoginActivity.createIntent(act(), phoneEdit.getText().toString());
                        } else {
                            RegisterActivity.createIntent(act(), phoneEdit.getText().toString());
                        }
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
        });

        truecallerBtn.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_TRUECALLER_CLICK).build();
                KLog.d("isUsable:" + TrueSDK.getInstance().isUsable());
                if (TrueSDK.getInstance().isUsable()) {
                    TrueSDK.getInstance().getUserProfile(act());
                }
            }
        });
    }

    @Override
    public void setupDatas() {
        if (AppConfigContext.instance().isCanUseTruecaller()) {
            initTruecallerConfig();
            truecallerRoot.setVisibility(TrueSDK.getInstance().isUsable() ? View.VISIBLE : View.GONE);
        } else {
            truecallerRoot.setVisibility(View.GONE);
        }
    }

    private void initTruecallerConfig() {
        TrueSDK.init(new TrueSdkScope.Builder(act(), new ITrueCallback() {
            @Override
            public void onSuccessProfileShared(@NonNull TrueProfile trueProfile) {
//                KLog.json(ConvertUtils.toString(new TrueProfileParam(trueProfile)));
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_TRUECALLER_SDK_SUCCESS).build();
                //loginByTruecaller
                HttpSender.post(new CommonResponsePendingCallback<PersonalInfoTruecallerResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 使用truecaller进行登录、注册
                        String sendTruecallerInfoForLoginUrl = HttpConfig.getRealUrl(StringConstant.HTTP_USER_ENTER_POST_TRUECALLER_LOGIN);
                        CommonRequestParams requestParams = new CommonRequestParams(sendTruecallerInfoForLoginUrl);
                        requestParams.addBodyParameter("data", ConvertUtils.toString(new TrueProfileParam(trueProfile)));

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
                    public void handleSuccess(PersonalInfoTruecallerResponseModel resultData, int resultCode, String resultMessage) {
                        if (resultData == null) {
                            return;
                        }

                        PersonalContext.instance().saveUserInfo(resultData, display());
                        act().finish();

                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_TRUECALLER_SUCCESS).build();
                        if (resultData.isLogin()) {
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN).build();
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_LOGIN_SUCCESS).build();
                        } else {
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER).build();
                            MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_REGISTER_SUCCESS).build();
                        }
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_TRUECALLER_FAILED).build();
                    }

                    @Override
                    public void onCancelled(CancelledException cex) {
                        super.onCancelled(cex);
                        KLog.d(cex);
                        showToast(cex.getMessage());
//                MaiDianUploaderUtils.trackEvent(display(), StringConstant.EVENT_TRUECALLER_FAILED);
                    }
                });
            }

            @Override
            public void onFailureProfileShared(@NonNull TrueError trueError) {
                KLog.json(ConvertUtils.toString(trueError));
                showToast("Truecaller profile shared failure:" + trueError.getErrorType());
            }

            @Override
            public void onVerificationRequired() {
                KLog.json("");
            }
        }).consentMode(TrueSdkScope.CONSENT_MODE_FULLSCREEN).consentTitleOption(TrueSdkScope.SDK_CONSENT_TITLE_REGISTER).footerType(TrueSdkScope.FOOTER_TYPE_SKIP).build());
    }

    public void onEventMainThread(UserLoginedEvent event) {
        finish();
    }


    @Override
    protected void onDestroy() {
        EventBus.getDefault().unregister(this);
        super.onDestroy();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
        TrueSDK.getInstance().onActivityResultObtained(this, resultCode, data);
    }

}

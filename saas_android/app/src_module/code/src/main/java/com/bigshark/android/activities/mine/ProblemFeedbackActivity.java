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
import com.gyf.immersionbar.ImmersionBar;

import butterknife.ButterKnife;
import butterknife.OnClick;

//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * 问题反馈
 */
public class ProblemFeedbackActivity extends DisplayBaseActivity {

    private ImageView iv_titlebar_left_back;
    private EditText et_content;
    private TextView tv_confirm;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_problem_feedback;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        iv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        et_content = findViewById(R.id.et_content);
        tv_confirm = findViewById(R.id.tv_confirm);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    private void submitFeedback(String content) {
//        UserFeedbackRequestBean requestBean = new UserFeedbackRequestBean();
//        requestBean.setProblem(content);
//        requestBean.setContact(UserCenter.instance().getUserInfo().getUser_id());
//        HttpApi.app().userFeedback(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                ToastUtil.showToast(ProblemFeedbackActivity.this, message);
//                finish();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(ProblemFeedbackActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_USERFEEDBACK_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("problem", content);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                showToast(resultMessage);
                finish();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void setupDatas() {

    }

    @OnClick({R.id.iv_titlebar_left_back, R.id.tv_confirm})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            case R.id.tv_confirm:
                String content = et_content.getText().toString().trim();
                if (content.length() == 0) {
                    return;
                }
                submitFeedback(content);
                break;
        }
    }
}

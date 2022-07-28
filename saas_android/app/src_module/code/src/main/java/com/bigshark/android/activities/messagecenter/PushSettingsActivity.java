package com.bigshark.android.activities.messagecenter;

import android.os.Bundle;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.message.GetAllPushStatusModel;
import com.bigshark.android.http.model.message.SetPushStatusModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.gyf.immersionbar.ImmersionBar;

import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * 消息推送设置
 */
public class PushSettingsActivity extends DisplayBaseActivity implements View.OnClickListener {

    public static final String TYPE_PRIVATECHAT = "privateChat";//私聊
    public static final String TYPE_BROADCAST = "broadcast";//广播
    public static final String TYPE_SYSTEM = "system";//系统
    public static final String TYPE_COMMENT = "comment";//评论
    public static final String TYPE_REPLY = "reply";//回复&点赞
    public static final String TYPE_PROFIT = "profit";//收益
    @BindView(R.id.iv_titlebar_left_back)
    ImageView iv_titlebar_left_back;
    @BindView(R.id.iv_titlebar_close)
    ImageView iv_titlebar_close;
    @BindView(R.id.tv_titlebar_title)
    TextView tv_titlebar_title;
    @BindView(R.id.rl_titlebar)
    RelativeLayout rl_titlebar;
    @BindView(R.id.cb_private_message_notification)
    CheckBox cb_private_message_notification;
    @BindView(R.id.cb_radio_notification)
    CheckBox cb_radio_notification;
    @BindView(R.id.cb_systematic_notification)
    CheckBox cb_systematic_notification;
    @BindView(R.id.cb_evaluation_notification)
    CheckBox cb_evaluation_notification;
    @BindView(R.id.cb_thumb_up_notice)
    CheckBox cb_thumb_up_notice;
    @BindView(R.id.cb_returns_remind)
    CheckBox cb_returns_remind;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_push_settings;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        tv_titlebar_title.setText("Message push settings");

        cb_private_message_notification.setOnClickListener(this);
        cb_radio_notification.setOnClickListener(this);
        cb_systematic_notification.setOnClickListener(this);
        cb_evaluation_notification.setOnClickListener(this);
        cb_thumb_up_notice.setOnClickListener(this);
        cb_returns_remind.setOnClickListener(this);

    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_titlebar_left_back.setOnClickListener(this);
    }

    @Override
    public void setupDatas() {
//        HttpApi.app().getPushStatus(this, new BaseRequestBean(), new HttpCallback<GetAllPushStatusModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, GetAllPushStatusModel data) {
//                if (data != null) {
//                    cb_private_message_notification.setChecked(data.getPrivateChat().getStatus() == 1);
//                    cb_radio_notification.setChecked(data.getBroadcast().getStatus() == 1);
//                    cb_systematic_notification.setChecked(data.getSystem().getStatus() == 1);
//                    cb_evaluation_notification.setChecked(data.getComment().getStatus() == 1);
//                    cb_thumb_up_notice.setChecked(data.getReply().getStatus() == 1);
//                    cb_returns_remind.setChecked(data.getProfit().getStatus() == 1);
//                } else {
//                    showToast(message);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showToast(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<GetAllPushStatusModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETPUSHSTATUS_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(GetAllPushStatusModel data, int resultCode, String resultMessage) {
                if (data != null) {
                    cb_private_message_notification.setChecked(data.getPrivateChat().getStatus() == 1);
                    cb_radio_notification.setChecked(data.getBroadcast().getStatus() == 1);
                    cb_systematic_notification.setChecked(data.getSystem().getStatus() == 1);
                    cb_evaluation_notification.setChecked(data.getComment().getStatus() == 1);
                    cb_thumb_up_notice.setChecked(data.getReply().getStatus() == 1);
                    cb_returns_remind.setChecked(data.getProfit().getStatus() == 1);
                } else {
                    showToast(resultMessage);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            case R.id.cb_private_message_notification:
                setPushStatus(TYPE_PRIVATECHAT, cb_private_message_notification.isChecked() ? 1 : 2);
                break;
            case R.id.cb_radio_notification:
                setPushStatus(TYPE_BROADCAST, cb_radio_notification.isChecked() ? 1 : 2);
                break;
            case R.id.cb_systematic_notification:
                setPushStatus(TYPE_SYSTEM, cb_systematic_notification.isChecked() ? 1 : 2);
                break;
            case R.id.cb_evaluation_notification:
                setPushStatus(TYPE_COMMENT, cb_evaluation_notification.isChecked() ? 1 : 2);
                break;
            case R.id.cb_thumb_up_notice:
                setPushStatus(TYPE_REPLY, cb_thumb_up_notice.isChecked() ? 1 : 2);
                break;
            case R.id.cb_returns_remind:
                setPushStatus(TYPE_PROFIT, cb_returns_remind.isChecked() ? 1 : 2);
                break;
            default:
                break;
        }
    }

    public void setPushStatus(String type, int status) {
//        SetPushStatusRequestBean requestBean = new SetPushStatusRequestBean();
//        requestBean.setType(type);
//        requestBean.setStatus(status);
//        HttpApi.app().setPushStatus(this, requestBean, new HttpCallback<SetPushStatusModel>() {
//            @Override
//            public void onSuccess(int code, String message, SetPushStatusModel data) {
//
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<SetPushStatusModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_SETPUSHSTATUS_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("type", type);
                requestParams.addBodyParameter("status", status);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(SetPushStatusModel data, int resultCode, String resultMessage) {
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

}

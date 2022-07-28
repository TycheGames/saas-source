package com.bigshark.android.activities.home;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.home.AnonymousReportingAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.home.UserReportOptionsModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.gyf.immersionbar.ImmersionBar;

import java.util.List;

import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * 匿名举报
 */
public class AnonymousReportingActivity extends DisplayBaseActivity {

    @BindView(R.id.iv_back)
    ImageView iv_back;
    @BindView(R.id.tv_submit)
    TextView tv_submit;
    @BindView(R.id.tv_report_name)
    TextView tv_report_name;
    @BindView(R.id.rv_report_options)
    RecyclerView mRecyclerView;
    @BindView(R.id.et_report_content)
    EditText et_report_content;

    private static final String EXTRA_TYPE = "type";//举报类型 （1用户 2约会）
    private static final String EXTRA_USERID = "userid";
    private static final String EXTRA_RADIOID = "radioid";

    private int mType;
    private String mUserId;
    private String mRadioId;
    private AnonymousReportingAdapter mAdapter;
    private List<UserReportOptionsModel> mOptionsBeanList;
    private String reasonId;

    public static void openIntent(Activity activity, int type, String userId, String radioId) {
        Intent intent = new Intent(activity, AnonymousReportingActivity.class);
        intent.putExtra(EXTRA_TYPE, type);
        intent.putExtra(EXTRA_USERID, userId);
        intent.putExtra(EXTRA_RADIOID, radioId);
        activity.startActivity(intent);
    }

    @Override
    protected int getLayoutId() {
        return R.layout.activity_anonymous_reporting;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        mType = getIntent().getIntExtra(EXTRA_TYPE, 0);
        mUserId = getIntent().getStringExtra(EXTRA_USERID);
        mRadioId = getIntent().getStringExtra(EXTRA_RADIOID);
        if (1 == mType) {
            tv_report_name.setText("Report this user");
        } else {
            tv_report_name.setText("Report this appointment");
        }
        initRecyclerView();
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        mAdapter = new AnonymousReportingAdapter();
        mRecyclerView.setAdapter(mAdapter);
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                UserReportOptionsModel itemBean = (UserReportOptionsModel) adapter.getData().get(position);
                for (int i = 0; i < mOptionsBeanList.size(); i++) {
                    mOptionsBeanList.get(i).setSelected(false);
                }
                mOptionsBeanList.get(position).setSelected(true);
                reasonId = mOptionsBeanList.get(position).getId();
                adapter.notifyDataSetChanged();
            }
        });
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_back.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });
        tv_submit.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (StringUtil.isBlank(reasonId)) {
                    ToastUtil.showToast(AnonymousReportingActivity.this, "请选择举报原因");
                    return;
                }
                requestUserReport();
            }
        });
    }

    /**
     * 请求举报接口
     */
    private void requestUserReport() {
//        UserReportRequestBean requestBean = new UserReportRequestBean();
//        requestBean.setType(mType);
//        requestBean.setReason_id(reasonId);
//        if (1 == mType) {
//            requestBean.setTo_uid(mUserId);
//        } else {
//            requestBean.setB_id(mRadioId);
//            if (!StringUtil.isBlankEdit(et_report_content)) {
//                requestBean.setMore_info(et_report_content.getText().toString().trim());
//            }
//        }
//        HttpApi.app().userReport(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                showToast("举报成功");
//                finish();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showToast(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_USERREPORT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("type", mType);
                requestParams.addBodyParameter("reason_id", reasonId);
                if (1 == mType) {
                    requestParams.addBodyParameter("to_uid", mUserId);
                } else {
                    requestParams.addBodyParameter("b_id", mRadioId);
                    if (!StringUtil.isBlankEdit(et_report_content)) {
                        requestParams.addBodyParameter("more_info", et_report_content.getText().toString().trim());
                    }
                }
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                showToast("举报成功");
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
//        UserReportOptionsRequestBean reportOptionsRequestBean = new UserReportOptionsRequestBean();
//        reportOptionsRequestBean.setType(mType);
//        HttpApi.app().userReportOptions(this, reportOptionsRequestBean, new HttpCallback<List<UserReportOptionsModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<UserReportOptionsModel> data) {
//                if (data != null && data.size() > 0) {
//                    mOptionsBeanList = data;
//                    mAdapter.setNewData(mOptionsBeanList);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//            }
//        });
        HttpSender.post(new CommonResponseCallback<List<UserReportOptionsModel>>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_USERREPORTOPTIONS_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("type", mType);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(List<UserReportOptionsModel> data, int resultCode, String resultMessage) {
                if (data != null && data.size() > 0) {
                    mOptionsBeanList = data;
                    mAdapter.setNewData(mOptionsBeanList);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

}

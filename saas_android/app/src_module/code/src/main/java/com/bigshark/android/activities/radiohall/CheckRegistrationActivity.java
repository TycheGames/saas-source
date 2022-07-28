package com.bigshark.android.activities.radiohall;

import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.radiohall.CheckRegistrationAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.radiohall.CheckRegistrationListItemModel;
import com.bigshark.android.http.model.radiohall.CheckRegistrationModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.gyf.immersionbar.ImmersionBar;

import java.util.ArrayList;
import java.util.List;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 查看报名
 */
public class CheckRegistrationActivity extends DisplayBaseActivity implements SwipeRefreshLayout.OnRefreshListener {

    private TextView mTv_titlebar_title;
    private ImageView mIv_titlebar_left_back;
    private RecyclerView mRecyclerView;
    private CheckRegistrationAdapter mAdapter;
    private List<CheckRegistrationListItemModel> mList = new ArrayList<>();
    public static final String EXTRA_ID = "extra_id";
    private String mRadioId;
    private SwipeRefreshLayout mSwipeRefreshLayout;
    private boolean isRefresh = true;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_check_registration;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        mRadioId = getIntent().getStringExtra(EXTRA_ID);
        mIv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        mTv_titlebar_title = findViewById(R.id.tv_titlebar_title);
        mTv_titlebar_title.setText("View registration");
        mRecyclerView = findViewById(R.id.recycler_view_check_registration);
        mSwipeRefreshLayout = findViewById(R.id.srl);
        initRecyclerView();
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        mAdapter = new CheckRegistrationAdapter(this);
        mRecyclerView.setAdapter(mAdapter);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {
        requestData();
    }

    public void requestData() {
        if (mRadioId == null) {
            return;
        }
        showProgressBar();
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(mRadioId);
//        HttpApi.app().getEnrollList(this, requestBean, new HttpCallback<CheckRegistrationModel>() {
//            @Override
//            public void onSuccess(int code, String message, CheckRegistrationModel data) {
//                hideProgressBar();
//                if (data != null) {
//                    mAdapter.setRadioId(data.getBroadcast_id());
//                    if (data.getList() != null && data.getList().size() != 0) {
//                        if (isRefresh) {
//                            mAdapter.setNewData(data.getList());
//                            isRefresh = false;
//                        } else {
//                            mAdapter.addData(data.getList());
//                        }
//                    } else {
//                        showNoData();
//                    }
//                } else {
//                    showErrorView();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showErrorView();
//                hideProgressBar();
//                showToast(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<CheckRegistrationModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETENROLLLIST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mRadioId);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(CheckRegistrationModel data, int resultCode, String resultMessage) {
                hideProgressBar();
                if (data != null) {
                    mAdapter.setRadioId(data.getBroadcast_id());
                    if (data.getList() != null && data.getList().size() != 0) {
                        if (isRefresh) {
                            mAdapter.setNewData(data.getList());
                            isRefresh = false;
                        } else {
                            mAdapter.addData(data.getList());
                        }
                    } else {
                        showNoData();
                    }
                } else {
                    showErrorView();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showErrorView();
                hideProgressBar();
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void onRefresh() {
        isRefresh = true;
        requestData();
    }

    public void showErrorView() {
        mAdapter.setEmptyView(R.layout.public_no_network, (ViewGroup) mRecyclerView.getParent());
    }

    public void showNoData() {
        mAdapter.setEmptyView(R.layout.public_no_data, (ViewGroup) mRecyclerView.getParent());
    }

    private void showProgressBar() {
        if (!mSwipeRefreshLayout.isRefreshing()) {
            mSwipeRefreshLayout.setRefreshing(true);
        }
    }

    private void hideProgressBar() {
        if (mSwipeRefreshLayout.isRefreshing()) {
            mSwipeRefreshLayout.setRefreshing(false);
        }
    }

    @OnClick(R.id.iv_titlebar_left_back)
    public void onViewClicked() {
        finish();
    }
}

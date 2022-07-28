package com.bigshark.android.activities.messagecenter;

import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.messagecenter.SystematicNotificationListAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.message.NewsSystemModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.widget.MyRecyclerView;
import com.gyf.immersionbar.ImmersionBar;

import butterknife.BindView;
import butterknife.ButterKnife;

/**
 * 系统通知
 */
public class SystematicNotificationListActivity extends DisplayBaseActivity implements MyRecyclerView.LoadMoreListener, SwipeRefreshLayout.OnRefreshListener {

    @BindView(R.id.iv_titlebar_left_back)
    ImageView iv_titlebar_left_back;
    @BindView(R.id.iv_titlebar_close)
    ImageView ivTitlebarClose;
    @BindView(R.id.tv_titlebar_title)
    TextView tv_titlebar_title;
    @BindView(R.id.rl_titlebar)
    RelativeLayout rlTitlebar;
    @BindView(R.id.recycler_view)
    MyRecyclerView mRecyclerView;
    @BindView(R.id.refresh_layout)
    SwipeRefreshLayout mSwipeRefreshLayout;

    private boolean isRefresh = true;
    private boolean canLoading = true;
    private SystematicNotificationListAdapter mAdapter;
    private int mPage = 1;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_radio_notice_list;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        tv_titlebar_title.setText("System Notification");
        initRecyclerView();
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        mAdapter = new SystematicNotificationListAdapter(this);
        mRecyclerView.setAdapter(mAdapter);
        mRecyclerView.setLoadMoreListener(this);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_titlebar_left_back.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });

    }

    @Override
    public void setupDatas() {
        showProgressBar();
//        MyBroadcastRequestBean requestBean = new MyBroadcastRequestBean();
//        requestBean.setPage(mPage);
//        requestBean.setP_num(20);
//        HttpApi.app().getNewsSystemList(this, requestBean, new HttpCallback<NewsSystemModel>() {
//            @Override
//            public void onSuccess(int code, String message, NewsSystemModel data) {
//                hideProgressBar();
//                if (data.getList() != null && data.getList().size() > 0) {
//                    canLoading = true;
//                    mPage++;
//                    if (isRefresh) {
//                        mAdapter.setNewData(data.getList());
//                        isRefresh = false;
//                    } else {
//                        mAdapter.addData(data.getList());
//                    }
//                } else {
//                    showNoData();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showErrorView();
//                hideProgressBar();
//                showToast( error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<NewsSystemModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETNEWSSYSTEMLIST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("page", mPage);
                requestParams.addBodyParameter("p_num", 20);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(NewsSystemModel data, int resultCode, String resultMessage) {
                hideProgressBar();
                if (data.getList() != null && data.getList().size() > 0) {
                    canLoading = true;
                    mPage++;
                    if (isRefresh) {
                        mAdapter.setNewData(data.getList());
                        isRefresh = false;
                    } else {
                        mAdapter.addData(data.getList());
                    }
                } else {
                    showNoData();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                hideProgressBar();
                showErrorView();
                showToast(resultMessage);
            }
        });
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

    public void showErrorView() {
        canLoading = true;
        mAdapter.setEmptyView(R.layout.public_no_network, (ViewGroup) mRecyclerView.getParent());
    }

    public void showNoData() {
        mAdapter.setEmptyView(R.layout.public_no_data, (ViewGroup) mRecyclerView.getParent());
    }

    @Override
    public void loadMore() {
        if (canLoading) {
            setupDatas();
            canLoading = false;
        }
    }

    @Override
    public void onRefresh() {
        isRefresh = true;
        mPage = 1;
        setupDatas();
    }

}

package com.bigshark.android.fragments.messagecenter;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.view.ViewGroup;

import com.bigshark.android.R;
import com.bigshark.android.activities.messagecenter.EarningsRemindListActivity;
import com.bigshark.android.activities.messagecenter.EvaluationNotificationListActivity;
import com.bigshark.android.activities.messagecenter.RadioNoticeListActivity;
import com.bigshark.android.activities.messagecenter.SystematicNotificationListActivity;
import com.bigshark.android.activities.radiohall.RadioDetailsActivity;
import com.bigshark.android.adapters.messagecenter.MyMessageListAdapter;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.http.model.message.NewsAllListItemModel;
import com.bigshark.android.http.model.message.NewsAllModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;

/**
 * 消息
 */
public class MyMessageListFragment extends DisplayBaseFragment implements SwipeRefreshLayout.OnRefreshListener {

    private SwipeRefreshLayout mSwipeRefreshLayout;
    private RecyclerView mRecyclerView;
    private MyMessageListAdapter mAdapter;

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_my_message_list;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        mSwipeRefreshLayout = fragmentRoot.findViewById(R.id.srl_message);
        mRecyclerView = fragmentRoot.findViewById(R.id.rv_message);
        initRecyclerView();
    }

    @Override
    public void onResume() {
        super.onResume();
        onRefresh();
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new LinearLayoutManager(act()));
        mAdapter = new MyMessageListAdapter(act());
        mRecyclerView.setAdapter(mAdapter);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
    }

    @Override
    protected void bindListeners() {
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                NewsAllListItemModel itemBean = (NewsAllListItemModel) adapter.getData().get(position);
                //20000 广播  20001 系统通知    20002  评价通知  20003 回复&点赞  20004 收益
                switch (itemBean.getJump_type()) {
                    case 20000:
                        startActivity(new Intent(act(), RadioNoticeListActivity.class));
                        break;
                    case 20001:
                        startActivity(new Intent(act(), SystematicNotificationListActivity.class));
                        break;
                    case 20002:
                        startActivity(new Intent(act(), EvaluationNotificationListActivity.class));
                        break;
                    case 20003:
                        if (StringUtil.isBlank(itemBean.getBroadcast_id())) {
                            ToastUtil.showToast(act(), itemBean.getContents());
                            return;
                        }
                        Intent intent = new Intent(act(), RadioDetailsActivity.class);
                        intent.putExtra(RadioDetailsActivity.EXTRA_ID, itemBean.getBroadcast_id());
                        act().startActivity(intent);
                        break;
                    case 20004:
                        startActivity(new Intent(act(), EarningsRemindListActivity.class));
                        break;
                    default:
                        break;
                }

            }
        });
    }

    @Override
    protected void setupDatas() {
        requestData();
    }

    private void requestData() {
        showProgressBar();
//        HttpApi.app().getNewsAll(act(), new BaseRequestBean(), new HttpCallback<NewsAllModel>() {
//            @Override
//            public void onSuccess(int code, String message, NewsAllModel data) {
//                hideProgressBar();
//                if (data != null && data.getList() != null && data.getList().size() > 0) {
//                    mAdapter.setNewData(data.getList());
//                } else {
//                    showNoData();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showErrorView();
//                hideProgressBar();
//                ToastUtil.showToast(act(), error.getErrMessage());
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<NewsAllModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETNEWSALL_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(NewsAllModel data, int resultCode, String resultMessage) {
                hideProgressBar();
                if (data != null && data.getList() != null && data.getList().size() > 0) {
                    mAdapter.setNewData(data.getList());
                } else {
                    showNoData();
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
        requestData();
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
        mAdapter.setEmptyView(R.layout.public_no_network, (ViewGroup) mRecyclerView.getParent());
    }

    public void showNoData() {
        mAdapter.setEmptyView(R.layout.public_no_data, (ViewGroup) mRecyclerView.getParent());
    }


}

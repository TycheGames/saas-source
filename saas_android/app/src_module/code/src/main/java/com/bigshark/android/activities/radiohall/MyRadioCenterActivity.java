package com.bigshark.android.activities.radiohall;

import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.UserMenHomePageActivity;
import com.bigshark.android.activities.home.UserWomenHomePageActivity;
import com.bigshark.android.adapters.radiohall.MyRadioListAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.radiohall.ClickGoodModel;
import com.bigshark.android.http.model.radiohall.CommentBroadcastModel;
import com.bigshark.android.http.model.radiohall.RadioHallModel;
import com.bigshark.android.http.model.radiohall.RadioListItemModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.listener.radiohall.OnRadioClickListener;
import com.bigshark.android.listener.radiohall.OnSendClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.widget.MyRecyclerView;
import com.bigshark.android.widget.NoEmojiPanelView;
import com.bigshark.android.widget.popupwindow.CommonOnePopup;
import com.bigshark.android.widget.popupwindow.ReportPopup;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.gyf.immersionbar.ImmersionBar;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 我的广播中心
 */
public class MyRadioCenterActivity extends DisplayBaseActivity implements MyRecyclerView.LoadMoreListener, SwipeRefreshLayout.OnRefreshListener {

    private int mPage = 1;
    private ImageView iv_titlebar_left_back, iv_release_radio;
    private TextView tv_titlebar_title;
    private SwipeRefreshLayout mSwipeRefreshLayout;
    private MyRecyclerView mRecyclerView;
    private NoEmojiPanelView panel_view;
    private MyRadioListAdapter mAdapter;
    private boolean isRefresh = true;
    private boolean canLoading = true;

    private int selectPosition;
    private RadioListItemModel selectItemBean;
    private CommonOnePopup mOverRadioPopup;
    private ReportPopup mReportPopup;

    private View rootview;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_my_radio_center;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_my_radio_center, null);
        iv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        iv_release_radio = findViewById(R.id.iv_release_radio);
        tv_titlebar_title = findViewById(R.id.tv_titlebar_title);
        tv_titlebar_title.setText("My Broadcast Center");
        mSwipeRefreshLayout = findViewById(R.id.srl);
        mRecyclerView = findViewById(R.id.recycler_view);
        panel_view = findViewById(R.id.panel_view);
        initRecyclerView();
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        mAdapter = new MyRadioListAdapter(this, R.layout.adapter_radiohall_recommend_listitem);
        mRecyclerView.setAdapter(mAdapter);
        mRecyclerView.setLoadMoreListener(this);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
        mAdapter.setOnRadioClickListener(new OnRadioClickListener() {

            @Override
            public void onCommentsClick(int pos) {
                //评论 吊起键盘
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                panel_view.showEmojiPanel();
            }

            @Override
            public void onApplyClick(int pos) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                //结束报名
                mOverRadioPopup = new CommonOnePopup(MyRadioCenterActivity.this, "Are you sure you want to end the broadcast？", "Confirm");
                mOverRadioPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
                    @Override
                    public void OnConfirmClick(String data) {
                        //结束广播
                        mOverRadioPopup.dismiss();
                        overRaido(pos);
                    }
                });
//                new XPopup.Builder(MyRadioCenterActivity.this).asCustom(mOverRadioPopup).show();
                mOverRadioPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }

            @Override
            public void onPraiseClick(int pos, ImageView imageView) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                requestGiveLike(pos);
            }

            @Override
            public void onAvatarClick(int pos) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                //用户主页
                Intent intentUser = null;
                if (selectItemBean.getSex() == 1) {
                    intentUser = new Intent(MyRadioCenterActivity.this, UserMenHomePageActivity.class);
                    intentUser.putExtra(UserMenHomePageActivity.EXTRA_USER_ID, selectItemBean.getUser_id());
                } else if (selectItemBean.getSex() == 2) {
                    intentUser = new Intent(MyRadioCenterActivity.this, UserWomenHomePageActivity.class);
                    intentUser.putExtra(UserWomenHomePageActivity.EXTRA_USER_ID, selectItemBean.getUser_id());
                }
                startActivity(intentUser);
            }

            @Override
            public void onCheckRegistrationClick(int pos) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                Intent intent = new Intent(MyRadioCenterActivity.this, CheckRegistrationActivity.class);
                intent.putExtra(CheckRegistrationActivity.EXTRA_ID, selectItemBean.getId());
                startActivity(intent);
            }

            @Override
            public void onReportClick(int pos, View view) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (selectItemBean.getStatus() == 2) {
                    return;
                }
                mReportPopup = new ReportPopup(MyRadioCenterActivity.this, selectItemBean.getId());
//                new XPopup.Builder(MyRadioCenterActivity.this).hasShadowBg(false).atView(view).asCustom(mReportPopup).show();
                mReportPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        });
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        panel_view.setOnSendClickListener(new OnSendClickListener() {

            @Override
            public void onSendContent(String content) {
                if (StringUtil.isBlank(content)) {
                    showToast("Comment content cannot be empty");
                    return;
                }
                //请求评论接口
                commentBroadcast(content);
            }
        });

        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                RadioListItemModel itemBean = (RadioListItemModel) adapter.getData().get(position);
                if (2 == itemBean.getStatus()) {
                    showToast("Broadcast has ended");
                    return;
                }
                if (1 == itemBean.getIs_official()) {
                    return;
                }
                Intent intent = new Intent(MyRadioCenterActivity.this, RadioDetailsActivity.class);
                intent.putExtra(RadioDetailsActivity.EXTRA_ID, itemBean.getId());
                startActivity(intent);
            }
        });
    }

    @Override
    public void setupDatas() {
        requestData();
    }

    private void requestData() {
        showProgressBar();
//        MyBroadcastRequestBean requestBean = new MyBroadcastRequestBean();
//        requestBean.setPage(mPage);
//        requestBean.setP_num(20);
//        HttpApi.app().getMyBroadcast(this, requestBean, new HttpCallback<RadioHallModel>() {
//            @Override
//            public void onSuccess(int code, String message, RadioHallModel data) {
//                hideProgressBar();
//                if (data != null) {
//                    if (data.getList() != null && data.getList().size() != 0) {
//                        canLoading = true;
//                        mPage++;
//                        if (isRefresh) {
//                            mAdapter.setNewData(data.getList());
//                            isRefresh = false;
//                        } else {
//                            mAdapter.addData(data.getList());
//                        }
//                    } else if (data.getPage() == 1 && data.getList().size() == 0) {
//                        showNoData();
//                    } else {
//                        canLoading = false;
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
//                ToastUtil.showToast(MyRadioCenterActivity.this, error.getErrMessage());
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<RadioHallModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETMYBROADCAST_KEY);
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
            public void handleSuccess(RadioHallModel data, int resultCode, String resultMessage) {
                hideProgressBar();
                if (data != null) {
                    if (data.getList() != null && data.getList().size() != 0) {
                        canLoading = true;
                        mPage++;
                        if (isRefresh) {
                            mAdapter.setNewData(data.getList());
                            isRefresh = false;
                        } else {
                            mAdapter.addData(data.getList());
                        }
                    } else if (data.getPage() == 1 && data.getList().size() == 0) {
                        showNoData();
                    } else {
                        canLoading = false;
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
    public void loadMore() {
        if (canLoading) {
            requestData();
            canLoading = false;
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
    public void onRefresh() {
        isRefresh = true;
        mPage = 1;
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

    /**
     * 评论广播
     *
     * @param content
     */
    private void commentBroadcast(String content) {
//        CommentBroadcastRequestBean requestBean = new CommentBroadcastRequestBean();
//        requestBean.setBroadcast_id(selectItemBean.getId());
//        requestBean.setContent(content);
//        HttpApi.app().commentBroadcast(this, requestBean, new HttpCallback<CommentBroadcastModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, CommentBroadcastModel data) {
//                panel_view.clearInputContent();
//                panel_view.dismiss();
//                showToast("Comment successful");
//                mAdapter.getItem(selectPosition).setCommented_num(data.getCommented_num());
//                mAdapter.getItem(selectPosition).setIs_comment(data.getIs_commented());
//                mAdapter.notifyItemChanged(selectPosition);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//            }
//        });
        HttpSender.post(new CommonResponseCallback<CommentBroadcastModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_COMMENTBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", selectItemBean.getId());
                requestParams.addBodyParameter("content", content);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(CommentBroadcastModel data, int resultCode, String resultMessage) {
                panel_view.clearInputContent();
                panel_view.dismiss();
                showToast("Comment successful");
                mAdapter.getItem(selectPosition).setCommented_num(data.getCommented_num());
                mAdapter.getItem(selectPosition).setIs_comment(data.getIs_commented());
                mAdapter.notifyItemChanged(selectPosition);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    /**
     * 结束广播
     */
    private void overRaido(int position) {
        RadioListItemModel item = mAdapter.getItem(position);
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(item.getId());
//        HttpApi.app().endBroadcast(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                onRefresh();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_ENDBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", item.getId());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                onRefresh();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    /**
     * 请求点赞
     */
    private void requestGiveLike(int position) {
        RadioListItemModel itemBean = mAdapter.getData().get(position);
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(itemBean.getId());
//        HttpApi.app().clickGoodBroadcast(MyRadioCenterActivity.this, requestBean, new HttpCallback<ClickGoodModel>() {
//            @Override
//            public void onSuccess(int code, String message, ClickGoodModel data) {
//                mAdapter.getItem(position).setClick_good_num(Integer.parseInt(data.getClick_good_num()));
//                mAdapter.getItem(position).setIs_click_good(1);
//                mAdapter.notifyItemChanged(position);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showToast(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<ClickGoodModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_CLICKGOODBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", itemBean.getId());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(ClickGoodModel data, int resultCode, String resultMessage) {
                mAdapter.getItem(position).setClick_good_num(Integer.parseInt(data.getClick_good_num()));
                mAdapter.getItem(position).setIs_click_good(1);
                mAdapter.notifyItemChanged(position);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @OnClick({R.id.iv_titlebar_left_back, R.id.iv_release_radio})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            case R.id.iv_release_radio:
                startActivity(new Intent(MyRadioCenterActivity.this, ReleaseRadioActivity.class));
                break;
        }
    }
}

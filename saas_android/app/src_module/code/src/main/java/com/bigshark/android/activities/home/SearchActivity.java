package com.bigshark.android.activities.home;

import android.content.Intent;
import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.view.ViewGroup;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.home.HomePagerRecommendListAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.model.home.HomePagerRecommendListResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.gyf.immersionbar.ImmersionBar;

import java.util.List;

import butterknife.BindView;
import butterknife.ButterKnife;

//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * 搜索 activity
 */
public class SearchActivity extends DisplayBaseActivity {

    @BindView(R.id.iv_titlebar_left_back)
    ImageView iv_titlebar_left_back;
    @BindView(R.id.et_search)
    EditText et_search;
    @BindView(R.id.tv_search)
    TextView tv_search;
    @BindView(R.id.rv_search)
    RecyclerView mRecyclerView;
    private HomePagerRecommendListAdapter mAdapter;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_search;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        mRecyclerView.setLayoutManager(new LinearLayoutManager(this));
        mAdapter = new HomePagerRecommendListAdapter(this, R.layout.adapter_home_recommend_listitem);
        mRecyclerView.setAdapter(mAdapter);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_titlebar_left_back.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });
        tv_search.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (StringUtil.isBlankEdit(et_search)) {
                    ToastUtil.showToast(SearchActivity.this, "Please enter a nickname");
                    return;
                }
                searchNickName(et_search.getText().toString().trim());
            }
        });

        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                HomePagerRecommendListResponseModel itemBean = (HomePagerRecommendListResponseModel) adapter.getData().get(position);
//                if (itemBean.getSex() == UserCenter.instance().getUserGender()) {
//                    ToastUtil.showToast(SearchActivity.this, "同性之间不能查看");
//                    return;
//                }
                //用户主页
                Intent intent = null;
                if (itemBean.getSex() == 1) {
                    intent = new Intent(SearchActivity.this, UserMenHomePageActivity.class);
                    intent.putExtra(UserMenHomePageActivity.EXTRA_USER_ID, itemBean.getUser_id());
                } else if (itemBean.getSex() == 2) {
                    intent = new Intent(SearchActivity.this, UserWomenHomePageActivity.class);
                    intent.putExtra(UserWomenHomePageActivity.EXTRA_USER_ID, itemBean.getUser_id());
                }
                startActivity(intent);
            }
        });
        mAdapter.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                switchFollow(Integer.valueOf(str));
            }
        });
    }

    /**
     * 添加或取消关注 0:添加收藏 -1:取消收藏
     *
     * @param pos
     */
    private void switchFollow(int pos) {
        HomePagerRecommendListResponseModel itemBean = mAdapter.getData().get(pos);
//        FollowRequestBean requestBean = new FollowRequestBean();
//        requestBean.setFav_uid(itemBean.getUser_id());
//        requestBean.setType(itemBean.getC_status() == 1 ? -1 : 0);
//        HttpApi.app().switchCollection(SearchActivity.this, requestBean, new HttpCallback<String>() {
//
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                mAdapter.getData().get(pos).setC_status(itemBean.getC_status() == 1 ? 0 : 1);
//                mAdapter.notifyItemChanged(pos);
//                if (itemBean.getC_status() == 1) {
//                    ToastUtil.showToast(SearchActivity.this, "Collection success");
//                } else {
//                    ToastUtil.showToast(SearchActivity.this, "Cancel collection successfully");
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(SearchActivity.this, error.getErrMessage());
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_SWITCHCOLLECTION_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("fav_uid", itemBean.getUser_id());
                requestParams.addBodyParameter("type", itemBean.getC_status() == 1 ? -1 : 0);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                mAdapter.getData().get(pos).setC_status(itemBean.getC_status() == 1 ? 0 : 1);
                mAdapter.notifyItemChanged(pos);
                if (itemBean.getC_status() == 1) {
                    ToastUtil.showToast(SearchActivity.this, "Collection success");
                } else {
                    ToastUtil.showToast(SearchActivity.this, "Cancel collection successfully");
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                ToastUtil.showToast(SearchActivity.this, resultMessage);
            }
        });
    }

    /**
     * 请求数据
     *
     * @param nickName
     */
    private void searchNickName(String nickName) {
//        HomePagerRecommendListRequestBean requestBean = new HomePagerRecommendListRequestBean();
//        requestBean.setNickname(nickName);
//        requestBean.setPage(1);
//        requestBean.setP_num(20);
//        HttpApi.app().getNearbyList(this, requestBean, new HttpCallback<List<HomePagerRecommendListResponseModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<HomePagerRecommendListResponseModel> data) {
//                if (data != null && data.size() != 0) {
//                    mAdapter.setNewData(data);
//                } else {
//                    showNoData();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                showErrorView();
//                ToastUtil.showToast(SearchActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<List<HomePagerRecommendListResponseModel>>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_NEARBYLIST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("nickname", nickName);
                requestParams.addBodyParameter("page", 1);
                requestParams.addBodyParameter("p_num", 20);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(List<HomePagerRecommendListResponseModel> data, int resultCode, String resultMessage) {
                if (data != null && data.size() != 0) {
                    mAdapter.setNewData(data);
                } else {
                    showNoData();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showErrorView();
                ToastUtil.showToast(SearchActivity.this, resultMessage);
            }
        });
    }

    @Override
    public void setupDatas() {

    }

    private void showErrorView() {
        mAdapter.setEmptyView(R.layout.public_no_network, (ViewGroup) mRecyclerView.getParent());

    }

    private void showNoData() {
        mAdapter.setNewData(null);
        mAdapter.setEmptyView(R.layout.public_no_data, (ViewGroup) mRecyclerView.getParent());
    }

}

package com.bigshark.android.fragments.radiohall;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.LinearLayoutManager;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.UserMenHomePageActivity;
import com.bigshark.android.activities.home.UserWomenHomePageActivity;
import com.bigshark.android.activities.radiohall.CheckRegistrationActivity;
import com.bigshark.android.activities.radiohall.RadioDetailsActivity;
import com.bigshark.android.activities.radiohall.ReleaseRadioActivity;
import com.bigshark.android.adapters.radiohall.RadioHallListAdapter;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.http.model.app.ProvinceModel;
import com.bigshark.android.http.model.radiohall.ClickGoodModel;
import com.bigshark.android.http.model.radiohall.CommentBroadcastModel;
import com.bigshark.android.http.model.radiohall.EnrollBroadcastModel;
import com.bigshark.android.http.model.radiohall.RadioHallModel;
import com.bigshark.android.http.model.radiohall.RadioListItemModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.listener.OnSelectCityListener;
import com.bigshark.android.listener.radiohall.OnRadioClickListener;
import com.bigshark.android.listener.radiohall.OnSendClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.popwindow.CommentOrPraisePopupWindow;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.bigshark.android.widget.MyRecyclerView;
import com.bigshark.android.widget.NoEmojiPanelView;
import com.bigshark.android.widget.popupwindow.ChoiceCityPopup;
import com.bigshark.android.widget.popupwindow.CommonOnePopup;
import com.bigshark.android.widget.popupwindow.OneChoiceListPopup;
import com.bigshark.android.widget.popupwindow.ReportPopup;
import com.chad.library.adapter.base.BaseQuickAdapter;

import java.util.ArrayList;
import java.util.List;

/**
 * 广播大厅 fragment
 */
public class RadioHallFragment extends DisplayBaseFragment implements View.OnClickListener, MyRecyclerView.LoadMoreListener, SwipeRefreshLayout.OnRefreshListener {

    public static final int REQUEST_CODE_SELECT = 100;
    private String mTitle;
    private MyRecyclerView mRecycler_view_radiohall;
    private ImageView mIv_radiohall_release_radio;
    private RadioHallListAdapter mAdapter;
    private SwipeRefreshLayout mSwipeRefreshLayout;
    private LinearLayout ll_radiohall_date_range, ll_radiohall_dating_theme, ll_radiohall_gender;
    private TextView tv_radiohall_dating_purpose, tv_radiohall_gender, tv_radiohall_dating_city;
    private int mPage = 1;
    private String mCity = "";
    private String mTheme = "";
    private int mSex = 0;
    private boolean isRefresh = true;
    private boolean canLoading = true;
    private OneChoiceListPopup datingPurposePopup;
    private OneChoiceListPopup chooseGenderPopup;
    private ChoiceCityPopup mChoiceCityPopup;
    private CommonOnePopup mCommonOnePopup;
    private ReportPopup mReportPopup;
    private CommonOnePopup mOverRadioPopup;

    private CommentOrPraisePopupWindow mCommentOrPraisePopupWindow;

    private RadioListItemModel selectItemBean;
    private int selectPosition;
    private ImageView mPraiseImageView;

    private NoEmojiPanelView panel_view;

    private View rootview;

    public static RadioHallFragment getInstance(String title) {
        RadioHallFragment sf = new RadioHallFragment();
        sf.mTitle = title;
        return sf;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_radio_hall;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        rootview = LayoutInflater.from(getContext()).inflate(R.layout.fragment_radio_hall, null);
        mRecycler_view_radiohall = fragmentRoot.findViewById(R.id.recycler_view_radiohall);
        mSwipeRefreshLayout = fragmentRoot.findViewById(R.id.srl_raido_hall);
        mIv_radiohall_release_radio = fragmentRoot.findViewById(R.id.iv_radiohall_release_radio);
        ll_radiohall_date_range = fragmentRoot.findViewById(R.id.ll_radiohall_date_range);
        ll_radiohall_dating_theme = fragmentRoot.findViewById(R.id.ll_radiohall_dating_theme);
        ll_radiohall_gender = fragmentRoot.findViewById(R.id.ll_radiohall_gender);
        tv_radiohall_dating_purpose = fragmentRoot.findViewById(R.id.tv_radiohall_dating_purpose);
        tv_radiohall_gender = fragmentRoot.findViewById(R.id.tv_radiohall_gender);
        tv_radiohall_dating_city = fragmentRoot.findViewById(R.id.tv_radiohall_dating_city);
        panel_view = fragmentRoot.findViewById(R.id.panel_view);
        //评论
        initRecyclerView();
        initPopView();
        getCityJson();
    }

    private void initPopView() {
//        AppTextModel appTextBean = UserCenter.instance().getAppText();
        List<String> genderList = new ArrayList<>();
        genderList.add("Unlimited");
        genderList.add("man");
        genderList.add("woman");
//        datingPurposePopup = new OneChoiceListPopup(act(), "约会目的", appTextBean.getDateProgram());
        chooseGenderPopup = new OneChoiceListPopup(act(), "gender", genderList);
        //约会主题
        datingPurposePopup.setOnConfirmClickListener(new OnConfirmClickListener() {

            @Override
            public void OnConfirmClick(String str) {
                tv_radiohall_dating_purpose.setText(str);
                datingPurposePopup.dismiss();
                if ("Unlimited content".equals(str)) {
                    mTheme = "";
                } else {
                    mTheme = str;
                }
                onRefresh();
            }
        });
        //性别
        chooseGenderPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                tv_radiohall_gender.setText(str);
                chooseGenderPopup.dismiss();
                if ("男".equals(str)) {
                    mSex = 1;
                } else if ("女".equals(str)) {
                    mSex = 2;
                } else {
                    mSex = 0;
                }
                onRefresh();
            }
        });

        mCommonOnePopup = new CommonOnePopup(act(), "You need to send your face photo to the registration～", "choose a photo");
        mCommonOnePopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String data) {
                //TODO 选择一张照片
                mCommonOnePopup.dismiss();
//                ImagePicker.getInstance().setSelectLimit(1);
//                Intent intent1 = new Intent(act(), ImageGridActivity.class);
//                startActivityForResult(intent1, REQUEST_CODE_SELECT);
            }
        });
    }

//    ArrayList<ImageItem> images = null;

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
//        if (resultCode == ImagePicker.RESULT_CODE_ITEMS) {
//            //选择图片返回
//            if (data != null && requestCode == REQUEST_CODE_SELECT) {
//                images = (ArrayList<ImageItem>) data.getSerializableExtra(ImagePicker.EXTRA_RESULT_ITEMS);
//                if (images != null) {
//                    uploadImage(images.get(0));
//                }
//            }
//        }
    }

//    private void uploadImage(ImageItem imageItemList) {
//        FileBean fileBean = new FileBean();
//        fileBean.setUpLoadKey("image");
//        fileBean.setFileSrc(imageItemList.path);
//        HttpApi.app().uploadImage(this, fileBean, new HttpCallback<ImageModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, ImageModel data) {
//                if (data != null && data.getUrl() != null) {
//                    //请求报名接口
//                    enrollBroadcast(data.getUrl());
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
//    }

    /**
     * 报名
     */
    private void enrollBroadcast(String imgUrl) {
//        EnrollBroadcastRequestBean requestBean = new EnrollBroadcastRequestBean();
//        requestBean.setBroadcast_id(selectItemBean.getId());
//        requestBean.setImage(imgUrl);
//        HttpApi.app().enrollBroadcast(mAdapter, requestBean, new HttpCallback<EnrollBroadcastModel>() {
//            @Override
//            public void onSuccess(int code, String message, EnrollBroadcastModel data) {
//                ToastUtil.showToast(act(), "Successful registration");
//                mAdapter.getData().get(selectPosition).setIs_enroll(data.getIs_enrolled());
//                mAdapter.getData().get(selectPosition).setEnrolled_num(data.getEnrolled_num());
//                mAdapter.notifyItemChanged(selectPosition + 1);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(act(), error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<EnrollBroadcastModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_ENROLLBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", selectItemBean.getId());
                requestParams.addBodyParameter("image", imgUrl);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(EnrollBroadcastModel data, int resultCode, String resultMessage) {
                showToast("Successful registration");
                mAdapter.getData().get(selectPosition).setIs_enroll(data.getIs_enrolled());
                mAdapter.getData().get(selectPosition).setEnrolled_num(data.getEnrolled_num());
                mAdapter.notifyItemChanged(selectPosition + 1);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    private void getCityJson() {
//        HttpApi.app().getRegion(this, new BaseRequestBean(), new HttpCallback<List<ProvinceModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<ProvinceModel> data) {
//                if (data != null) {
//                    mChoiceCityPopup = new ChoiceCityPopup(act(), data);
//                    //选择城市
//                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
//                        @Override
//                        public void onConfirmClick(String name) {
//                            tv_radiohall_dating_city.setText(name);
//                            mChoiceCityPopup.dismiss();
//                            mCity = name;
//                            onRefresh();
//                        }
//
//                        @Override
//                        public void onUnlimitedClick(String str) {
//                            tv_radiohall_dating_city.setText(str);
//                            mChoiceCityPopup.dismiss();
//                            mCity = "";
//                            onRefresh();
//                        }
//                    });
//
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<List<ProvinceModel>>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETREGION_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(List<ProvinceModel> data, int resultCode, String resultMessage) {
                if (data != null) {
                    mChoiceCityPopup = new ChoiceCityPopup(act(), data);
                    //选择城市
                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
                        @Override
                        public void onConfirmClick(String name) {
                            tv_radiohall_dating_city.setText(name);
                            mChoiceCityPopup.dismiss();
                            mCity = name;
                            onRefresh();
                        }

                        @Override
                        public void onUnlimitedClick(String str) {
                            tv_radiohall_dating_city.setText(str);
                            mChoiceCityPopup.dismiss();
                            mCity = "";
                            onRefresh();
                        }
                    });
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    private void initRecyclerView() {
        mRecycler_view_radiohall.setLayoutManager(new LinearLayoutManager(act()));
        mAdapter = new RadioHallListAdapter(display(), R.layout.adapter_radiohall_recommend_listitem);
        mRecycler_view_radiohall.setAdapter(mAdapter);
        mRecycler_view_radiohall.setLoadMoreListener(this);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
        View headView = View.inflate(act(), R.layout.headview_radiohall_list, null);
        View footerView = View.inflate(act(), R.layout.footerview_radiohall_list, null);
        mAdapter.addHeaderView(headView);
        mAdapter.addFooterView(footerView);
        mAdapter.setOnRadioClickListener(new OnRadioClickListener() {

            //评论
            @Override
            public void onCommentsClick(int pos) {
                //评论 吊起键盘
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                panel_view.showEmojiPanel();
            }

            //报名
            @Override
            public void onApplyClick(int pos) {
                selectPosition = pos;
                selectItemBean = mAdapter.getItem(pos);
                if (1 == selectItemBean.getIs_oneself()) {
                    //结束报名
                    mOverRadioPopup = new CommonOnePopup(act(), "Are you sure you want to end the broadcast？", "Confirm");
                    mOverRadioPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
                        @Override
                        public void OnConfirmClick(String data) {
                            //结束广播
                            mOverRadioPopup.dismiss();
                            overRaido(pos);
                        }
                    });
                    //显示PopupWindow
                    mOverRadioPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
//                    new XPopup.Builder(act()).asCustom(mOverRadioPopup).show();
                } else {
//                    if (selectItemBean.getSex() == UserCenter.instance().getUserGender()) {
//                        ToastUtil.showToast(act(), "同性之间不能报名");
//                        return;
//                    }
//                    if (1 == selectItemBean.getIs_enroll()) {
//                        ToastUtil.showToast(act(), "您已经报名了");
//                        return;
//                    }
//                    if (1 == UserCenter.instance().getUserGender()) {
//                        if (1 == MmkvGroup.loginInfo().getVipState()) {
//                            new XPopup.Builder(act()).asCustom(mCommonOnePopup).show();
//                        } else {
//                            ToastUtil.showToast(act(), "您还不是VIP，无法报名");
//                        }
//                    } else if (2 == UserCenter.instance().getUserGender()) {
//                        if (1 == MmkvGroup.loginInfo().getIdentifyState()) {
//                            new XPopup.Builder(act()).asCustom(mCommonOnePopup).show();
//                        } else {
//                            ToastUtil.showToast(act(), "您还没有认证，无法报名");
//                        }
//                    }
                }
            }

            //like
            @Override
            public void onPraiseClick(int pos, ImageView imageView) {
                selectPosition = pos;
                mPraiseImageView = imageView;
                requestGiveLike(pos);
            }

            //点击头像
            @Override
            public void onAvatarClick(int pos) {
                selectPosition = pos;
                RadioListItemModel itemBean = mAdapter.getData().get(pos);
                if (itemBean.getIs_official() == 1) {
                    return;
                }
//                if (itemBean.getSex() == UserCenter.instance().getUserGender()) {
//                    ToastUtil.showToast(act(), "同性之间不能查看用户主页");
//                    return;
//                }
                //用户主页
                Intent intentUser = null;
                if (itemBean.getSex() == 1) {
                    intentUser = new Intent(act(), UserMenHomePageActivity.class);
                    intentUser.putExtra(UserMenHomePageActivity.EXTRA_USER_ID, itemBean.getUser_id());
                } else if (itemBean.getSex() == 2) {
                    intentUser = new Intent(act(), UserWomenHomePageActivity.class);
                    intentUser.putExtra(UserWomenHomePageActivity.EXTRA_USER_ID, itemBean.getUser_id());
                }
                act().startActivity(intentUser);
            }

            //查看报名
            @Override
            public void onCheckRegistrationClick(int pos) {
                selectPosition = pos;
                RadioListItemModel itemBean = mAdapter.getData().get(pos);
                Intent intent = new Intent(act(), CheckRegistrationActivity.class);
                intent.putExtra(CheckRegistrationActivity.EXTRA_ID, itemBean.getId());
                act().startActivity(intent);
            }

            //举报
            @Override
            public void onReportClick(int pos, View view) {
                selectPosition = pos;
                mReportPopup = new ReportPopup(act(), mAdapter.getItem(selectPosition).getId());
//                new XPopup.Builder(act()).hasShadowBg(false).atView(view).asCustom(mReportPopup).show();
                //显示PopupWindow
                mReportPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        });
    }

    @Override
    protected void setupDatas() {

    }

    /**
     * 请求广播数据
     */
    private void requestData() {
        showProgressBar();
//        RadioHallListRequestBean requestBean = new RadioHallListRequestBean();
//        requestBean.setP_num(20);
//        requestBean.setPage(mPage);
//        requestBean.setCity(mCity);
//        requestBean.setSex(mSex);
//        requestBean.setTheme(mTheme);
//        HttpApi.app().getBroadcastList(act(), requestBean, new HttpCallback<RadioHallModel>() {
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
//                    } else if (mPage != 1) {
//                        showNoMoreData();
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
//                ToastUtil.showToast(act(), error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<RadioHallModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETBROADCASTLIST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("p_num", 20);
                requestParams.addBodyParameter("page", mPage);
                requestParams.addBodyParameter("city", mCity);
                requestParams.addBodyParameter("sex", mSex);
                requestParams.addBodyParameter("theme", mTheme);
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
                    } else if (mPage != 1) {
                        showNoMoreData();
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
                ToastUtil.showToast(act(), resultMessage);
            }
        });
    }

    @Override
    protected void bindListeners() {
        mIv_radiohall_release_radio.setOnClickListener(this);
        ll_radiohall_date_range.setOnClickListener(this);
        ll_radiohall_dating_theme.setOnClickListener(this);
        ll_radiohall_gender.setOnClickListener(this);
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {

            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                selectPosition = position;
                //广播详情
                RadioListItemModel itemBean = (RadioListItemModel) adapter.getData().get(position);
                if (1 == itemBean.getIs_official()) {
                    return;
                }
                Intent intent = new Intent(act(), RadioDetailsActivity.class);
                intent.putExtra(RadioDetailsActivity.EXTRA_ID, itemBean.getId());
                startActivity(intent);
            }
        });
        panel_view.setOnSendClickListener(new OnSendClickListener() {
            @Override
            public void onSendContent(String content) {
                if (StringUtil.isBlank(content)) {
                    ToastUtil.showToast(act(), "Comment content cannot be empty");
                    return;
                }
                //请求评论接口
                commentBroadcast(content);
            }
        });
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
//        HttpApi.app().commentBroadcast(act(), requestBean, new HttpCallback<CommentBroadcastModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, CommentBroadcastModel data) {
//                panel_view.clearInputContent();
//                panel_view.dismiss();
//                ToastUtil.showToast(act(), "Comment successful");
//                mAdapter.getItem(selectPosition).setCommented_num(data.getCommented_num());
//                mAdapter.getItem(selectPosition).setIs_comment(data.getIs_commented());
//                mAdapter.notifyItemChanged(selectPosition + 1);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(act(), error.getErrMessage());
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
                ToastUtil.showToast(act(), "Comment successful");
                mAdapter.getItem(selectPosition).setCommented_num(data.getCommented_num());
                mAdapter.getItem(selectPosition).setIs_comment(data.getIs_commented());
                mAdapter.notifyItemChanged(selectPosition + 1);
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
            //发布广播
            case R.id.iv_radiohall_release_radio:
                startActivity(new Intent(act(), ReleaseRadioActivity.class));
                break;
            //约会范围
            case R.id.ll_radiohall_date_range:
                if (mChoiceCityPopup != null) {
//                    new XPopup.Builder(act()).asCustom(mChoiceCityPopup).show();
                    mChoiceCityPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
                break;
            //约会主题
            case R.id.ll_radiohall_dating_theme:
//                new XPopup.Builder(act()).asCustom(datingPurposePopup).show();
                //显示PopupWindow
                datingPurposePopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            //性别
            case R.id.ll_radiohall_gender:
//                new XPopup.Builder(act()).asCustom(chooseGenderPopup).show();
                chooseGenderPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            default:
                break;
        }
    }

    @Override
    public void onRefresh() {
        isRefresh = true;
        mPage = 1;
        requestData();
    }

    @Override
    public void loadMore() {
        if (canLoading) {
            requestData();
            canLoading = false;
        }
    }

    private void showNoData() {
        mAdapter.setNewData(null);
        mAdapter.setEmptyView(R.layout.public_no_data, (ViewGroup) mRecycler_view_radiohall.getParent());
    }

    public void showErrorView() {
        canLoading = true;
        mAdapter.setEmptyView(R.layout.public_no_network, (ViewGroup) mRecycler_view_radiohall.getParent());
    }

    public void showNoMoreData() {
        canLoading = false;
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

    @Override
    public void onHiddenChanged(boolean hidden) {
        super.onHiddenChanged(hidden);
        if (hidden) {// // 不在最前端显示 相当于调用了onPause();

        } else {// 在最前端显示 相当于调用了onResume();
            requestData();
        }
    }

    /**
     * 请求点赞
     */
    private void requestGiveLike(int position) {
        RadioListItemModel itemBean = mAdapter.getData().get(position);
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(itemBean.getId());
//        HttpApi.app().clickGoodBroadcast(act(), requestBean, new HttpCallback<ClickGoodModel>() {
//            @Override
//            public void onSuccess(int code, String message, ClickGoodModel data) {
//                mAdapter.getItem(position).setClick_good_num(Integer.parseInt(data.getClick_good_num()));
//                mAdapter.getItem(position).setIs_click_good(1);
//                mAdapter.notifyItemChanged(position + 1);
//
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(act(), error.getErrMessage());
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
                mAdapter.notifyItemChanged(position + 1);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
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
//                mAdapter.remove(position);
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
                mAdapter.remove(position);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }
}

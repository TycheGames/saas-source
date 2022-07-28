package com.bigshark.android.activities.radiohall;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.content.ContextCompat;
import android.support.v4.view.ViewPager;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.activities.home.UserMenHomePageActivity;
import com.bigshark.android.activities.home.UserWomenHomePageActivity;
import com.bigshark.android.activities.home.ViewUserPhotoActivity;
import com.bigshark.android.adapters.home.ViewUserPhotoAdapter;
import com.bigshark.android.fragments.radiohall.RadioDetailCommentsListFragment;
import com.bigshark.android.fragments.radiohall.RadioDetailPraiseListFragment;
import com.bigshark.android.http.model.radiohall.ClickGoodModel;
import com.bigshark.android.http.model.radiohall.CommentBroadcastModel;
import com.bigshark.android.http.model.radiohall.EnrollBroadcastModel;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;
import com.bigshark.android.http.model.user.PicsModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.SoftKeyBoardListener;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.bigshark.android.utils.ViewUtil;
import com.bigshark.android.widget.MyViewPager;
import com.bigshark.android.widget.popupwindow.CommonOnePopup;
import com.bigshark.android.widget.popupwindow.ReportPopup;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.flyco.tablayout.SlidingTabLayout;
import com.flyco.tablayout.listener.OnTabSelectListener;
import com.gyf.immersionbar.ImmersionBar;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.ArrayList;
import java.util.List;

import butterknife.ButterKnife;
import butterknife.OnClick;

//import com.shuimiao.sangeng.imagepicker.ImagePicker;
//import com.shuimiao.sangeng.imagepicker.bean.ImageItem;
//import com.shuimiao.sangeng.imagepicker.ui.ImageGridActivity;
//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * 广播详情 activity
 */
public class RadioDetailsActivity extends com.bigshark.android.display.DisplayBaseActivity {

    public static final int REQUEST_CODE_SELECT = 100;

    public static final String EXTRA_ID = "EXTRA_ID";
    private SlidingTabLayout mSlidingTabLayout;
    private MyViewPager mViewPager;
    private ArrayList<Fragment> mFragments = new ArrayList<>();
    private final String[] mTitles = {"like", "comment"};
    private MyPagerAdapter mAdapter;
    private RaidoDetailsModel mDetailsBean;
    private String mRadioId;

    private ImageView iv_radiodetails_head, iv_radiodetails_gender, iv_radiodetails_vip, mIv_radiohall_item_like_icon,
            iv_radiohall_item_comment_icon, iv_radiohall_item_apply, iv_titlebar_left_back, iv_radiodetails_to_report,
            iv_send_comments;
    private TextView tv_title, tv_radiodetails_nickname, tv_radiodetails_creat_time, tv_radiodetails_theme, tv_radiodetails_appointment_time,
            tv_radiodetails_city, tv_radiodetails_hope, tv_radiodetails_supplement, mTv_radiohall_item_like_number,
            tv_radiohall_item_comment, tv_radiohall_item_myapply_number, tv_radiohall_item_apply;
    private RecyclerView mRecyclerView;
    private LinearLayout ll_radiohall_item_like, ll_radiohall_item_comment, ll_radiohall_item_check_registration,
            ll_radiohall_item_apply, ll_praise_and_comments, ll_radiodetails_supplement, ll_input_box_bottom;
    private RelativeLayout rl_bottom, input_box;
    private EditText edit_text;
    private ViewUserPhotoAdapter mPhotoAdapter;
    private List<PicsModel> mPicsModelList;

    private ReportPopup mReportPopup;
    private CommonOnePopup mApplyPopup;
    private CommonOnePopup mOverRadioPopup;
    private CommonOnePopup mCommonOnePopup;

    private RadioDetailPraiseListFragment mPraiseListFragment;
    private RadioDetailCommentsListFragment mCommentsListFragment;
    private View rootview;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_radio_details;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_radio_details, null);
        mRadioId = getIntent().getStringExtra(EXTRA_ID);
        mSlidingTabLayout = findViewById(R.id.tab_layout_radio_details);
        mViewPager = findViewById(R.id.vp_radio_details);
        mPraiseListFragment = new RadioDetailPraiseListFragment();
        mCommentsListFragment = new RadioDetailCommentsListFragment();
        mFragments.add(mPraiseListFragment);
        mFragments.add(mCommentsListFragment);
        mAdapter = new MyPagerAdapter(getSupportFragmentManager());
        mViewPager.setAdapter(mAdapter);
        mSlidingTabLayout.setViewPager(mViewPager, mTitles, this, mFragments);
        mViewPager.setCurrentItem(0);

        tv_title = findViewById(R.id.tv_title);
        iv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        iv_radiodetails_head = findViewById(R.id.iv_radiodetails_head);
        tv_radiodetails_nickname = findViewById(R.id.tv_radiodetails_nickname);
        iv_radiodetails_gender = findViewById(R.id.iv_radiodetails_gender);
        iv_radiodetails_vip = findViewById(R.id.iv_radiodetails_vip);
        tv_radiodetails_creat_time = findViewById(R.id.tv_radiodetails_creat_time);
        tv_radiodetails_theme = findViewById(R.id.tv_radiodetails_theme);
        tv_radiodetails_appointment_time = findViewById(R.id.tv_radiodetails_appointment_time);
        tv_radiodetails_city = findViewById(R.id.tv_radiodetails_city);
        tv_radiodetails_hope = findViewById(R.id.tv_radiodetails_hope);
        tv_radiodetails_supplement = findViewById(R.id.tv_radiodetails_supplement);
        mRecyclerView = findViewById(R.id.recyclerview_radiodetails);
        ll_radiohall_item_like = findViewById(R.id.ll_radiohall_item_like);
        mIv_radiohall_item_like_icon = findViewById(R.id.iv_radiohall_item_like_icon);
        mTv_radiohall_item_like_number = findViewById(R.id.tv_radiohall_item_like_number);
        ll_radiohall_item_comment = findViewById(R.id.ll_radiohall_item_comment);
        iv_radiohall_item_comment_icon = findViewById(R.id.iv_radiohall_item_comment_icon);
        tv_radiohall_item_comment = findViewById(R.id.tv_radiohall_item_comment);
        ll_radiohall_item_check_registration = findViewById(R.id.ll_radiohall_item_check_registration);
        tv_radiohall_item_myapply_number = findViewById(R.id.tv_radiohall_item_myapply_number);
        ll_radiohall_item_apply = findViewById(R.id.ll_radiohall_item_apply);
        iv_radiohall_item_apply = findViewById(R.id.iv_radiohall_item_apply);
        tv_radiohall_item_apply = findViewById(R.id.tv_radiohall_item_apply);
        ll_praise_and_comments = findViewById(R.id.ll_praise_and_comments);
        iv_radiodetails_to_report = findViewById(R.id.iv_radiodetails_to_report);
        ll_radiodetails_supplement = findViewById(R.id.ll_radiodetails_supplement);
        rl_bottom = findViewById(R.id.rl_bottom);
        input_box = findViewById(R.id.input_box);
        ll_input_box_bottom = findViewById(R.id.ll_input_box_bottom);
        edit_text = findViewById(R.id.edit_text);
        iv_send_comments = findViewById(R.id.iv_send_comments);

        initRecyclerView();
        initPop();
    }

    private void initPop() {
        mApplyPopup = new CommonOnePopup(this, "报名需要发你的正脸照给对方哦～", "选择照片");
        mApplyPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String data) {
                //TODO 选择一张照片
                ToastUtil.showToast(RadioDetailsActivity.this, mDetailsBean.getCreated_at());
                mApplyPopup.dismiss();
            }
        });

        //广播报名
        mCommonOnePopup = new CommonOnePopup(this, "报名需要发你的正脸照给对方哦～", "选择照片");
        mCommonOnePopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String data) {
                //TODO 选择一张照片
                mCommonOnePopup.dismiss();
//                ImagePicker.getInstance().setSelectLimit(1);
//                Intent intent1 = new Intent(RadioDetailsActivity.this, ImageGridActivity.class);
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
//        requestBean.setBroadcast_id(mDetailsBean.getId());
//        requestBean.setImage(imgUrl);
//        HttpApi.app().enrollBroadcast(mAdapter, requestBean, new HttpCallback<EnrollBroadcastModel>() {
//            @Override
//            public void onSuccess(int code, String message, EnrollBroadcastModel data) {
//                ToastUtil.showToast(RadioDetailsActivity.this, "Successful registration");
//                //刷新数据
//                setupDatas();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(RadioDetailsActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<EnrollBroadcastModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_ENROLLBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mDetailsBean.getId());
                requestParams.addBodyParameter("image", imgUrl);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(EnrollBroadcastModel data, int resultCode, String resultMessage) {
                showToast("Successful registration");
                //刷新数据
                setupDatas();
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
    private void overRaido() {
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(mDetailsBean.getId());
//        HttpApi.app().endBroadcast(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                setupDatas();
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
                requestParams.addBodyParameter("broadcast_id", mDetailsBean.getId());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                setupDatas();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new GridLayoutManager(this, 3));
        mPhotoAdapter = new ViewUserPhotoAdapter(this);
        mRecyclerView.setAdapter(mPhotoAdapter);
        mPhotoAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                ViewUserPhotoActivity.openIntent(RadioDetailsActivity.this, JSON.toJSONString(mPicsModelList), position);
            }
        });
    }

    @OnClick({R.id.ll_radiohall_item_like, R.id.ll_radiohall_item_comment,
            R.id.ll_radiohall_item_check_registration, R.id.ll_radiohall_item_apply,
            R.id.iv_titlebar_left_back, R.id.iv_radiodetails_head,
            R.id.iv_radiodetails_to_report, R.id.iv_send_comments})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_titlebar_left_back:
                finish();
                break;
            //点赞
            case R.id.ll_radiohall_item_like:
                requestGiveLike();
                break;
            //评论
            case R.id.ll_radiohall_item_comment:
                ViewUtil.showKeyboard(this, true);
                edit_text.requestFocus();
                //                panel_view.showEmojiPanel();
                break;
            //查看报名
            case R.id.ll_radiohall_item_check_registration:
                Intent intent = new Intent(this, CheckRegistrationActivity.class);
                intent.putExtra(CheckRegistrationActivity.EXTRA_ID, mDetailsBean.getId());
                startActivity(intent);
                break;
            //报名
            case R.id.ll_radiohall_item_apply:
                if (1 == mDetailsBean.getIs_oneself()) {
                    //结束报名
                    mOverRadioPopup = new CommonOnePopup(this, "Are you sure you want to end the broadcast？", "Confirm");
                    mOverRadioPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
                        @Override
                        public void OnConfirmClick(String data) {
                            //结束广播
                            mOverRadioPopup.dismiss();
                            overRaido();
                        }
                    });
//                    new XPopup.Builder(this).asCustom(mOverRadioPopup).show();
                    mOverRadioPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                } else {
//                    if (mDetailsBean.getSex() == UserCenter.instance().getUserGender()) {
//                        showToast("同性之间不能报名");
//                        return;
//                    }
                    if (1 == mDetailsBean.getIs_enroll()) {
                        showToast("您已经报名了");
                        return;
                    }
//                    if (1 == UserCenter.instance().getUserGender()) {
//                        if (1 == MmkvGroup.loginInfo().getVipState()) {
//                            new XPopup.Builder(this).asCustom(mCommonOnePopup).show();
//                        } else {
//                            showToast("您还不是VIP，无法报名");
//                        }
//                    } else if (2 == UserCenter.instance().getUserGender()) {
//                        if (1 == MmkvGroup.loginInfo().getIdentifyState()) {
//                            new XPopup.Builder(this).asCustom(mCommonOnePopup).show();
//                        } else {
//                            showToast("您还没有认证，无法报名");
//                        }
//                    }
                }
                break;
            //头像
            case R.id.iv_radiodetails_head:
//                if (mDetailsBean.getSex() == UserCenter.instance().getUserGender()) {
//                    showToast("同性之间不能查看用户主页");
//                    return;
//                }
                //用户主页
                Intent intentUser = null;
                if (mDetailsBean.getSex() == 1) {
                    intentUser = new Intent(this, UserMenHomePageActivity.class);
                    intentUser.putExtra(UserMenHomePageActivity.EXTRA_USER_ID, mDetailsBean.getUser_id());
                } else if (mDetailsBean.getSex() == 2) {
                    intentUser = new Intent(this, UserWomenHomePageActivity.class);
                    intentUser.putExtra(UserWomenHomePageActivity.EXTRA_USER_ID, mDetailsBean.getUser_id());
                }
                startActivity(intentUser);
                break;
            //举报
            case R.id.iv_radiodetails_to_report:
                mReportPopup = new ReportPopup(this, mDetailsBean.getId());
//                new XPopup.Builder(this).hasShadowBg(false).atView(iv_radiodetails_to_report).asCustom(mReportPopup).show();
                mReportPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            case R.id.iv_send_comments:
                if (StringUtil.isBlankEdit(edit_text)) {
                    ToastUtil.showToast(RadioDetailsActivity.this, "Comment content cannot be empty");
                    return;
                }
                //请求评论接口
                commentBroadcast(edit_text.getText().toString().trim());
                break;
        }
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        mSlidingTabLayout.setOnTabSelectListener(new OnTabSelectListener() {
            @Override
            public void onTabSelect(int position) {

            }

            @Override
            public void onTabReselect(int position) {

            }
        });
        mViewPager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int i, float v, int i1) {

            }

            @Override
            public void onPageSelected(int i) {

            }

            @Override
            public void onPageScrollStateChanged(int i) {

            }
        });
        SoftKeyBoardListener softKeyBoardListener = new SoftKeyBoardListener(this);
        softKeyBoardListener.setListener(this, new SoftKeyBoardListener.OnSoftKeyBoardChangeListener() {
            @Override
            public void keyBoardShow(int height) {
                rl_bottom.setVisibility(View.VISIBLE);
                ViewGroup.LayoutParams lp = ll_input_box_bottom.getLayoutParams();
                lp.height = height;
                ll_input_box_bottom.setLayoutParams(lp);

                //                ToastUtil.showToast(RadioDetailsActivity.this,"键盘显示了，键盘高度为"+height);
            }

            @Override
            public void keyBoardHide(int height) {
                //                ToastUtil.showToast(RadioDetailsActivity.this,"键盘隐藏了，键盘高度为"+height);
                rl_bottom.setVisibility(View.GONE);
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
//        requestBean.setBroadcast_id(mDetailsBean.getId());
//        requestBean.setContent(content);
//        HttpApi.app().commentBroadcast(this, requestBean, new HttpCallback<CommentBroadcastModel>() {
//            @Override
//            public void onSuccess(int code, String message, CommentBroadcastModel data) {
//                ToastUtil.showToast(RadioDetailsActivity.this, "Comment successful");
//                setupDatas();
//                InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
//                imm.hideSoftInputFromWindow(edit_text.getWindowToken(), 0);
//                edit_text.clearFocus();
//                edit_text.setText(null);
//                mSlidingTabLayout.setCurrentTab(1);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(RadioDetailsActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<CommentBroadcastModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_COMMENTBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mDetailsBean.getId());
                requestParams.addBodyParameter("content", content);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(CommentBroadcastModel data, int resultCode, String resultMessage) {
                ToastUtil.showToast(RadioDetailsActivity.this, "Comment successful");
                setupDatas();
                InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
                imm.hideSoftInputFromWindow(edit_text.getWindowToken(), 0);
                edit_text.clearFocus();
                edit_text.setText(null);
                mSlidingTabLayout.setCurrentTab(1);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void setupDatas() {
        if (mRadioId == null) {
            return;
        }
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(mRadioId);
//        HttpApi.app().getBroadcastDetail(this, requestBean, new HttpCallback<RaidoDetailsModel>() {
//            @Override
//            public void onSuccess(int code, String message, RaidoDetailsModel data) {
//                if (data != null) {
//                    mDetailsBean = data;
//                    setData();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(RadioDetailsActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<RaidoDetailsModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETBROADCASTDETAIL_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mRadioId);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(RaidoDetailsModel data, int resultCode, String resultMessage) {
                if (data != null) {
                    mDetailsBean = data;
                    setData();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    private void setData() {
        if (1 == mDetailsBean.getIs_oneself()) {
            tv_title.setText("我的广播");
        } else {
            tv_title.setText(mDetailsBean.getNickname() + "的广播");
        }
        tv_radiodetails_nickname.setText(mDetailsBean.getNickname());
        iv_radiodetails_gender.setImageDrawable(mDetailsBean.getSex() == 1 ? ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_gender_men_icon) : ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_gender_women_icon));
        if (1 == mDetailsBean.getSex()) {
            x.image().bind(
                    iv_radiodetails_head,
                    mDetailsBean.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            if (1 == mDetailsBean.getIsVip()) {
                iv_radiodetails_vip.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_vip_icon));
            } else {
                iv_radiodetails_vip.setVisibility(View.GONE);
            }
        } else if (2 == mDetailsBean.getSex()) {

            x.image().bind(
                    iv_radiodetails_head,
                    mDetailsBean.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );

            if (1 == mDetailsBean.getIsIdentify()) {
                iv_radiodetails_vip.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_certified_icon));
            } else {
                iv_radiodetails_vip.setVisibility(View.GONE);
            }
        }
        tv_radiodetails_creat_time.setText(mDetailsBean.getCreated_at());
        tv_radiodetails_theme.setText(mDetailsBean.getTheme());
        tv_radiodetails_appointment_time.setText(mDetailsBean.getDate() + mDetailsBean.getTime_slot());
        tv_radiodetails_city.setText(mDetailsBean.getCity());
        tv_radiodetails_hope.setText("约会期望：" + mDetailsBean.getHope());
        if (StringUtil.isBlank(mDetailsBean.getSupplement())) {
            ll_radiodetails_supplement.setVisibility(View.GONE);
        } else {
            ll_radiodetails_supplement.setVisibility(View.VISIBLE);
            tv_radiodetails_supplement.setText(mDetailsBean.getSupplement());
        }
        //图片
        if (1 == mDetailsBean.getBroadcast_img() && mDetailsBean.getImg() != null && mDetailsBean.getImg().size() > 0) {
            mRecyclerView.setVisibility(View.VISIBLE);
            if (mPicsModelList == null) {
                mPicsModelList = new ArrayList<>();
            } else {
                mPicsModelList.clear();
            }
            for (int i = 0; i < mDetailsBean.getImg().size(); i++) {
                PicsModel picsModel = new PicsModel();
                picsModel.setPic_url(mDetailsBean.getImg().get(i));
                mPicsModelList.add(picsModel);
            }
            mPhotoAdapter.setNewData(mPicsModelList);
        } else {
            mRecyclerView.setVisibility(View.GONE);
        }

        //点赞
        if (mDetailsBean.getClick_good_num() > 0) {
            mTv_radiohall_item_like_number.setText(mDetailsBean.getClick_good_num() + "");
        } else {
            mTv_radiohall_item_like_number.setText("like");
        }

        if (1 == mDetailsBean.getIs_click_good()) {
            mIv_radiohall_item_like_icon.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_givelike_icon));
            ll_radiohall_item_like.setEnabled(false);
        } else {
            mIv_radiohall_item_like_icon.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_ungivelike_icon));
            ll_radiohall_item_like.setEnabled(true);
        }

        //是否可评论
        if (1 == mDetailsBean.getComment_status()) {
            ll_radiohall_item_comment.setEnabled(true);
            iv_radiohall_item_comment_icon.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_no_comments_icon));
            tv_radiohall_item_comment.setText("comment");
            tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(this, R.color.color_666666));
        } else {
            iv_radiohall_item_comment_icon.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_no_comments_icon));
            tv_radiohall_item_comment.setText("Comments are closed");
            tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(this, R.color.color_cfcfd2));
            ll_radiohall_item_comment.setEnabled(false);
        }
        //查看报名
        if (1 == mDetailsBean.getIs_oneself()) {
            iv_radiodetails_to_report.setVisibility(View.GONE);
            ll_radiohall_item_check_registration.setVisibility(View.VISIBLE);
            tv_radiohall_item_myapply_number.setText("View registration(" + mDetailsBean.getEnrolled_num() + ")");
        } else {
            iv_radiodetails_to_report.setVisibility(View.VISIBLE);
            ll_radiohall_item_check_registration.setVisibility(View.INVISIBLE);
        }
        //报名
        if (1 == mDetailsBean.getStatus()) {
            ll_radiohall_item_apply.setVisibility(View.VISIBLE);
            if (1 == mDetailsBean.getIs_oneself()) {
                iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_overradio_textleft_icon));
                tv_radiohall_item_apply.setText("Close registration");
                tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(this, R.color.color_666666));
            } else {
                if (1 == mDetailsBean.getIs_enroll()) {
                    iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_applied_textleft_icon));
                    tv_radiohall_item_apply.setText("Successful registration(" + mDetailsBean.getEnrolled_num() + ")");
                    tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(this, R.color.color_666666));
                } else {
                    iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(this, R.mipmap.radiohall_listitem_apply_textleft_icon));
                    tv_radiohall_item_apply.setText("I want to sign up(" + mDetailsBean.getEnrolled_num() + ")");
                    tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(this, R.color.color_161618));
                }
            }
        } else if (2 == mDetailsBean.getStatus()) {
            ll_radiohall_item_apply.setVisibility(View.INVISIBLE);
        }
        if ((mDetailsBean.getClick_good() != null && mDetailsBean.getClick_good().size() > 0) || (mDetailsBean.getCommented_list() != null && mDetailsBean.getCommented_list().size() > 0)) {
            ll_praise_and_comments.setVisibility(View.VISIBLE);
            //点赞列表
            mPraiseListFragment.setData(mDetailsBean.getClick_good());
            //评论列表
            mCommentsListFragment.setData(mDetailsBean.getCommented_list());
        } else {
            ll_praise_and_comments.setVisibility(View.GONE);
        }

    }

    private class MyPagerAdapter extends FragmentPagerAdapter {
        public MyPagerAdapter(FragmentManager fm) {
            super(fm);
        }

        @Override
        public int getCount() {
            return mFragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return mTitles[position];
        }

        @Override
        public Fragment getItem(int position) {
            return mFragments.get(position);
        }
    }

    /**
     * 请求点赞
     */
    private void requestGiveLike() {
//        ClickGoodRequestBean requestBean = new ClickGoodRequestBean();
//        requestBean.setBroadcast_id(mDetailsBean.getId());
//        HttpApi.app().clickGoodBroadcast(this, requestBean, new HttpCallback<ClickGoodModel>() {
//            @Override
//            public void onSuccess(int code, String message, ClickGoodModel data) {
//                setupDatas();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(RadioDetailsActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<ClickGoodModel>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_CLICKGOODBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mDetailsBean.getId());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(ClickGoodModel data, int resultCode, String resultMessage) {
                setupDatas();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

}

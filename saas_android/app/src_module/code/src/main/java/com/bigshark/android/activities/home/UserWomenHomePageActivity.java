package com.bigshark.android.activities.home;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.support.v4.content.ContextCompat;
import android.support.v4.widget.SwipeRefreshLayout;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.text.TextUtils;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.activities.radiohall.RadioDetailsActivity;
import com.bigshark.android.activities.radiohall.ViewRadioPhotoActivity;
import com.bigshark.android.adapters.home.ViewUserPhotoAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.events.TargetJumpEvent;
import com.bigshark.android.http.model.bean.PaytipsPopupModel;
import com.bigshark.android.http.model.home.EvaluationItemModel;
import com.bigshark.android.http.model.home.TimesNoticeModel;
import com.bigshark.android.http.model.pay.PayAlipayModel;
import com.bigshark.android.http.model.pay.PayBalanceModel;
import com.bigshark.android.http.model.pay.PayResult;
import com.bigshark.android.http.model.pay.PayWeChatModel;
import com.bigshark.android.http.model.pay.PaymentConfirmationModel;
import com.bigshark.android.http.model.user.PicsModel;
import com.bigshark.android.http.model.user.ViewUserProfileBean;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.listener.OnShieldingAndReportClickListener;
import com.bigshark.android.listener.OnTwoButtonClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.bigshark.android.widget.popupwindow.CommonOnePopup;
import com.bigshark.android.widget.popupwindow.ConfirmPaymentPopup;
import com.bigshark.android.widget.popupwindow.EvaluationTaPopup;
import com.bigshark.android.widget.popupwindow.PaytipsPopup;
import com.bigshark.android.widget.popupwindow.PersonalCenterTopRightMenuPopup;
import com.bigshark.android.widget.popupwindow.SendSocialAccountPopup;
import com.bigshark.android.widget.popupwindow.SocialAccountPopup;
import com.bigshark.android.widget.popupwindow.ViewTimesUsedPopup;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.gyf.immersionbar.ImmersionBar;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import butterknife.BindView;
import butterknife.ButterKnife;
import de.greenrobot.event.EventBus;

//import com.alipay.sdk.app.PayTask;
//import com.shuimiao.sangeng.nim.session.SessionHelper;
//import com.bigshark.android.activities.usercenter.UserCenter;
//import com.tencent.mm.opensdk.modelpay.PayReq;
//import com.tencent.mm.opensdk.openapi.IWXAPI;
//import com.tencent.mm.opensdk.openapi.WXAPIFactory;

/**
 * 用户的个人主页 女性
 */
public class UserWomenHomePageActivity extends DisplayBaseActivity implements View.OnClickListener, SwipeRefreshLayout.OnRefreshListener {

    public static final String EXTRA_USER_ID = "user_id";
    @BindView(R.id.iv_womenuserhome_head_portrait)
    ImageView iv_womenuserhome_head_portrait;
    @BindView(R.id.iv_womenuserhome_vip)
    ImageView iv_womenuserhome_vip;
    @BindView(R.id.tv_womenuserhome_nickname)
    TextView tv_womenuserhome_nickname;
    @BindView(R.id.tv_womenuserhome_information)
    TextView tv_womenuserhome_information;
    @BindView(R.id.iv_womenuserhome_authenticated)
    ImageView iv_womenuserhome_authenticated;
    @BindView(R.id.tv_womenuserhome_authentication_described)
    TextView tv_womenuserhome_authentication_described;
    @BindView(R.id.tv_womenuserhome_date_range)
    TextView tv_womenuserhome_date_range;
    @BindView(R.id.tv_womenuserhome_distance)
    TextView tv_womenuserhome_distance;
    @BindView(R.id.tv_womenuserhome_online)
    TextView tv_womenuserhome_online;
    @BindView(R.id.tv_womenuserhome_follow)
    TextView tv_womenuserhome_follow;
    @BindView(R.id.tv_womenuserhome_height)
    TextView tv_womenuserhome_height;
    @BindView(R.id.tv_womenuserhome_weight)
    TextView tv_womenuserhome_weight;
    @BindView(R.id.tv_womenuserhome_bust)
    TextView tv_womenuserhome_bust;
    @BindView(R.id.tv_womenuserhome_bust_unit)
    TextView tv_womenuserhome_bust_unit;
    @BindView(R.id.tv_womenuserhome_show_radio)
    TextView tv_womenuserhome_show_radio;
    @BindView(R.id.ll_no_photo)
    LinearLayout ll_no_photo;
    @BindView(R.id.recycler_view_photo)
    RecyclerView recycler_view_photo;
    @BindView(R.id.tv_womenuserhome_introduce_myself)
    TextView tv_womenuserhome_introduce_myself;
    @BindView(R.id.tv_womenuserhome_date_program)
    TextView tv_womenuserhome_date_program;
    @BindView(R.id.tv_womenuserhome_date_condition)
    TextView tv_womenuserhome_date_condition;
    @BindView(R.id.tv_womenuserhome_check_weixin)
    TextView tv_womenuserhome_check_weixin;
    @BindView(R.id.rl_weixin)
    RelativeLayout rl_weixin;
    @BindView(R.id.tv_womenuserhome_check_qq)
    TextView tv_womenuserhome_check_qq;
    @BindView(R.id.rl_qq)
    RelativeLayout rl_qq;
    @BindView(R.id.tv_womenuserhome_style)
    TextView tv_womenuserhome_style;
    @BindView(R.id.tv_womenuserhome_language)
    TextView tv_womenuserhome_language;
    @BindView(R.id.tv_womenuserhome_affection)
    TextView tv_womenuserhome_affection;
    @BindView(R.id.srl)
    SwipeRefreshLayout mSwipeRefreshLayout;
    @BindView(R.id.iv_titlebar_leftback_user)
    ImageView iv_titlebar_leftback_user;
    @BindView(R.id.title)
    TextView title;
    @BindView(R.id.iv_titlebar_right_more)
    ImageView iv_titlebar_right_more;
    @BindView(R.id.tv_womenuserhome_commenting_on_his)
    TextView tv_womenuserhome_commenting_on_his;
    @BindView(R.id.tv_womenuserhome_private_chat_he)
    TextView tv_womenuserhome_private_chat_he;
    @BindView(R.id.tv_womenuserhome_contact)
    TextView tv_womenuserhome_contact;
    @BindView(R.id.rl_women_homePage)
    RelativeLayout rl_women_homePage;
    private String mUser_id;
    private ViewUserProfileBean mUserProfileBean;
    private List<PicsModel> mPhtotList;
    private ViewUserPhotoAdapter mAdapter;

    private List<EvaluationItemModel> mEvaluationList;
    private EvaluationTaPopup mEvaluationTaPopup;
    private ConfirmPaymentPopup mConfirmPaymentPopup;
    private ViewTimesUsedPopup mViewTimesUsedPopup;
    private CommonOnePopup mSocialAccountHidingPopup;
    private SocialAccountPopup mSocialAccountPopup;
    private SendSocialAccountPopup mSendSocialAccountPopup;
    private PersonalCenterTopRightMenuPopup mRightMenuPopup;
    private PaytipsPopup mPaytipsPopup;

    private PaytipsPopupModel mPaytipsPopupModel;
    private PayAlipayModel mPayAlipayModel;
    private PayWeChatModel mPayWeChatModel;
    private View rootview;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_user_women_home_page;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        EventBus.getDefault().register(this);
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_user_women_home_page, null);
        mUser_id = getIntent().getStringExtra(EXTRA_USER_ID);
        mSwipeRefreshLayout.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
        mSwipeRefreshLayout.setOnRefreshListener(this);
        initRecyclerView();
        initPop();
    }

    /**
     * 初始化pop
     */
    private void initPop() {
        //拉黑 and 举报
        mRightMenuPopup = new PersonalCenterTopRightMenuPopup(this);
        mRightMenuPopup.setOnShieldingAndReportClickListener(new OnShieldingAndReportClickListener() {
            @Override
            public void OnShieldingClick() {
                //拉黑
                requestShielding();
            }

            @Override
            public void OnReportClick() {
                //举报
                mRightMenuPopup.dismiss();
                AnonymousReportingActivity.openIntent(UserWomenHomePageActivity.this, 1, mUserProfileBean.getUser_id(), "");
            }
        });
        //收费弹框
        mPaytipsPopupModel = new PaytipsPopupModel();
        mPaytipsPopupModel.setTitle("联系她");
        mPaytipsPopupModel.setPay_content("付费查看和私聊");
        mPaytipsPopupModel.setPay_number("（12元）");
        mPaytipsPopupModel.setVip_content("成为会员，免费看");
        mPaytipsPopup = new PaytipsPopup(this, mPaytipsPopupModel);
        mPaytipsPopup.setOnTwoButtonClickListener(new OnTwoButtonClickListener() {

            @Override
            public void OnOneButtonClick() {
                mPaytipsPopup.dismiss();
                //弹出 付费弹框
                if (mConfirmPaymentPopup != null) {
                    mConfirmPaymentPopup.setContent(String.valueOf(mUserProfileBean.getFee()));
//                    new XPopup.Builder(UserWomenHomePageActivity.this).asCustom(mConfirmPaymentPopup).show();
                    mConfirmPaymentPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
            }

            @Override
            public void OnTwoButtonClick() {
                //成为会员
                mPaytipsPopup.dismiss();

                BrowserActivity.goIntent(UserWomenHomePageActivity.this, MmkvGroup.global().getVipLink());
            }
        });

        //社交账号隐藏提示pop
        mSocialAccountHidingPopup = new CommonOnePopup(this, "Tips", "The other party has hidden the social account, you can get it through private chat ~", "Talk to her privately");
        mSocialAccountHidingPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                //TODO 去私聊
                mSocialAccountHidingPopup.dismiss();
                privateChatOperation();
            }
        });

        //发送我的社交账号
        mSendSocialAccountPopup = new SendSocialAccountPopup(this);
        mSendSocialAccountPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                //发送账号
                sendAccount(str);
            }
        });
    }

    /**
     * 发送账号
     *
     * @param str
     */
    private void sendAccount(String str) {
//        SendSocialAccountRequestBean requestBean = new SendSocialAccountRequestBean();
//        requestBean.setContact(str);
//        requestBean.setTo_uid(mUser_id);
//        HttpApi.app().sendSocialAccount(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                if (mSendSocialAccountPopup != null) {
//                    mSendSocialAccountPopup.dismiss();
//                }
//                ToastUtil.showToast(UserWomenHomePageActivity.this, "发送成功");
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_SENDSOCIALACCOUNT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("contact", str);
                requestParams.addBodyParameter("to_uid", mUser_id);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                if (mSendSocialAccountPopup != null) {
                    mSendSocialAccountPopup.dismiss();
                }
                showToast("发送成功");
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_titlebar_leftback_user.setOnClickListener(this);
        tv_womenuserhome_commenting_on_his.setOnClickListener(this);
        tv_womenuserhome_private_chat_he.setOnClickListener(this);
        tv_womenuserhome_contact.setOnClickListener(this);
        tv_womenuserhome_follow.setOnClickListener(this);
        tv_womenuserhome_show_radio.setOnClickListener(this);
        tv_womenuserhome_check_weixin.setOnClickListener(this);
        tv_womenuserhome_check_qq.setOnClickListener(this);
        iv_titlebar_right_more.setOnClickListener(this);
        iv_womenuserhome_head_portrait.setOnClickListener(this);

        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                ViewUserPhotoActivity.openIntent(UserWomenHomePageActivity.this, JSON.toJSONString(mPhtotList), position);
            }
        });
    }

    private void initRecyclerView() {
        recycler_view_photo.setLayoutManager(new GridLayoutManager(this, 3));
        mAdapter = new ViewUserPhotoAdapter(this);
        recycler_view_photo.setAdapter(mAdapter);
    }

    @Override
    public void setupDatas() {
    }

    @Override
    protected void onResume() {
        super.onResume();
        requestData();
    }

    /**
     * 获取评价
     */
    private void requestEvaluation() {
        if (mUser_id == null) {
            return;
        }
//        UserDataRequestBean requestBean = new UserDataRequestBean();
//        requestBean.setUid(mUser_id);
//        HttpApi.app().getComment(this, requestBean, new HttpCallback<List<EvaluationItemModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<EvaluationItemModel> data) {
//                if (data != null && data.size() > 0) {
//                    mEvaluationList = data;
//                    if (mEvaluationTaPopup == null) {
//                        mEvaluationTaPopup = new EvaluationTaPopup(UserWomenHomePageActivity.this, mEvaluationList, mUser_id, mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
//                    } else {
//                        mEvaluationTaPopup.setData(mEvaluationList);
//                    }
//                    mEvaluationTaPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
//                        @Override
//                        public void OnConfirmClick(String str) {
//                            if (str != null && str.length() > 0) {
//                                sendComment(str);
//                            }
//                        }
//                    });
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<List<EvaluationItemModel>>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETCOMMENT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("uid", mUser_id);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(List<EvaluationItemModel> data, int resultCode, String resultMessage) {
                if (data != null && data.size() > 0) {
                    mEvaluationList = data;
                    if (mEvaluationTaPopup == null) {
                        mEvaluationTaPopup = new EvaluationTaPopup(display(), mEvaluationList, mUser_id, mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
                    } else {
                        mEvaluationTaPopup.setData(mEvaluationList);
                    }
                    mEvaluationTaPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
                        @Override
                        public void OnConfirmClick(String str) {
                            if (str != null && str.length() > 0) {
                                sendComment(str);
                            }
                        }
                    });
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    /**
     * 发送评价
     */
    private void sendComment(String str) {
//        SendCommentRequestBean requestBean = new SendCommentRequestBean();
//        requestBean.setCid(str);
//        requestBean.setTo_uid(mUserProfileBean.getUser_id());
//        HttpApi.app().sendComment(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, "评价成功");
//                mEvaluationTaPopup.dismiss();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_SENDCOMMENT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("cid", str);
                requestParams.addBodyParameter("to_uid", mUserProfileBean.getUser_id());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                showToast("评价成功");
                mEvaluationTaPopup.dismiss();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    /**
     * 请求用户数据
     */
    private void requestData() {
        if (mUser_id == null) {
            return;
        }
        showProgressBar();
//        UserDataRequestBean requestBean = new UserDataRequestBean();
//        requestBean.setUid(mUser_id);
//        HttpApi.app().getPubInfo(this, requestBean, new HttpCallback<ViewUserProfileBean>() {
//            @Override
//            public void onSuccess(int code, String message, ViewUserProfileBean data) {
//                hideProgressBar();
//                if (data != null) {
//                    mUserProfileBean = data;
//                    requestEvaluation();
//                    initData();
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                hideProgressBar();
//                ToastUtil.showToast(UserWomenHomePageActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<ViewUserProfileBean>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_PUBINFO_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("uid", mUser_id);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(ViewUserProfileBean data, int resultCode, String resultMessage) {
                hideProgressBar();
                if (data != null) {
                    mUserProfileBean = data;
                    requestEvaluation();
                    initData();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                hideProgressBar();
                ToastUtil.showToast(UserWomenHomePageActivity.this, resultMessage);
            }
        });
    }

    private void initData() {
        if (mUserProfileBean.getTimes_notice() != null) {
            TimesNoticeModel noticeBean = mUserProfileBean.getTimes_notice();
            if (noticeBean.isIs_show_profile()) {
                rl_women_homePage.setVisibility(View.VISIBLE);
            } else {
                rl_women_homePage.setVisibility(View.INVISIBLE);
            }
            if (noticeBean.isIs_pop()) {
                //查看用户次数
                mViewTimesUsedPopup = new ViewTimesUsedPopup(this, noticeBean);
//                new XPopup.Builder(UserWomenHomePageActivity.this).asCustom(mViewTimesUsedPopup).show();
                mViewTimesUsedPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        }
        x.image().bind(
                iv_womenuserhome_head_portrait,
                mUserProfileBean.getAvatar(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                        .build()
        );
        tv_womenuserhome_nickname.setText(mUserProfileBean.getNickname());
        tv_womenuserhome_information.setText(mUserProfileBean.getAge() + " | " + mUserProfileBean.getLocation() + " | " + mUserProfileBean.getCareer());
        iv_womenuserhome_authenticated.setImageDrawable(mUserProfileBean.getIsIdentify() ? ContextCompat.getDrawable(this, R.drawable.mine_authenticated_icon) : ContextCompat.getDrawable(this, R.drawable.mine_unauthenticated_icon));
        tv_womenuserhome_authentication_described.setText(mUserProfileBean.getIdentifyDesc());
        tv_womenuserhome_date_range.setText("约会范围：" + mUserProfileBean.getDateRange());
        tv_womenuserhome_distance.setText(mUserProfileBean.getDistance());
        tv_womenuserhome_online.setText(mUserProfileBean.getOnlineDesc());
        tv_womenuserhome_height.setText(mUserProfileBean.getHeight());
        tv_womenuserhome_weight.setText(mUserProfileBean.getWeight());
        tv_womenuserhome_bust.setText(mUserProfileBean.getBust());
        tv_womenuserhome_bust_unit.setText(mUserProfileBean.getBust_unit());
        tv_womenuserhome_date_program.setText(mUserProfileBean.getDate_program());
        tv_womenuserhome_date_condition.setText(mUserProfileBean.getDate_condition());
        if (mUserProfileBean.getIsFav()) {
            tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(this, R.drawable.shape_btn_9e9ea4_corners_50));
            tv_womenuserhome_follow.setText("collected");
            tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(this, R.color.color_cfcfd2));
        } else {
            tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(this, R.drawable.shape_btn_yellow_corners_50));
            tv_womenuserhome_follow.setText("+collect");
            tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(this, R.color.color_232021));
        }
        if (!"0".equals(mUserProfileBean.getBroadcast_id())) {
            tv_womenuserhome_show_radio.setVisibility(View.VISIBLE);
        } else {
            tv_womenuserhome_show_radio.setVisibility(View.GONE);
        }
        tv_womenuserhome_introduce_myself.setText(mUserProfileBean.getSelfIntro());
        if (mUserProfileBean.getPics() != null && mUserProfileBean.getPics().size() > 0) {
            mPhtotList = mUserProfileBean.getPics();
            ll_no_photo.setVisibility(View.GONE);
            recycler_view_photo.setVisibility(View.VISIBLE);
            mAdapter.setNewData(mPhtotList);
        } else {
            ll_no_photo.setVisibility(View.VISIBLE);
            recycler_view_photo.setVisibility(View.GONE);
        }

        if (mUserProfileBean.isIs_unlock()) {
            tv_womenuserhome_check_weixin.setText(mUserProfileBean.getWeixin());
            tv_womenuserhome_check_weixin.setBackground(null);
            tv_womenuserhome_check_weixin.setEnabled(false);
            tv_womenuserhome_check_weixin.setTextColor(ContextCompat.getColor(this, R.color.color_3e3d3d));
            tv_womenuserhome_check_qq.setText(mUserProfileBean.getQq());
            tv_womenuserhome_check_qq.setBackground(null);
            tv_womenuserhome_check_qq.setEnabled(false);
            tv_womenuserhome_check_qq.setTextColor(ContextCompat.getColor(this, R.color.color_3e3d3d));
        } else {
            tv_womenuserhome_check_weixin.setText("View>");
//            tv_womenuserhome_check_weixin.setBackground(ContextCompat.getDrawable(this, R.drawable.nim_shape_theme_color_bg_corners_50));
            tv_womenuserhome_check_weixin.setEnabled(true);
            tv_womenuserhome_check_weixin.setTextColor(ContextCompat.getColor(this, R.color.color_232021));
            tv_womenuserhome_check_qq.setText("View>");
//            tv_womenuserhome_check_qq.setBackground(ContextCompat.getDrawable(this, R.drawable.nim_shape_theme_color_bg_corners_50));
            tv_womenuserhome_check_qq.setEnabled(true);
            tv_womenuserhome_check_qq.setTextColor(ContextCompat.getColor(this, R.color.color_232021));
        }
        tv_womenuserhome_style.setText(mUserProfileBean.getStyle());
        tv_womenuserhome_language.setText(mUserProfileBean.getLanguage());
        tv_womenuserhome_affection.setText(mUserProfileBean.getAffection());
        if (StringUtil.isBlank(mUserProfileBean.getWeixin())) {
            rl_weixin.setVisibility(View.GONE);
        } else {
            rl_weixin.setVisibility(View.VISIBLE);
        }
        if (StringUtil.isBlank(mUserProfileBean.getQq())) {
            rl_qq.setVisibility(View.GONE);
        } else {
            rl_qq.setVisibility(View.VISIBLE);
        }
        if (mEvaluationTaPopup != null) {
            mEvaluationTaPopup.setAvatar(mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
        }

        if (mPaytipsPopupModel != null && mPaytipsPopup != null) {
            mPaytipsPopupModel.setPay_number("（" + mUserProfileBean.getFee() + "元）");
            mPaytipsPopup.setContent(mPaytipsPopupModel);
        }
        //付费弹框
        mConfirmPaymentPopup = new ConfirmPaymentPopup(this, String.valueOf(mUserProfileBean.getFee()));
        mConfirmPaymentPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                if (StringUtil.isBlank(str)) {
                    return;
                }
                switch (str) {
                    //余额支付
                    case ConfirmPaymentPopup.PAYTYPE_BALANCE:
                        requestPayBalance();
                        break;
                    //支付宝支付
                    case ConfirmPaymentPopup.PAYTYPE_ZHIFUBAO:
                        requestAliPay();
                        break;
                    //微信支付
                    case ConfirmPaymentPopup.PAYTYPE_WEIXIN:
                        requestWXPay();
                        break;
                    default:
                        break;
                }
            }
        });

    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_titlebar_leftback_user:
                finish();
                break;
            //评价她
            case R.id.tv_womenuserhome_commenting_on_his:
                if (mUserProfileBean == null) {
                    return;
                }
                if (mEvaluationTaPopup == null) {
                    return;
                }
                if (mUserProfileBean.isAlready_date()) {
                    mEvaluationTaPopup.setAvatar(mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
//                    new XPopup.Builder(this).asCustom(mEvaluationTaPopup).show();
                    mEvaluationTaPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                } else {
                    showToast("你还未约过她，不能评价哦");
                }
                break;
            //私聊她
            case R.id.tv_womenuserhome_private_chat_he:
                if (mUserProfileBean == null) {
                    return;
                }
                privateChatOperation();
                break;
            //联系方式
            case R.id.tv_womenuserhome_contact:
                if (mUserProfileBean == null) {
                    return;
                }
                viewContactInformation();
                break;
            //收藏
            case R.id.tv_womenuserhome_follow:
                if (mUserProfileBean == null) {
                    return;
                }
                if (mUserProfileBean.getIsFav()) {
                    requestFollow(-1);
                } else {
                    requestFollow(0);
                }
                break;
            //广播
            case R.id.tv_womenuserhome_show_radio:
                if (mUserProfileBean == null) {
                    return;
                }
                Intent intent = new Intent(this, RadioDetailsActivity.class);
                intent.putExtra(RadioDetailsActivity.EXTRA_ID, mUserProfileBean.getBroadcast_id());
                startActivity(intent);
                break;
            //查看微信
            case R.id.tv_womenuserhome_check_weixin:
                if (mUserProfileBean == null) {
                    return;
                }
                checkTaAccount(SocialAccountPopup.TYPE_WEIXIN);
                break;
            //查看qq
            case R.id.tv_womenuserhome_check_qq:
                if (mUserProfileBean == null) {
                    return;
                }
                checkTaAccount(SocialAccountPopup.TYPE_QQ);
                break;
            //更多
            case R.id.iv_titlebar_right_more:
                if (mRightMenuPopup == null) {
                    return;
                }
//                new XPopup.Builder(this).asCustom(mRightMenuPopup).show();
                mRightMenuPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            case R.id.iv_womenuserhome_head_portrait:
                if (mUserProfileBean == null) {
                    return;
                }
                ArrayList<String> avatarList = new ArrayList<>();
                avatarList.add(mUserProfileBean.getAvatar());
                ViewRadioPhotoActivity.openIntent(this, avatarList, 0);
                break;
            default:
                break;
        }
    }

    /**
     * 查看联系方式
     */
    private void viewContactInformation() {
        if (1 != MmkvGroup.loginInfo().getVipState() && !mUserProfileBean.isIs_unlock()) {
//            new XPopup.Builder(this).asCustom(mPaytipsPopup).show();
            mPaytipsPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
        } else {
            if (mUserProfileBean.isIs_hidden_social_accounts()) {
                // 提示 隐藏了社交账号，去私聊获取
//                new XPopup.Builder(this).asCustom(mSocialAccountHidingPopup).show();
                mSocialAccountHidingPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            } else {
                if (StringUtil.isBlank(mUserProfileBean.getQq())) {
                    mSocialAccountPopup = new SocialAccountPopup(this, SocialAccountPopup.TYPE_WEIXIN, mUserProfileBean);
                } else if (StringUtil.isBlank(mUserProfileBean.getWeixin())) {
                    mSocialAccountPopup = new SocialAccountPopup(this, SocialAccountPopup.TYPE_QQ, mUserProfileBean);
                } else {
                    mSocialAccountPopup = new SocialAccountPopup(this, SocialAccountPopup.TYPE_ALL, mUserProfileBean);
                }
                mSocialAccountPopup.setOnConfirmClickListener(new OnConfirmClickListener() {

                    @Override
                    public void OnConfirmClick(String str) {
                        //发送我的社交账号给她>
                        mSocialAccountPopup.dismiss();
                        if (mSendSocialAccountPopup != null) {
//                            new XPopup.Builder(UserWomenHomePageActivity.this).asCustom(mSendSocialAccountPopup).show();
                            mSendSocialAccountPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                        }
                    }
                });
//                new XPopup.Builder(this).asCustom(mSocialAccountPopup).show();
                mSocialAccountPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        }
    }

    /**
     * 查看账号
     *
     * @param tupe
     */
    private void checkTaAccount(int tupe) {
        if (1 != MmkvGroup.loginInfo().getVipState() && !mUserProfileBean.isIs_unlock()) {
//            new XPopup.Builder(this).asCustom(mPaytipsPopup).show();
            mPaytipsPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
        } else {
            if (mUserProfileBean.isIs_hidden_social_accounts()) {
                // 提示 隐藏了社交账号，去私聊获取
//                new XPopup.Builder(this).asCustom(mSocialAccountHidingPopup).show();
                mSocialAccountHidingPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            } else {

                mSocialAccountPopup = new SocialAccountPopup(this, tupe, mUserProfileBean);
                mSocialAccountPopup.setOnConfirmClickListener(new OnConfirmClickListener() {

                    @Override
                    public void OnConfirmClick(String str) {
                        //发送我的社交账号给她>
                        mSocialAccountPopup.dismiss();
                        if (mSendSocialAccountPopup != null) {
//                            new XPopup.Builder(UserWomenHomePageActivity.this).asCustom(mSendSocialAccountPopup).show();
                            mSendSocialAccountPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                        }
                    }
                });
//                new XPopup.Builder(this).asCustom(mSocialAccountPopup).show();
                mSocialAccountPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        }
    }

    /**
     * 去私聊的操作逻辑
     */
    private void privateChatOperation() {
        //判断性别
//        if (mUserProfileBean.getSex() == UserCenter.instance().getUserGender()) {
//            showToast("同性之间不能私聊");
//        }
//        else
        if (!mUserProfileBean.isIs_unlock()) {
            if (MmkvGroup.loginInfo().getVipState() != 1) {
                //判断是否是vip 不是 去付钱或成为vip
//                new XPopup.Builder(this).asCustom(mPaytipsPopup).show();
                mPaytipsPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            } else {
//                SessionHelper.startP2PSession(this, mUserProfileBean.getAccid(), mUserProfileBean.getUser_id());
            }
        } else {
//            SessionHelper.startP2PSession(this, mUserProfileBean.getAccid(), mUserProfileBean.getUser_id());
        }
    }

    /**
     * 添加或取消关注 0:添加收藏 -1:取消收藏
     *
     * @param i
     */
    private void requestFollow(int i) {
//        FollowRequestBean requestBean = new FollowRequestBean();
//        requestBean.setFav_uid(mUser_id);
//        requestBean.setType(i);
//        HttpApi.app().switchCollection(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                if (i == -1) {
//                    ToastUtil.showToast(UserWomenHomePageActivity.this, "Cancel collection");
//                    tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(UserWomenHomePageActivity.this, R.drawable.shape_btn_yellow_corners_50));
//                    tv_womenuserhome_follow.setText("+Favorite");
//                    tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(UserWomenHomePageActivity.this, R.color.color_232021));
//                    mUserProfileBean.setIsFav(false);
//                } else {
//                    ToastUtil.showToast(UserWomenHomePageActivity.this, "Favorited");
//                    tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(UserWomenHomePageActivity.this, R.drawable.shape_btn_9e9ea4_corners_50));
//                    tv_womenuserhome_follow.setText("collected");
//                    tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(UserWomenHomePageActivity.this, R.color.color_cfcfd2));
//                    mUserProfileBean.setIsFav(true);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_SWITCHCOLLECTION_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("fav_uid", mUser_id);
                requestParams.addBodyParameter("type", i);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                if (i == -1) {
                    ToastUtil.showToast(UserWomenHomePageActivity.this, "Cancel collection");
                    tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(UserWomenHomePageActivity.this, R.drawable.shape_btn_yellow_corners_50));
                    tv_womenuserhome_follow.setText("+collect");
                    tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(UserWomenHomePageActivity.this, R.color.color_232021));
                    mUserProfileBean.setIsFav(false);
                } else {
                    ToastUtil.showToast(UserWomenHomePageActivity.this, "Favorited");
                    tv_womenuserhome_follow.setBackground(ContextCompat.getDrawable(UserWomenHomePageActivity.this, R.drawable.shape_btn_9e9ea4_corners_50));
                    tv_womenuserhome_follow.setText("collected");
                    tv_womenuserhome_follow.setTextColor(ContextCompat.getColor(UserWomenHomePageActivity.this, R.color.color_cfcfd2));
                    mUserProfileBean.setIsFav(true);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                ToastUtil.showToast(UserWomenHomePageActivity.this, resultMessage);
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

    @Override
    public void onRefresh() {
        requestData();
    }

    /**
     * 请求拉黑
     */
    private void requestShielding() {
//        ShieldingRequestBean requestBean = new ShieldingRequestBean();
//        requestBean.setType(1);
//        requestBean.setBlack_uid(mUser_id);
//        HttpApi.app().addOrCancelBlack(this, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, message);
//                mRightMenuPopup.dismiss();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserWomenHomePageActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_ADDORCANCELBLACK_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("type", 1);
                requestParams.addBodyParameter("black_uid", mUser_id);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                showToast(resultMessage);
                mRightMenuPopup.dismiss();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    /**
     * 支付宝支付
     */
    private void requestAliPay() {
//        PayAlipayRequestBean requestBean = new PayAlipayRequestBean();
//        requestBean.setScene(8);
//        requestBean.setAmount(String.valueOf(mUserProfileBean.getFee()));
//        requestBean.setGoods("{\"for_uid\":" + mUser_id + "}");
//        HttpApi.app().payAlipay(this, requestBean, new HttpCallback<PayAlipayModel>() {
//            @Override
//            public void onSuccess(int code, String message, PayAlipayModel data) {
//                if (data != null) {
//                    mPayAlipayModel = data;
//                    alipay(data.getContents());
//                } else {
//                    payfailure(message);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                payfailure(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PayAlipayModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_PAYALIPAY_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("scene", 8);
                requestParams.addBodyParameter("amount", String.valueOf(mUserProfileBean.getFee()));
                requestParams.addBodyParameter("goods", "{\"for_uid\":" + mUser_id + "}");
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PayAlipayModel data, int resultCode, String resultMessage) {
                if (data != null) {
                    mPayAlipayModel = data;
                    alipay(data.getContents());
                } else {
                    payfailure(resultMessage);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                payfailure(resultMessage);
            }
        });
    }

    private static final int PAY_FLAG = 1;

    private void alipay(String orderInfo) {
        final Runnable payRunnable = new Runnable() {

            @Override
            public void run() {
//                PayTask alipay = new PayTask(UserWomenHomePageActivity.this);
//                Map<String, String> result = alipay.payV2(orderInfo, true);
//                Log.i("msp", result.toString());
//
//                Message msg = new Message();
//                msg.what = PAY_FLAG;
//                msg.obj = result;
//                mHandler.sendMessage(msg);
            }
        };
        // 必须异步调用
        Thread payThread = new Thread(payRunnable);
        payThread.start();
    }

    @SuppressLint("HandlerLeak")
    private Handler mHandler = new Handler() {

        public void handleMessage(Message msg) {
            switch (msg.what) {
                case PAY_FLAG: {
                    @SuppressWarnings("unchecked")
                    PayResult payResult = new PayResult((Map<String, String>) msg.obj);
                    /**
                     * 对于支付结果，请商户依赖服务端的异步通知结果。同步通知结果，仅作为支付结束的通知。
                     */
                    String resultInfo = payResult.getResult();// 同步返回需要验证的信息
                    String resultStatus = payResult.getResultStatus();
                    // 判断resultStatus 为9000则代表支付成功
                    if (TextUtils.equals(resultStatus, "9000")) {
                        // 该笔订单是否真实支付成功，需要依赖服务端的异步通知。
                        getAlipayResult();
                    } else {
                        payfailure("支付失败");
                    }
                    break;
                }
                default:
                    break;
            }
        }
    };

    /**
     * 支付宝 支付确认
     */
    private void getAlipayResult() {
        if (mPayAlipayModel == null) {
            return;
        }
//        PaymentConfirmationRequestBean requestBean = new PaymentConfirmationRequestBean();
//        requestBean.setOrder_no(mPayAlipayModel.getOrder_no());
//        HttpApi.app().getAlipayResult(this, requestBean, new HttpCallback<PaymentConfirmationModel>() {
//            @Override
//            public void onSuccess(int code, String message, PaymentConfirmationModel data) {
//                //支付宝支付成功，刷新数据
//                paySuccess();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                payfailure(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PaymentConfirmationModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETALIPAYRESULT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("order_no", mPayAlipayModel.getOrder_no());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PaymentConfirmationModel data, int resultCode, String resultMessage) {
                //支付宝支付成功，刷新数据
                paySuccess();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                payfailure(resultMessage);
            }
        });
    }

    /**
     * 微信支付
     */
    private void requestWXPay() {
//        PayAlipayRequestBean requestBean = new PayAlipayRequestBean();
//        requestBean.setScene(8);
//        requestBean.setAmount(String.valueOf(mUserProfileBean.getFee()));
//        requestBean.setGoods("{\"for_uid\":" + mUser_id + "}");
//        HttpApi.app().payWeChat(this, requestBean, new HttpCallback<PayWeChatModel>() {
//            @Override
//            public void onSuccess(int code, String message, PayWeChatModel data) {
//                if (data != null) {
//                    mPayWeChatModel = data;
//                    WXPay(data.getContents());
//                } else {
//                    payfailure(message);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                payfailure(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PayWeChatModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_PAYWECHAT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("scene", 8);
                requestParams.addBodyParameter("amount", String.valueOf(mUserProfileBean.getFee()));
                requestParams.addBodyParameter("goods", "{\"for_uid\":" + mUser_id + "}");
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PayWeChatModel data, int resultCode, String resultMessage) {
                if (data != null) {
                    mPayWeChatModel = data;
                    WXPay(data.getContents());
                } else {
                    payfailure(resultMessage);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                payfailure(resultMessage);
            }
        });
    }

    private void WXPay(PayWeChatModel.ContentsBean contents) {
//        IWXAPI api = WXAPIFactory.createWXAPI(this, UserCenter.WX_APP_ID, false);
//        //        api.registerApp(UserCenter.WX_APP_ID);
//        PayReq req = new PayReq();
//        req.appId = contents.getAppid();//你的微信appid
//        req.partnerId = contents.getPartnerid();//商户号
//        req.prepayId = contents.getPrepayid();//预支付交易会话ID
//        req.nonceStr = contents.getNoncestr();//随机字符串
//        req.timeStamp = contents.getTimestamp();//时间戳
//        req.packageValue = contents.getExtPackage();//扩展字段,这里固定填写Sign=WXPay
//        req.sign = contents.getSign();//签名
//        //        req.extData = WXPayEntryActivity.EXT_USERHOMEPAGE;
//        // 在支付之前，如果应用没有注册到微信，应该先调用IWXMsg.registerApp将应用注册到微信
//        api.sendReq(req);
    }

    public void onEventMainThread(TargetJumpEvent event) {
        if (event == null) {
            return;
        }
        if (event.getType() == TargetJumpEvent.EVENT_WEIXINPAY_SUCCESS) {
            if ("0".equals(event.getMessage())) {
                //微信支付回调成功
                getWechatResult();
            } else {
                //支付失败
                payfailure("支付失败");
            }
        }
    }

    /**
     * 微信支付结果确认
     */
    private void getWechatResult() {
//        PaymentConfirmationRequestBean requestBean = new PaymentConfirmationRequestBean();
//        requestBean.setOrder_no(mPayWeChatModel.getOrder_no());
//        HttpApi.app().getWechatResult(this, requestBean, new HttpCallback<PaymentConfirmationModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, PaymentConfirmationModel data) {
//                if (1 == data.getIs_ok()) {
//                    paySuccess();
//                } else {
//                    payfailure(message);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                payfailure(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PaymentConfirmationModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETWECHATRESULT_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("order_no", mPayWeChatModel.getOrder_no());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PaymentConfirmationModel data, int resultCode, String resultMessage) {
                if (1 == data.getIs_ok()) {
                    paySuccess();
                } else {
                    payfailure(resultMessage);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                payfailure(resultMessage);
            }
        });
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        EventBus.getDefault().unregister(this);
    }

    /**
     * 余额支付
     */
    private void requestPayBalance() {
//        PayAlipayRequestBean requestBean = new PayAlipayRequestBean();
//        requestBean.setScene(8);
//        requestBean.setAmount(String.valueOf(mUserProfileBean.getFee()));
//        requestBean.setGoods("{\"for_uid\":" + mUser_id + "}");
//        HttpApi.app().payBalance(this, requestBean, new HttpCallback<PayBalanceModel>() {
//            @Override
//            public void onSuccess(int code, String message, PayBalanceModel data) {
//                //余额支付成功
//                paySuccess();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                payfailure(error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PayBalanceModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_PAYBALANCE_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("scene", 8);
                requestParams.addBodyParameter("amount", String.valueOf(mUserProfileBean.getFee()));
                requestParams.addBodyParameter("goods", "{\"for_uid\":" + mUser_id + "}");
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PayBalanceModel data, int resultCode, String resultMessage) {
                // 余额支付成功
                paySuccess();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                payfailure(resultMessage);
            }
        });
    }

    /**
     * 支付成功 刷新数据
     */
    private void paySuccess() {
        requestData();
        //刷新自己的信息
//        UserCenter.instance().refreshMyUserinfo(this);
    }

    private void payfailure(String errorStr) {
        ToastUtil.showToast(UserWomenHomePageActivity.this, errorStr);
    }

}


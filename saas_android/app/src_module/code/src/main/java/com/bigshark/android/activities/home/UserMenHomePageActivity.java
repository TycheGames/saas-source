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
import com.bigshark.android.widget.popupwindow.ConfirmPaymentPopup;
import com.bigshark.android.widget.popupwindow.EvaluationTaPopup;
import com.bigshark.android.widget.popupwindow.PaytipsPopup;
import com.bigshark.android.widget.popupwindow.PersonalCenterTopRightMenuPopup;
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
 * 用户的个人主页 男性
 */
public class UserMenHomePageActivity extends DisplayBaseActivity implements View.OnClickListener, SwipeRefreshLayout.OnRefreshListener {

    public static final String EXTRA_USER_ID = "user_id";
    @BindView(R.id.iv_menuserhome_head_portrait)
    ImageView iv_menuserhome_head_portrait;
    @BindView(R.id.iv_menuserhome_vip)
    ImageView iv_menuserhome_vip;
    @BindView(R.id.tv_menuserhome_nickname)
    TextView tv_menuserhome_nickname;
    @BindView(R.id.tv_menuserhome_information)
    TextView tv_menuserhome_information;
    @BindView(R.id.iv_menuserhome_authenticated)
    ImageView ivMenuserhomeAuthenticated;
    @BindView(R.id.tv_menuserhome_authentication_described)
    TextView tvMenuserhomeAuthenticationDescribed;
    @BindView(R.id.tv_menuserhome_date_range)
    TextView tv_menuserhome_date_range;
    @BindView(R.id.tv_menuserhome_distance)
    TextView tv_menuserhome_distance;
    @BindView(R.id.tv_menuserhome_online)
    TextView tv_menuserhome_online;
    @BindView(R.id.tv_menuserhome_follow)
    TextView tv_menuserhome_follow;
    @BindView(R.id.tv_menuserhome_show_radio)
    TextView tv_menuserhome_show_radio;
    @BindView(R.id.ll_no_photo)
    LinearLayout ll_no_photo;
    @BindView(R.id.recycler_view_photo)
    RecyclerView recycler_view_photo;
    @BindView(R.id.tv_menuserhome_introduce_myself)
    TextView tv_menuserhome_introduce_myself;
    @BindView(R.id.srl_menuserhome)
    SwipeRefreshLayout srl_menuserhome;
    @BindView(R.id.iv_titlebar_leftback_user)
    ImageView iv_titlebar_leftback_user;
    @BindView(R.id.title)
    TextView title;
    @BindView(R.id.iv_titlebar_right_more)
    ImageView iv_titlebar_right_more;
    @BindView(R.id.tv_menuserhome_commenting_on_his)
    TextView tv_menuserhome_commenting_on_his;
    @BindView(R.id.tv_menuserhome_private_chat_he)
    TextView tv_menuserhome_private_chat_he;
    @BindView(R.id.rl_usermen_home)
    RelativeLayout rl_usermen_home;
    private String mUser_id;

    private ViewUserProfileBean mUserProfileBean;
    private ViewUserPhotoAdapter mAdapter;
    private List<PicsModel> mPhtotList;
    private EvaluationTaPopup mEvaluationTaPopup;
    private List<EvaluationItemModel> mEvaluationList;
    private PaytipsPopupModel mPaytipsPopupModel;
    private PaytipsPopup mPaytipsPopup;
    private ConfirmPaymentPopup mConfirmPaymentPopup;
    private PersonalCenterTopRightMenuPopup mRightMenuPopup;
    private ViewTimesUsedPopup mViewTimesUsedPopup;

    private PayAlipayModel mPayAlipayModel;
    private PayWeChatModel mPayWeChatModel;
    private View rootview;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_user_men_home_page;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        EventBus.getDefault().register(this);
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_user_men_home_page, null);
        mUser_id = getIntent().getStringExtra(EXTRA_USER_ID);
        srl_menuserhome.setColorSchemeResources(R.color.colorPrimary, R.color.colorAccent, R.color.colorPrimaryDark);
        srl_menuserhome.setOnRefreshListener(this);
        initRecyclerView();
        initPopup();
    }

    private void initPopup() {
        //收费弹框
        mPaytipsPopupModel = new PaytipsPopupModel();
        mPaytipsPopupModel.setTitle("contact him");
        mPaytipsPopupModel.setPay_content("Paid Viewing and Private Chat");
        mPaytipsPopupModel.setPay_number("（" + MmkvGroup.global().getPricePrivatechat() + "元）");
        mPaytipsPopupModel.setVip_content("Certified now, free private chat");
        mPaytipsPopup = new PaytipsPopup(this, mPaytipsPopupModel);
        mPaytipsPopup.setOnTwoButtonClickListener(new OnTwoButtonClickListener() {
            @Override
            public void OnOneButtonClick() {
                mPaytipsPopup.dismiss();
                //弹出 付费弹框
                if (mConfirmPaymentPopup != null) {
//                    new XPopup.Builder(UserMenHomePageActivity.this).asCustom(mConfirmPaymentPopup).show();
                    mConfirmPaymentPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
            }

            @Override
            public void OnTwoButtonClick() {
                mPaytipsPopup.dismiss();

                BrowserActivity.goIntent(UserMenHomePageActivity.this, MmkvGroup.global().getAuthCenterLink());
            }
        });

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
                AnonymousReportingActivity.openIntent(UserMenHomePageActivity.this, 1, mUserProfileBean.getUser_id(), "");
            }
        });
    }

    private void initRecyclerView() {
        recycler_view_photo.setLayoutManager(new GridLayoutManager(this, 3));
        mAdapter = new ViewUserPhotoAdapter(this);
        recycler_view_photo.setAdapter(mAdapter);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        iv_titlebar_leftback_user.setOnClickListener(this);
        tv_menuserhome_follow.setOnClickListener(this);
        tv_menuserhome_show_radio.setOnClickListener(this);
        tv_menuserhome_commenting_on_his.setOnClickListener(this);
        tv_menuserhome_private_chat_he.setOnClickListener(this);
        iv_titlebar_right_more.setOnClickListener(this);
        iv_menuserhome_head_portrait.setOnClickListener(this);
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                //                PicsModel picsBean = (PicsModel) adapter.getData().get(position);
                ViewUserPhotoActivity.openIntent(UserMenHomePageActivity.this, JSON.toJSONString(mPhtotList), position);
            }
        });

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
//                        mEvaluationTaPopup = new EvaluationTaPopup(UserMenHomePageActivity.this, mEvaluationList, mUser_id, mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
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
//                ToastUtil.showToast(UserMenHomePageActivity.this, "评价成功");
//                mEvaluationTaPopup.dismiss();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserMenHomePageActivity.this, error.getErrMessage());
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
                showToast("Evaluation success");
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
//                ToastUtil.showToast(UserMenHomePageActivity.this, error.getErrMessage());
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
                ToastUtil.showToast(UserMenHomePageActivity.this, resultMessage);
            }
        });
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_titlebar_leftback_user:
                finish();
                break;
            //收藏
            case R.id.tv_menuserhome_follow:
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
            case R.id.tv_menuserhome_show_radio:
                if (mUserProfileBean == null) {
                    return;
                }
                Intent intent = new Intent(this, RadioDetailsActivity.class);
                intent.putExtra(RadioDetailsActivity.EXTRA_ID, mUserProfileBean.getBroadcast_id());
                startActivity(intent);
                break;
            //评价他
            case R.id.tv_menuserhome_commenting_on_his:
                if (mUserProfileBean == null) {
                    return;
                }
                if (mUserProfileBean.isAlready_date()) {
                    mEvaluationTaPopup.setAvatar(mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
//                    new XPopup.Builder(this).asCustom(mEvaluationTaPopup).show();
                    mEvaluationTaPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                } else {
                    showToast("你还未约过他，不能评价哦");
                }
                break;
            //私聊他
            case R.id.tv_menuserhome_private_chat_he:
                if (mUserProfileBean == null) {
                    return;
                }
                //判断性别
//                if (mUserProfileBean.getSex() == UserCenter.instance().getUserGender()) {
//                    showToast("同性之间不能私聊");
//                }
                else if (!mUserProfileBean.isIs_unlock()) {
                    if (MmkvGroup.loginInfo().getIdentifyState() != 1) {
                        //判断是否已认证 不是 去付钱或成为vip
//                        new XPopup.Builder(this).asCustom(mPaytipsPopup).show();
                        mPaytipsPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                    } else {
//                        SessionHelper.startP2PSession(this, mUserProfileBean.getAccid(), mUserProfileBean.getUser_id());
                    }
                } else {
//                    SessionHelper.startP2PSession(this, mUserProfileBean.getAccid(), mUserProfileBean.getUser_id());
                }
                break;
            //更多
            case R.id.iv_titlebar_right_more:
                if (mRightMenuPopup == null) {
                    return;
                }
//                new XPopup.Builder(this).asCustom(mRightMenuPopup).show();
                mRightMenuPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            //头像
            case R.id.iv_menuserhome_head_portrait:
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

    private void initData() {
        if (mUserProfileBean.getTimes_notice() != null) {
            TimesNoticeModel noticeBean = mUserProfileBean.getTimes_notice();
            if (noticeBean.isIs_show_profile()) {
                rl_usermen_home.setVisibility(View.VISIBLE);
            } else {
                rl_usermen_home.setVisibility(View.INVISIBLE);
            }
            if (noticeBean.isIs_pop()) {
                //查看用户次数
                mViewTimesUsedPopup = new ViewTimesUsedPopup(this, noticeBean);
//                new XPopup.Builder(UserMenHomePageActivity.this).asCustom(mViewTimesUsedPopup).show();
                mViewTimesUsedPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
            }
        }
        x.image().bind(
                iv_menuserhome_head_portrait,
                mUserProfileBean.getAvatar(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                        .build()
        );
        iv_menuserhome_vip.setVisibility(mUserProfileBean.getIsVip() ? View.VISIBLE : View.GONE);
        tv_menuserhome_nickname.setText(mUserProfileBean.getNickname());
        tv_menuserhome_information.setText(mUserProfileBean.getAge() + " | " + mUserProfileBean.getLocation() + " | " + mUserProfileBean.getCareer());
        tv_menuserhome_date_range.setText("Dating Range：" + mUserProfileBean.getDateRange());
        tv_menuserhome_distance.setText(mUserProfileBean.getDistance());
        tv_menuserhome_online.setText(mUserProfileBean.getOnlineDesc());
        if (mUserProfileBean.getIsFav()) {
            tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(this, R.drawable.shape_btn_9e9ea4_corners_50));
            tv_menuserhome_follow.setText("collected");
            tv_menuserhome_follow.setTextColor(ContextCompat.getColor(this, R.color.color_cfcfd2));
        } else {
            tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(this, R.drawable.shape_btn_yellow_corners_50));
            tv_menuserhome_follow.setText("+collect");
            tv_menuserhome_follow.setTextColor(ContextCompat.getColor(this, R.color.color_232021));
        }
        if (!"0".equals(mUserProfileBean.getBroadcast_id())) {
            tv_menuserhome_show_radio.setVisibility(View.VISIBLE);
        } else {
            tv_menuserhome_show_radio.setVisibility(View.GONE);
        }

        tv_menuserhome_introduce_myself.setText(mUserProfileBean.getSelfIntro());
        if (mUserProfileBean.getPics() != null && mUserProfileBean.getPics().size() > 0) {
            mPhtotList = mUserProfileBean.getPics();
            ll_no_photo.setVisibility(View.GONE);
            recycler_view_photo.setVisibility(View.VISIBLE);
            mAdapter.setNewData(mPhtotList);
        } else {
            ll_no_photo.setVisibility(View.VISIBLE);
            recycler_view_photo.setVisibility(View.GONE);
        }
        if (mPaytipsPopupModel != null && mPaytipsPopup != null) {
            mPaytipsPopupModel.setPay_number("（" + mUserProfileBean.getFee() + "元）");
            mPaytipsPopup.setContent(mPaytipsPopupModel);
        }
        if (mEvaluationTaPopup != null) {
            mEvaluationTaPopup.setAvatar(mUserProfileBean.getAvatar(), mUserProfileBean.getSex());
        }
        //付费弹框
        mConfirmPaymentPopup = new ConfirmPaymentPopup(this, String.valueOf(mUserProfileBean.getFee()));
        mConfirmPaymentPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                //选择付费方式
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
//                    ToastUtil.showToast(UserMenHomePageActivity.this, "Cancel collection");
//                    tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(UserMenHomePageActivity.this, R.drawable.shape_btn_yellow_corners_50));
//                    tv_menuserhome_follow.setText("+Favorite");
//                    tv_menuserhome_follow.setTextColor(ContextCompat.getColor(UserMenHomePageActivity.this, R.color.color_232021));
//                    mUserProfileBean.setIsFav(false);
//                } else {
//                    ToastUtil.showToast(UserMenHomePageActivity.this, "Favorited");
//                    tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(UserMenHomePageActivity.this, R.drawable.shape_btn_9e9ea4_corners_50));
//                    tv_menuserhome_follow.setText("collected");
//                    tv_menuserhome_follow.setTextColor(ContextCompat.getColor(UserMenHomePageActivity.this, R.color.color_cfcfd2));
//                    mUserProfileBean.setIsFav(true);
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserMenHomePageActivity.this, error.getErrMessage());
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
                    ToastUtil.showToast(UserMenHomePageActivity.this, "Cancel collection");
                    tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(UserMenHomePageActivity.this, R.drawable.shape_btn_yellow_corners_50));
                    tv_menuserhome_follow.setText("+Favorite");
                    tv_menuserhome_follow.setTextColor(ContextCompat.getColor(UserMenHomePageActivity.this, R.color.color_232021));
                    mUserProfileBean.setIsFav(false);
                } else {
                    ToastUtil.showToast(UserMenHomePageActivity.this, "Favorited");
                    tv_menuserhome_follow.setBackground(ContextCompat.getDrawable(UserMenHomePageActivity.this, R.drawable.shape_btn_9e9ea4_corners_50));
                    tv_menuserhome_follow.setText("collected");
                    tv_menuserhome_follow.setTextColor(ContextCompat.getColor(UserMenHomePageActivity.this, R.color.color_cfcfd2));
                    mUserProfileBean.setIsFav(true);
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                ToastUtil.showToast(UserMenHomePageActivity.this, resultMessage);
            }
        });
    }

    @Override
    public void onRefresh() {
        requestData();
    }

    private void showProgressBar() {
        if (!srl_menuserhome.isRefreshing()) {
            srl_menuserhome.setRefreshing(true);
        }
    }

    private void hideProgressBar() {
        if (srl_menuserhome.isRefreshing()) {
            srl_menuserhome.setRefreshing(false);
        }
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
//                ToastUtil.showToast(UserMenHomePageActivity.this, message);
//                mRightMenuPopup.dismiss();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(UserMenHomePageActivity.this, error.getErrMessage());
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
//                PayTask alipay = new PayTask(UserMenHomePageActivity.this);
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

    /**
     * 支付成功 刷新数据
     */
    private void paySuccess() {
        requestData();
        //刷新自己的信息
//        UserCenter.instance().refreshMyUserinfo(this);
    }

    private void payfailure(String errorStr) {
        ToastUtil.showToast(UserMenHomePageActivity.this, errorStr);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        EventBus.getDefault().unregister(this);
    }

}

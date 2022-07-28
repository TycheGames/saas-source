package com.bigshark.android.activities.home;

import android.animation.ValueAnimator;
import android.annotation.SuppressLint;
import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.support.v4.view.ViewPager;
import android.text.TextUtils;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.WindowManager;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.adapters.home.UserPhotoDetailsAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.events.TargetJumpEvent;
import com.bigshark.android.http.model.pay.PayAlipayModel;
import com.bigshark.android.http.model.pay.PayBalanceModel;
import com.bigshark.android.http.model.pay.PayResult;
import com.bigshark.android.http.model.pay.PayWeChatModel;
import com.bigshark.android.http.model.pay.PaymentConfirmationModel;
import com.bigshark.android.http.model.user.PicsModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.bigshark.android.widget.PhotoViewPager;
import com.bigshark.android.widget.popupwindow.ConfirmPaymentPopup;
import com.github.chrisbanes.photoview.PhotoView;
import com.gyf.immersionbar.ImmersionBar;

import org.xutils.common.util.DensityUtil;
import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.List;
import java.util.Map;

import butterknife.BindView;
import butterknife.ButterKnife;
import de.greenrobot.event.EventBus;

//import com.dinuscxj.progressbar.CircleProgressBar;

//import com.bigshark.android.activities.usercenter.UserCenter;
//import com.tencent.mm.opensdk.modelpay.PayReq;
//import com.tencent.mm.opensdk.openapi.IWXAPI;
//import com.tencent.mm.opensdk.openapi.WXAPIFactory;

/**
 * 查看用户图片
 */
public class ViewUserPhotoActivity extends DisplayBaseActivity {

    private static final String EXTRA_LIST = "extra_list";
    private static final String EXTRA_POS = "extra_pos";
    @BindView(R.id.iv_titlebar_left_back)
    ImageView mIv_titlebar_left_back;
    @BindView(R.id.tv_page)
    TextView tv_page;
    @BindView(R.id.view_pager)
    PhotoViewPager view_pager;
    private int mCurrentPosition;
    private List<PicsModel> mPhotoList;
    private UserPhotoDetailsAdapter mUserPhotoDetailsAdapter;
    //    private CircleProgressBar custom_progress;
    private RelativeLayout rl_set_shadow, rl_burn_after_reading, rl_big_photo_bg;
    private LinearLayout ll_has_burned, ll_send_red_envelope, ll_to_certification_prompt, ll_red_envelope;
    private PhotoView photoview;

    private ConfirmPaymentPopup mConfirmPaymentPopup;

    private PayAlipayModel mPayAlipayModel;
    private PayWeChatModel mPayWeChatModel;

    private boolean isBurned = false;
    private PicsModel currentpicsModel;

    private View rootview;

    public static void openIntent(Activity activity, String list, int pos) {
        Intent intent = new Intent(activity, ViewUserPhotoActivity.class);
        intent.putExtra(EXTRA_LIST, list);
        intent.putExtra(EXTRA_POS, pos);
        activity.startActivity(intent);
    }

    @Override
    protected int getLayoutId() {
        return R.layout.activity_view_user_photo;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        getWindow().addFlags(WindowManager.LayoutParams.FLAG_SECURE);
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_view_user_photo, null);
        if (!EventBus.getDefault().isRegistered(this)) {
            EventBus.getDefault().register(this);
        }
        if (getIntent() != null) {
            String listStr = getIntent().getStringExtra(EXTRA_LIST);
            mPhotoList = JSON.parseArray(listStr, PicsModel.class);
            mCurrentPosition = getIntent().getIntExtra(EXTRA_POS, 0);
        }
        initPopup();
    }

    private void initPopup() {
        //付费弹框
        mConfirmPaymentPopup = new ConfirmPaymentPopup(this, String.valueOf(MmkvGroup.global().getPriceRedpackPhoto()));
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

    Runnable mLongPressRunnable;
    Runnable burnedRunnable;
    int mLastMotionX, mLastMotionY;
    boolean isLongPress;
    boolean isMoved;

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        mIv_titlebar_left_back.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finish();
            }
        });

        mLongPressRunnable = new Runnable() {
            @Override
            public void run() {
                photoHandler.sendMessage(photoHandler.obtainMessage(66));
            }
        };

        burnedRunnable = new Runnable() {
            @Override
            public void run() {
                photoHandler.sendMessage(photoHandler.obtainMessage(67));
            }
        };


    }

    private Handler photoHandler = new Handler(new Handler.Callback() {

        @Override
        public boolean handleMessage(Message msg) {
            // TODO Auto-generated method stub
            switch (msg.what) {
                case 66:
                    //                    new AlertDialog.Builder(ViewUserPhotoActivity.this).setMessage("Test")
                    //                            .setPositiveButton("OK", null).show();
                    startTiming(currentpicsModel);
                    break;
                case 67:
                    endTiming(currentpicsModel);
                    break;
                default:
                    break;
            }
            return false;
        }
    });

    @Override
    public void setupDatas() {
        mUserPhotoDetailsAdapter = new UserPhotoDetailsAdapter(this, mPhotoList);
        view_pager.setAdapter(mUserPhotoDetailsAdapter);
        view_pager.setCurrentItem(mCurrentPosition, false);
        tv_page.setText(mCurrentPosition + 1 + "/" + mPhotoList.size());
        view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {

            @Override
            public void onPageScrolled(int i, float v, int i1) {

            }

            @Override
            public void onPageSelected(int i) {
                mCurrentPosition = i;
                tv_page.setText(mCurrentPosition + 1 + "/" + mPhotoList.size());
                currentpicsModel = mPhotoList.get(mCurrentPosition);

            }

            @Override
            public void onPageScrollStateChanged(int i) {

            }
        });
    }

    @Override
    public boolean dispatchTouchEvent(MotionEvent event) {
        currentpicsModel = mPhotoList.get(mCurrentPosition);
        View currentView = mUserPhotoDetailsAdapter.getPrimaryItem();
        photoview = currentView.findViewById(R.id.photoview);
        rl_set_shadow = currentView.findViewById(R.id.rl_set_shadow);
        rl_big_photo_bg = currentView.findViewById(R.id.rl_big_photo_bg);
        ll_has_burned = currentView.findViewById(R.id.ll_has_burned);
        ll_to_certification_prompt = currentView.findViewById(R.id.ll_to_certification_prompt);
        rl_burn_after_reading = currentView.findViewById(R.id.rl_burn_after_reading);
        ll_send_red_envelope = currentView.findViewById(R.id.ll_send_red_envelope);
        ll_red_envelope = currentView.findViewById(R.id.ll_red_envelope);
        ll_red_envelope.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                //弹出支付弹框
                if (mConfirmPaymentPopup != null) {
//                    new XPopup.Builder(ViewUserPhotoActivity.this).asCustom(mConfirmPaymentPopup).show();
                    mConfirmPaymentPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
            }
        });

//        custom_progress = currentView.findViewById(R.id.custom_progress);
//        custom_progress.setProgressFormatter(new CircleProgressBar.ProgressFormatter() {
//
//            @Override
//            public CharSequence format(int progress, int max) {
//                if (progress == max) {
//                    custom_progress.clearAnimation();
//                    return "";
//                } else {
//                    return currentpicsModel.getCan_read_time() - (progress / (max / currentpicsModel.getCan_read_time())) + "s";
//                }
//            }
//        });
        // TODO Auto-generated method stub
        int x = (int) event.getX(0);
        int y = (int) event.getY(0);
        switch (event.getAction()) {
            case MotionEvent.ACTION_DOWN:
                isLongPress = false;
                isMoved = false;
                mLastMotionX = x;
                mLastMotionY = y;
                if (currentpicsModel.isIs_red_pack()) {
                    if (currentpicsModel.isIs_pay()) {
                        if (currentpicsModel.isIs_burn_after_reading() && !currentpicsModel.isIs_burn()) {
                            photoHandler.postDelayed(mLongPressRunnable, 100);
                        }
                    }
                } else {
                    if (currentpicsModel.isIs_burn_after_reading() && !currentpicsModel.isIs_burn()) {
                        photoHandler.postDelayed(mLongPressRunnable, 100);
                    }
                }
                break;
            case MotionEvent.ACTION_MOVE:
                if (isMoved)
                    break;
                if (Math.abs(mLastMotionX - x) > 10
                        || Math.abs(mLastMotionY - y) > 10) {
                    isMoved = true;
                    photoHandler.removeCallbacks(mLongPressRunnable);
                }
                break;
            case MotionEvent.ACTION_UP:
                photoHandler.removeCallbacks(mLongPressRunnable);
                photoHandler.removeCallbacks(burnedRunnable);
                break;
            default:
                break;
        }
        return super.dispatchTouchEvent(event);
    }

    private void startTiming(PicsModel picsModel) {
        isBurned = true;
        rl_set_shadow.setVisibility(View.GONE);
        x.image().bind(
                photoview,
                picsModel.getPic_url(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setRadius(DensityUtil.dip2px(10))
                        .build()
        );
        rl_big_photo_bg.setVisibility(View.GONE);
//        custom_progress.setVisibility(View.VISIBLE);
        ValueAnimator animator = ValueAnimator.ofInt(0, 100);
        animator.addUpdateListener(new ValueAnimator.AnimatorUpdateListener() {
            @Override
            public void onAnimationUpdate(ValueAnimator animation) {
                int progress = (int) animation.getAnimatedValue();
//                custom_progress.setProgress(progress);
            }
        });
        animator.setRepeatCount(ValueAnimator.INFINITE);
        animator.setDuration(picsModel.getCan_read_time() * 1000);
        animator.start();
        //        myHandler.postDelayed(runnable, picsModel.getCan_read_time() * 1000);
        photoHandler.postDelayed(burnedRunnable, picsModel.getCan_read_time() * 1000);
    }

    private void endTiming(PicsModel picsModel) {
        if (!picsModel.isIs_burn_after_reading()) {
            return;
        }
        if (picsModel.isIs_burn()) {
            return;
        }
        rl_set_shadow.setVisibility(View.VISIBLE);
        rl_big_photo_bg.setVisibility(View.VISIBLE);
        x.image().bind(
                photoview,
                picsModel.getPic_url(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .setRadius(DensityUtil.dip2px(10))
                        .build()
        );
//        custom_progress.setVisibility(View.GONE);
        ll_has_burned.setVisibility(View.VISIBLE);
        rl_burn_after_reading.setVisibility(View.GONE);
        ll_send_red_envelope.setVisibility(View.GONE);
//        if (1 == UserCenter.instance().getUserGender()) {
//            if (MmkvGroup.loginInfo().getVipState() == 1) {
//                ll_to_certification_prompt.setVisibility(View.GONE);
//            } else {
//                ll_to_certification_prompt.setVisibility(View.VISIBLE);
//            }
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            if (MmkvGroup.loginInfo().getIdentifyState() == 1) {
//                ll_to_certification_prompt.setVisibility(View.GONE);
//            } else {
//                ll_to_certification_prompt.setVisibility(View.VISIBLE);
//            }
//        }
        //请求图片销毁
        destroyImage(picsModel);
    }

    private void destroyImage(PicsModel picsModel) {
//        BurnPicRequestBean requestBean = new BurnPicRequestBean();
//        requestBean.setPic_id(picsModel.getPic_id());
//        HttpApi.app().burnPic(this, requestBean, new HttpCallback<PicsModel>() {
//            @Override
//            public void onSuccess(int code, String message, PicsModel data) {
//                picsModel.setIs_burn(data.isIs_burn());
//                photoHandler.removeCallbacks(burnedRunnable);
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<PicsModel>((display())) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_BURNPIC_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("pic_id", picsModel.getPic_id());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(PicsModel data, int resultCode, String resultMessage) {
                picsModel.setIs_burn(data.isIs_burn());
                photoHandler.removeCallbacks(burnedRunnable);
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
//        PayAlipayRequestBean requestBean = buildRequestBean();
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
                requestParams.addBodyParameter("scene", 4);
                requestParams.addBodyParameter("amount", String.valueOf(MmkvGroup.global().getPriceRedpackPhoto()));
                requestParams.addBodyParameter("goods", "{\"pic_id\":" + mPhotoList.get(mCurrentPosition).getPic_id() + "}");
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

//    private PayAlipayRequestBean buildRequestBean() {
//        PayAlipayRequestBean requestBean = new PayAlipayRequestBean();
//        requestBean.setScene(4);
//        requestBean.setAmount(String.valueOf(MmkvGroup.global().getPriceRedpackPhoto()));
//        requestBean.setGoods("{\"pic_id\":" + mPhotoList.get(mCurrentPosition).getPic_id() + "}");
//        return requestBean;
//    }

    /**
     * 支付宝支付
     */
    private void requestAliPay() {
//        PayAlipayRequestBean requestBean = buildRequestBean();
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
                requestParams.addBodyParameter("scene", 4);
                requestParams.addBodyParameter("amount", String.valueOf(MmkvGroup.global().getPriceRedpackPhoto()));
                requestParams.addBodyParameter("goods", "{\"pic_id\":" + mPhotoList.get(mCurrentPosition).getPic_id() + "}");
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
//                PayTask alipay = new PayTask(ViewUserPhotoActivity.this);
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
//        PayAlipayRequestBean requestBean = buildRequestBean();
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
                requestParams.addBodyParameter("scene", 4);
                requestParams.addBodyParameter("amount", String.valueOf(MmkvGroup.global().getPriceRedpackPhoto()));
                requestParams.addBodyParameter("goods", "{\"pic_id\":" + mPhotoList.get(mCurrentPosition).getPic_id() + "}");
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
        ToastUtil.showToast(ViewUserPhotoActivity.this, "支付成功");
        //刷新自己的信息
//        UserCenter.instance().refreshMyUserinfo(this);
        //刷新照片状态
        mPhotoList.get(mCurrentPosition).setIs_pay(true);
        mConfirmPaymentPopup.dismiss();
        if (currentpicsModel.isIs_burn_after_reading()) {
            ll_send_red_envelope.setVisibility(View.GONE);
        } else {
            ll_send_red_envelope.setVisibility(View.GONE);
            rl_set_shadow.setVisibility(View.GONE);
            rl_big_photo_bg.setVisibility(View.GONE);
            x.image().bind(
                    photoview,
                    currentpicsModel.getPic_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setRadius(DensityUtil.dip2px(10))
                            .build()
            );
        }
    }

    private void payfailure(String errorStr) {
        ToastUtil.showToast(ViewUserPhotoActivity.this, errorStr);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        EventBus.getDefault().unregister(this);
    }

}

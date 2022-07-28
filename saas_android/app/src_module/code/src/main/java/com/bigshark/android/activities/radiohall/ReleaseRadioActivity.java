package com.bigshark.android.activities.radiohall;

import android.annotation.SuppressLint;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Message;
import android.support.v7.widget.RecyclerView;
import android.text.TextUtils;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.CheckBox;
import android.widget.EditText;
import android.widget.ImageView;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.alibaba.fastjson.JSONArray;
import com.bigshark.android.R;
import com.bigshark.android.activities.home.BrowserActivity;
import com.bigshark.android.adapters.radiohall.ImagePickerAdapter;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.events.TargetJumpEvent;
import com.bigshark.android.http.model.app.ProvinceModel;
import com.bigshark.android.http.model.bean.PaytipsPopupModel;
import com.bigshark.android.http.model.mine.PublishBroadcastRequestModel;
import com.bigshark.android.http.model.pay.PayAlipayModel;
import com.bigshark.android.http.model.pay.PayBalanceModel;
import com.bigshark.android.http.model.pay.PayResult;
import com.bigshark.android.http.model.pay.PayWeChatModel;
import com.bigshark.android.http.model.pay.PaymentConfirmationModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.listener.OnSelectCityListener;
import com.bigshark.android.listener.OnTwoButtonClickListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.StringUtil;
import com.bigshark.android.utils.ToastUtil;
import com.bigshark.android.widget.popupwindow.ChoiceCityPopup;
import com.bigshark.android.widget.popupwindow.ConfirmPaymentPopup;
import com.bigshark.android.widget.popupwindow.MultipleChoiceListPopup;
import com.bigshark.android.widget.popupwindow.OneChoiceListPopup;
import com.bigshark.android.widget.popupwindow.PaytipsPopup;
import com.bigshark.android.widget.popupwindow.SelectDatePopup;
import com.gyf.immersionbar.ImmersionBar;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import butterknife.ButterKnife;
import butterknife.OnClick;
import de.greenrobot.event.EventBus;

//import com.bigshark.android.activities.usercenter.UserCenter;
//import com.shuimiao.sangeng.imagepicker.ImagePicker;
//import com.shuimiao.sangeng.imagepicker.bean.ImageItem;
//import com.shuimiao.sangeng.imagepicker.ui.ImageGridActivity;
//import com.shuimiao.sangeng.imagepicker.ui.ImagePreviewDelActivity;

//import com.alipay.sdk.app.PayTask;
//import com.tencent.mm.opensdk.modelpay.PayReq;
//import com.tencent.mm.opensdk.openapi.IWXAPI;
//import com.tencent.mm.opensdk.openapi.WXAPIFactory;

/**
 * 发布广播
 */
public class ReleaseRadioActivity extends DisplayBaseActivity implements ImagePickerAdapter.OnRecyclerViewItemClickListener {

    public static final int IMAGE_ITEM_ADD = -1;
    public static final int REQUEST_CODE_SELECT = 100;
    public static final int REQUEST_CODE_PREVIEW = 101;

    private ImageView mIv_release_radio_back;
    private TextView mTv_release_radio, tv_release_radio_dating_theme, tv_release_radio_dating_expectations,
            tv_release_radio_time, tv_release_radio_city, tv_release_radio_date, tv_release_radio_btn, tv_release_hint;
    private RecyclerView recyclerView;
    private CheckBox cb_hidden, cb_comments;
    private EditText et_supplement;

    private OneChoiceListPopup mOneChoiceListPopup, timePopup;
    private MultipleChoiceListPopup mMultipleChoiceListPopup;
    private ChoiceCityPopup mChoiceCityPopup;
    private SelectDatePopup mSelectDatePopup;
    private PaytipsPopup mHintPopup;
    private ConfirmPaymentPopup mConfirmPaymentPopup;
    private List<ProvinceModel> cityJson = null;
    //    private ImagePickerAdapter      adapter;
//    private ArrayList<ImageItem>    selImageList; //当前选择的所有图片
    private List<String> selImageURlList = new ArrayList<>();

    private View rootview;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_release_radio;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        if (!EventBus.getDefault().isRegistered(this)) {
            EventBus.getDefault().register(this);
        }
        getCityJson();
        rootview = LayoutInflater.from(this).inflate(R.layout.activity_release_radio, null);
        mIv_release_radio_back = findViewById(R.id.iv_release_radio_back);
        mTv_release_radio = findViewById(R.id.tv_release_radio);
        tv_release_radio_dating_theme = findViewById(R.id.tv_release_radio_dating_theme);
        tv_release_radio_dating_expectations = findViewById(R.id.tv_release_radio_dating_expectations);
        tv_release_radio_time = findViewById(R.id.tv_release_radio_time);
        tv_release_radio_city = findViewById(R.id.tv_release_radio_city);
        tv_release_radio_date = findViewById(R.id.tv_release_radio_date);
        recyclerView = findViewById(R.id.recycler_view_release_radio);
        tv_release_radio_btn = findViewById(R.id.tv_release_radio_btn);
        tv_release_hint = findViewById(R.id.tv_release_hint);
        cb_hidden = findViewById(R.id.cb_hidden);
        cb_comments = findViewById(R.id.cb_comments);
        et_supplement = findViewById(R.id.et_supplement);
        initPop();
        initRecyclerView();
    }

    private void initRecyclerView() {
//        selImageList = new ArrayList<>();
//        adapter = new ImagePickerAdapter(this, selImageList, MyApplication.maxImgCount);
//        adapter.setOnItemClickListener(this);
//        recyclerView.setLayoutManager(new GridLayoutManager(this, 3));
//        recyclerView.setHasFixedSize(true);
//        recyclerView.setAdapter(adapter);
    }

    private void initPop() {
        //约会目的弹框
//        AppTextModel appTextBean = UserCenter.instance().getAppText();
//        mOneChoiceListPopup = new OneChoiceListPopup(this, "约会目的", appTextBean.getDateProgram());
//        timePopup = new OneChoiceListPopup(this, "约会时间", UserCenter.instance().getAppointmentTime());
//        mOneChoiceListPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
//            @Override
//            public void OnConfirmClick(String str) {
//                tv_release_radio_dating_theme.setText(str);
//                mOneChoiceListPopup.dismiss();
//            }
//        });

        //时间弹框
//        timePopup.setOnConfirmClickListener(new OnConfirmClickListener() {
//
//            @Override
//            public void OnConfirmClick(String str) {
//                tv_release_radio_time.setText(str);
//                timePopup.dismiss();
//            }
//        });

        //约会期望 弹框
//        mMultipleChoiceListPopup = new MultipleChoiceListPopup(this, "约会期望", UserCenter.instance().getDatingExpectations());
//        mMultipleChoiceListPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
//            @Override
//            public void OnConfirmClick(String str) {
//                tv_release_radio_dating_expectations.setText(str);
//                mMultipleChoiceListPopup.dismiss();
//            }
//        });

        //提示需要去认证或付费弹框
        PaytipsPopupModel paytipsPopupModel = new PaytipsPopupModel();
        paytipsPopupModel.setPay_number("（50元）");
        paytipsPopupModel.setPay_content("Paid Broadcast");
//        if (1 == UserCenter.instance().getUserGender()) {
//            paytipsPopupModel.setVip_content("成为会员，免费发布");
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            paytipsPopupModel.setVip_content("认证资料，免费发布");
//        }
        mHintPopup = new PaytipsPopup(this, paytipsPopupModel);
        mHintPopup.setOnTwoButtonClickListener(new OnTwoButtonClickListener() {
            @Override
            public void OnOneButtonClick() {
                //付费
                mHintPopup.dismiss();
                //弹出 付费弹框
                if (mConfirmPaymentPopup != null) {
//                    new XPopup.Builder(ReleaseRadioActivity.this).asCustom(mConfirmPaymentPopup).show();
                    mConfirmPaymentPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
            }

            @Override
            public void OnTwoButtonClick() {
                //去认证或者去购买vip
                mHintPopup.dismiss();
                BrowserActivity.goIntent(ReleaseRadioActivity.this, MmkvGroup.global().getVipLink());
            }
        });

        //付费弹框 则
        mConfirmPaymentPopup = new ConfirmPaymentPopup(this, MmkvGroup.global().getPriceBroadcast() + "");
        mConfirmPaymentPopup.setOnConfirmClickListener(new OnConfirmClickListener() {
            @Override
            public void OnConfirmClick(String str) {
                //确认支付
                if (StringUtil.isBlank(str)) {
                    ToastUtil.showToast(ReleaseRadioActivity.this, "请选择支付方式");
                    return;
                }
                switch (str) {
                    //余额支付
                    case ConfirmPaymentPopup.PAYTYPE_BALANCE:
                        requestPayBalance(MmkvGroup.global().getPriceBroadcast() + "");
                        break;
                    //支付宝支付
                    case ConfirmPaymentPopup.PAYTYPE_ZHIFUBAO:
                        requestAliPay(MmkvGroup.global().getPriceBroadcast() + "");
                        break;
                    //微信支付
                    case ConfirmPaymentPopup.PAYTYPE_WEIXIN:
                        requestWXPay(MmkvGroup.global().getPriceBroadcast() + "");
                        break;
                    default:
                        break;
                }
                mConfirmPaymentPopup.dismiss();
            }
        });

        //选择日期
        mSelectDatePopup = new SelectDatePopup(this);
        mSelectDatePopup.setOnSelectCityListener(new OnSelectCityListener() {
            @Override
            public void onConfirmClick(String name) {
                mSelectDatePopup.dismiss();
                String date = name.replaceAll("年", "-").replaceAll("月", "-");
                tv_release_radio_date.setText(date.substring(0, date.length() - 1));
            }

            @Override
            public void onUnlimitedClick(String str) {
                mSelectDatePopup.dismiss();
                tv_release_radio_date.setText(str);
            }
        });
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {
//        if (1 == UserCenter.instance().getUserGender()) {
//            tv_release_hint.setText("男士已是会员免费，否则需要支付50.00元");
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            tv_release_hint.setText("已认证女士免费，否则需要支付50.00元");
//        }
    }

    /**
     * 校验是否是vip或者是否已认证
     *
     * @return
     */
    private void checkQualification() {
//        if (1 == UserCenter.instance().getUserGender()) {
//            if (1 == MmkvGroup.loginInfo().getVipState()) {
//                requestReleaseRadio();
//            } else {
//                showHintDialog();
//            }
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            if (1 == MmkvGroup.loginInfo().getIdentifyState()) {
//                requestReleaseRadio();
//            } else {
//                showHintDialog();
//            }
//        }
    }

    /**
     * 提示去充值或认证的diaog （50元）
     */
    private void showHintDialog() {
//        new XPopup.Builder(this).asCustom(mHintPopup).show();
        mHintPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
    }

    /**
     * 发布广播
     */
    private void requestReleaseRadio() {
//        PublishBroadcastRequestBean requestBean = new PublishBroadcastRequestBean();
//        requestBean.setTheme(tv_release_radio_dating_theme.getText().toString().trim());
//        requestBean.setHope(tv_release_radio_dating_expectations.getText().toString().trim());
//        requestBean.setCity(tv_release_radio_city.getText().toString().trim());
//        requestBean.setDate(tv_release_radio_date.getText().toString().trim());
//        requestBean.setTime_slot(tv_release_radio_time.getText().toString().trim());
//        requestBean.setSex_status(cb_hidden.isChecked() ? "2" : "1");
//        requestBean.setComment_status(cb_comments.isChecked() ? "2" : "1");
//        if (selImageURlList != null && selImageURlList.size() > 0) {
//            requestBean.setImage(JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
//        }
//        if (!StringUtil.isBlankEdit(et_supplement)) {
//            requestBean.setSupplement(et_supplement.getText().toString().trim());
//        }
//        HttpApi.app().publishBroadcast(this, requestBean, new HttpCallback<String>() {
//
//            @Override
//            public void onSuccess(int code, String message, String data) {
//                ToastUtil.showToast(ReleaseRadioActivity.this, "Broadcast released successfully");
//                finish();
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(ReleaseRadioActivity.this, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<String>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_PUBLISHBROADCAST_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("theme", tv_release_radio_dating_theme.getText().toString().trim());
                requestParams.addBodyParameter("hope", tv_release_radio_dating_expectations.getText().toString().trim());
                requestParams.addBodyParameter("city", tv_release_radio_city.getText().toString().trim());
                requestParams.addBodyParameter("date", tv_release_radio_date.getText().toString().trim());
                requestParams.addBodyParameter("time_slot", cb_hidden.isChecked() ? "2" : "1");
                requestParams.addBodyParameter("sex_status", tv_release_radio_dating_theme.getText().toString().trim());
                requestParams.addBodyParameter("comment_status", cb_comments.isChecked() ? "2" : "1");
                if (selImageURlList != null && selImageURlList.size() > 0) {
                    requestParams.addBodyParameter("image", JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
                }
                if (!StringUtil.isBlankEdit(et_supplement)) {
                    requestParams.addBodyParameter("supplement", et_supplement.getText().toString().trim());
                }
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(String data, int resultCode, String resultMessage) {
                showToast("Broadcast released successfully");
                finish();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    /**
     * 检查填写情况
     *
     * @return
     */
    private boolean checkInput() {
        if (StringUtil.isBlankEdit(tv_release_radio_dating_theme)) {
            showToast("Please select a dating theme");
            return false;
        } else if (StringUtil.isBlankEdit(tv_release_radio_dating_expectations)) {
            showToast("Please select a dating expectation");
            return false;
        } else if (StringUtil.isBlankEdit(tv_release_radio_city)) {
            showToast("Please select a dating city");
            return false;
        } else if (StringUtil.isBlankEdit(tv_release_radio_date)) {
            showToast("Please select an appointment date");
            return false;
        } else if (StringUtil.isBlankEdit(tv_release_radio_time)) {
            showToast("Please select an appointment");
            return false;
        } else if (tv_release_radio_dating_expectations.getText().toString().trim().equals("Multiple choice")) {
            showToast("Please select a dating expectation");
            return false;
        } else if (tv_release_radio_date.getText().toString().trim().equals("Select date")) {
            showToast("Please select an appointment date");
            return false;
        } else if (tv_release_radio_time.getText().toString().trim().equals("selection period")) {
            showToast("Please select an appointment");
            return false;
        }
        return true;
    }

    private void getCityJson() {
//        HttpApi.app().getRegion(this, new BaseRequestBean(), new HttpCallback<List<ProvinceModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<ProvinceModel> data) {
//                if (data != null) {
//                    cityJson = data;
//                    mChoiceCityPopup = new ChoiceCityPopup(ReleaseRadioActivity.this, cityJson);
//                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
//                        @Override
//                        public void onConfirmClick(String name) {
//                            tv_release_radio_city.setText(name);
//                            mChoiceCityPopup.dismiss();
//                        }
//
//                        @Override
//                        public void onUnlimitedClick(String str) {
//                            tv_release_radio_city.setText(str);
//                            mChoiceCityPopup.dismiss();
//                        }
//                    });
//
//
//                } else {
//                    ToastUtil.showToast(ReleaseRadioActivity.this, "Failed to get list of cities");
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(ReleaseRadioActivity.this, error.getErrMessage());
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
                    cityJson = data;
                    mChoiceCityPopup = new ChoiceCityPopup(ReleaseRadioActivity.this, cityJson);
                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
                        @Override
                        public void onConfirmClick(String name) {
                            tv_release_radio_city.setText(name);
                            mChoiceCityPopup.dismiss();
                        }

                        @Override
                        public void onUnlimitedClick(String str) {
                            tv_release_radio_city.setText(str);
                            mChoiceCityPopup.dismiss();
                        }
                    });
                } else {
                    ToastUtil.showToast(ReleaseRadioActivity.this, "Failed to get list of cities");
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showToast(resultMessage);
            }
        });
    }

    @Override
    public void onItemClick(View view, int position) {
        switch (position) {
            case IMAGE_ITEM_ADD:
                //打开选择,本次允许选择的数量
//                ImagePicker.getInstance().setSelectLimit(MyApplication.maxImgCount - selImageList.size());
//                Intent intent1 = new Intent(ReleaseRadioActivity.this, ImageGridActivity.class);
                /* 如果需要进入选择的时候显示已经选中的图片，
                 * 详情请查看ImagePickerActivity
                 * */
                //                                intent1.putExtra(ImageGridActivity.EXTRAS_IMAGES,images);
//                startActivityForResult(intent1, REQUEST_CODE_SELECT);
                break;
            default:
                //打开预览
//                Intent intentPreview = new Intent(this, ImagePreviewDelActivity.class);
//                intentPreview.putExtra(ImagePicker.EXTRA_IMAGE_ITEMS, (ArrayList<ImageItem>) adapter.getImages());
//                intentPreview.putExtra(ImagePicker.EXTRA_SELECTED_IMAGE_POSITION, position);
//                intentPreview.putExtra(ImagePicker.EXTRA_FROM_ITEMS, true);
//                intentPreview.putExtra(ImagePreviewDelActivity.EXTRA_SET_PHOTO, false);
//                startActivityForResult(intentPreview, REQUEST_CODE_PREVIEW);
                break;
        }
    }

//    ArrayList<ImageItem> images = null;

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        super.onActivityResult(requestCode, resultCode, data);
//        if (resultCode == ImagePicker.RESULT_CODE_ITEMS) {
//            //添加图片返回
//            if (data != null && requestCode == REQUEST_CODE_SELECT) {
//                images = (ArrayList<ImageItem>) data.getSerializableExtra(ImagePicker.EXTRA_RESULT_ITEMS);
//                if (images != null) {
//                    uploadImage(images);
//                }
//            }
//        } else if (resultCode == ImagePicker.RESULT_CODE_BACK) {
//            //预览图片返回
//            if (data != null && requestCode == REQUEST_CODE_PREVIEW) {
//                images = (ArrayList<ImageItem>) data.getSerializableExtra(ImagePicker.EXTRA_IMAGE_ITEMS);
//                if (images != null) {
//                    selImageList.clear();
//                    selImageList.addAll(images);
//                    adapter.setImages(selImageList);
//                }
//            }
//        }
    }

//    private void uploadImage(ArrayList<ImageItem> imageList) {
//        List<FileBean> fileBeanList = new ArrayList<>();
//        for (int i = 0; i < imageList.size(); i++) {
//            FileBean fileBean = new FileBean();
//            fileBean.setUpLoadKey("images[]");
//            fileBean.setFileSrc(imageList.get(i).path);
//            fileBeanList.add(fileBean);
//        }
//        HttpApi.app().uploadImageBatch(this, fileBeanList, new HttpCallback<ImageBatchModel>() {
//
//            @Override
//            public void onSuccess(int code, String message, ImageBatchModel data) {
//                selImageList.addAll(images);
//                adapter.setImages(selImageList);
//                selImageURlList.addAll(data.getUrl());
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
//    }

//    private PayAlipayRequestBean buildPayRequestBean(String money) {
//        PayAlipayRequestBean requestBean = new PayAlipayRequestBean();
//        requestBean.setScene(7);
//        requestBean.setAmount(money);
//        PublishBroadcastRequestBean parameBean = new PublishBroadcastRequestBean();
//        parameBean.setTheme(tv_release_radio_dating_theme.getText().toString().trim());
//        parameBean.setHope(tv_release_radio_dating_expectations.getText().toString().trim());
//        parameBean.setCity(tv_release_radio_city.getText().toString().trim());
//        parameBean.setDate(tv_release_radio_date.getText().toString().trim());
//        parameBean.setTime_slot(tv_release_radio_time.getText().toString().trim());
//        parameBean.setSex_status(cb_hidden.isChecked() ? "2" : "1");
//        parameBean.setComment_status(cb_comments.isChecked() ? "2" : "1");
//        if (selImageURlList != null && selImageURlList.size() > 0) {
//            parameBean.setImage(JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
//        }
//        if (!StringUtil.isBlankEdit(et_supplement)) {
//            parameBean.setSupplement(et_supplement.getText().toString().trim());
//        }
//        requestBean.setGoods(JSON.toJSONString(parameBean));
//        return requestBean;
//    }

    /**
     * 余额支付
     *
     * @param money
     */
    private void requestPayBalance(String money) {
//        PayAlipayRequestBean requestBean = buildPayRequestBean(money);
//        HttpApi.app().payBalance(this, requestBean, new HttpCallback<PayBalanceModel>() {
//            @Override
//            public void onSuccess(int code, String message, PayBalanceModel data) {
//                // 余额支付成功
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
                requestParams.addBodyParameter("scene", 7);
                requestParams.addBodyParameter("amount", money);
                PublishBroadcastRequestModel parameModel = new PublishBroadcastRequestModel();
                parameModel.setTheme(tv_release_radio_dating_theme.getText().toString().trim());
                parameModel.setHope(tv_release_radio_dating_expectations.getText().toString().trim());
                parameModel.setCity(tv_release_radio_city.getText().toString().trim());
                parameModel.setDate(tv_release_radio_date.getText().toString().trim());
                parameModel.setTime_slot(tv_release_radio_time.getText().toString().trim());
                parameModel.setSex_status(cb_hidden.isChecked() ? "2" : "1");
                parameModel.setComment_status(cb_comments.isChecked() ? "2" : "1");
                if (selImageURlList != null && selImageURlList.size() > 0) {
                    parameModel.setImage(JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
                }
                if (!StringUtil.isBlankEdit(et_supplement)) {
                    parameModel.setSupplement(et_supplement.getText().toString().trim());
                }
                requestParams.addBodyParameter("goods", JSON.toJSONString(parameModel));
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

    private PayAlipayModel mPayAlipayModel;

    /**
     * 支付宝支付
     */
    private void requestAliPay(String money) {
//        PayAlipayRequestBean requestBean = buildPayRequestBean(money);
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
                requestParams.addBodyParameter("scene", 7);
                requestParams.addBodyParameter("amount", money);
                PublishBroadcastRequestModel parameModel = new PublishBroadcastRequestModel();
                parameModel.setTheme(tv_release_radio_dating_theme.getText().toString().trim());
                parameModel.setHope(tv_release_radio_dating_expectations.getText().toString().trim());
                parameModel.setCity(tv_release_radio_city.getText().toString().trim());
                parameModel.setDate(tv_release_radio_date.getText().toString().trim());
                parameModel.setTime_slot(tv_release_radio_time.getText().toString().trim());
                parameModel.setSex_status(cb_hidden.isChecked() ? "2" : "1");
                parameModel.setComment_status(cb_comments.isChecked() ? "2" : "1");
                if (selImageURlList != null && selImageURlList.size() > 0) {
                    parameModel.setImage(JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
                }
                if (!StringUtil.isBlankEdit(et_supplement)) {
                    parameModel.setSupplement(et_supplement.getText().toString().trim());
                }
                requestParams.addBodyParameter("goods", JSON.toJSONString(parameModel));
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
//                PayTask alipay = new PayTask(ReleaseRadioActivity.this);
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
                        payfailure("Alipay payment failed");
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

    private PayWeChatModel mPayWeChatModel;

    /**
     * 微信支付
     */
    private void requestWXPay(String money) {
//        PayAlipayRequestBean requestBean = buildPayRequestBean(money);
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
                requestParams.addBodyParameter("scene", 7);
                requestParams.addBodyParameter("amount", money);
                PublishBroadcastRequestModel parameModel = new PublishBroadcastRequestModel();
                parameModel.setTheme(tv_release_radio_dating_theme.getText().toString().trim());
                parameModel.setHope(tv_release_radio_dating_expectations.getText().toString().trim());
                parameModel.setCity(tv_release_radio_city.getText().toString().trim());
                parameModel.setDate(tv_release_radio_date.getText().toString().trim());
                parameModel.setTime_slot(tv_release_radio_time.getText().toString().trim());
                parameModel.setSex_status(cb_hidden.isChecked() ? "2" : "1");
                parameModel.setComment_status(cb_comments.isChecked() ? "2" : "1");
                if (selImageURlList != null && selImageURlList.size() > 0) {
                    parameModel.setImage(JSONArray.parseArray(JSON.toJSONString(selImageURlList)));
                }
                if (!StringUtil.isBlankEdit(et_supplement)) {
                    parameModel.setSupplement(et_supplement.getText().toString().trim());
                }
                requestParams.addBodyParameter("goods", JSON.toJSONString(parameModel));
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
                //微信支付成功
                getWechatResult();
            } else {
                //微信支付失败
                payfailure("WeChat payment failed");
            }
        }
    }

    /**
     * 微信支付结果确认
     */
    private void getWechatResult() {
        if (mPayWeChatModel == null) {
            return;
        }
//        PaymentConfirmationRequestBean requestBean = new PaymentConfirmationRequestBean();
//        requestBean.setOrder_no(mPayWeChatModel.getOrder_no());
//        HttpApi.app().getWechatResult(this, requestBean, new HttpCallback<PaymentConfirmationModel>() {
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
     * 支付成功
     */
    private void paySuccess() {
        mConfirmPaymentPopup.dismiss();
        finish();
        //去发广播
//        requestReleaseRadio();
    }

    /**
     * 支付失败
     *
     * @param errorStr
     */
    private void payfailure(String errorStr) {
        mConfirmPaymentPopup.dismiss();
        ToastUtil.showToast(ReleaseRadioActivity.this, errorStr);
    }

    @Override
    protected void onDestroy() {
        super.onDestroy();
        if (EventBus.getDefault().isRegistered(this)) {
            EventBus.getDefault().unregister(this);
        }
    }

    @OnClick({R.id.iv_release_radio_back, R.id.tv_release_radio,
            R.id.tv_release_radio_dating_theme, R.id.tv_release_radio_dating_expectations,
            R.id.tv_release_radio_city, R.id.tv_release_radio_date,
            R.id.tv_release_radio_time, R.id.tv_release_radio_btn})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.iv_release_radio_back:
                finish();
                break;
            //发布
            case R.id.tv_release_radio:
            case R.id.tv_release_radio_btn:
                if (checkInput()) {
                    checkQualification();
                }
                break;
            //约会主题
            case R.id.tv_release_radio_dating_theme:
//                new XPopup.Builder(this).asCustom(mOneChoiceListPopup).show();
                mOneChoiceListPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            //约会期望
            case R.id.tv_release_radio_dating_expectations:
//                new XPopup.Builder(this).asCustom(mMultipleChoiceListPopup).show();
                mMultipleChoiceListPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            //约会时间
            case R.id.tv_release_radio_time:
//                new XPopup.Builder(this).asCustom(timePopup).show();
                timePopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                break;
            //约会城市
            case R.id.tv_release_radio_city:
                if (mChoiceCityPopup != null) {
//                    new XPopup.Builder(this).asCustom(mChoiceCityPopup).show();
                    mChoiceCityPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
                break;
            //日期
            case R.id.tv_release_radio_date:
                if (mSelectDatePopup != null) {
                    mSelectDatePopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
                break;
        }
    }
}

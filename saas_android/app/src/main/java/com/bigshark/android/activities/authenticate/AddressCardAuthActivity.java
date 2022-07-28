package com.bigshark.android.activities.authenticate;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.view.MotionEvent;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.TextView;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.AddressCardAuthConfigResponseModel;
import com.bigshark.android.http.model.authenticate.AddressCardAuthResponseModel;
import com.bigshark.android.http.model.authenticate.UploadAddressCardAuthResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.carema.CaptureStrategy;
import com.bigshark.android.utils.carema.MediaStoreCompat;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.authenticate.addresscard.aadhaar.AddressCardAuthAadhaarMustVh;
import com.bigshark.android.vh.authenticate.addresscard.aadhaar.AddressCardAuthAadhaarVh;
import com.bigshark.android.vh.authenticate.addresscard.choose.AddressCardAuthTypeChooseVh;
import com.bigshark.android.vh.authenticate.addresscard.driver.AddressCardAuthDriverFillVh;
import com.bigshark.android.vh.authenticate.addresscard.driver.AddressCardAuthDriverOcrVh;
import com.bigshark.android.vh.authenticate.addresscard.passport.AddressCardAuthPassportVh;
import com.bigshark.android.vh.authenticate.addresscard.votercard.AddressCardAuthVoterCardIdVh;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 地址证明认证：aadhaar认证 + address认证
 */
public class AddressCardAuthActivity extends DisplayBaseActivity {

    private NavigationStatusLinearLayout toolBar;

    private AddressCardAuthAadhaarMustVh aadhaarOcrMustVh;
    private AddressCardAuthTypeChooseVh authTypeChooseVh;
    private AddressCardAuthAadhaarVh aadhaarVh;
    private AddressCardAuthVoterCardIdVh voterIdVh;
    private AddressCardAuthPassportVh passportVh;
    private AddressCardAuthDriverOcrVh driverOcrVh;
    private AddressCardAuthDriverFillVh driverFillVh;

    private TextView nextTxt;

    private MediaStoreCompat mMediaStoreCompat;

    private AddressCardAuthConfigResponseModel addressCardAuthConfigResponseModel = null;
    private int selectedType = StringConstant.ADDRESS_CARD_AUTH_RESPONSE_UNKNOWN;// 当前地址的选择类型
    private UploadAddressCardAuthResponseModel aadhaarData;
    private String aadhaarFrontFilePath, aadhaarBackFilePath;// 图片的路径
    private UploadAddressCardAuthResponseModel voterIdData;
    private String voterIdFrontFilePath, voterIdBackFilePath;// 图片的路径
    private UploadAddressCardAuthResponseModel passportData;
    private String passportFrontFilePath, passportBackFilePath;// 图片的路径
    private UploadAddressCardAuthResponseModel driverOcrData;
    private String driverOcrFrontFilePath, driverOcrBackFilePath;// 图片的路径
    private String driverFillLicenseText;
    private String driverFillBirthday;


    @Override
    protected int getLayoutId() {
        return R.layout.activity_address_card_auth;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);

        mMediaStoreCompat = new MediaStoreCompat(this);
        mMediaStoreCompat.setCaptureStrategy(new CaptureStrategy(true, BuildConfig.FILE_PROVIDER));

        toolBar = findViewById(R.id.authenticate_address_title);

        View aadhaarRoot = findViewById(R.id.authenticate_address_aadhaar_root);
        aadhaarOcrMustVh = new AddressCardAuthAadhaarMustVh(this, mMediaStoreCompat, aadhaarRoot, new AddressCardAuthAadhaarMustVh.Callback() {
            @Override
            public void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath) {
                aadhaarData = data;
                aadhaarFrontFilePath = frontFilePath;
                aadhaarBackFilePath = backFilePath;
                virifyDatasToSubmitDataView();
            }
        });
        aadhaarOcrMustVh.getRoot().setVisibility(View.GONE);

        View chooseView = findViewById(R.id.authenticate_address_address_select_root);
        authTypeChooseVh = new AddressCardAuthTypeChooseVh(this, chooseView, new AddressCardAuthTypeChooseVh.Callback() {
            @Override
            public void onClick(int type) {
                cleanupHistoryDataAndDisplay(type);
            }
        });
        authTypeChooseVh.getRoot().setVisibility(View.GONE);

        View aadhaarRoot2 = findViewById(R.id.authenticate_address_aadhaar2_root);
        aadhaarVh = new AddressCardAuthAadhaarVh(this, mMediaStoreCompat, aadhaarRoot2, new AddressCardAuthAadhaarVh.Callback() {
            @Override
            public void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath) {
                aadhaarData = data;
                aadhaarFrontFilePath = frontFilePath;
                aadhaarBackFilePath = backFilePath;
                virifyDatasToSubmitDataView();
            }
        });
        aadhaarVh.getRoot().setVisibility(View.GONE);

        View voterCardView = findViewById(R.id.authenticate_address_voterid_root);
        voterIdVh = new AddressCardAuthVoterCardIdVh(this, mMediaStoreCompat, voterCardView, new AddressCardAuthVoterCardIdVh.Callback() {
            @Override
            public void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath) {
                voterIdData = data;
                voterIdFrontFilePath = frontFilePath;
                voterIdBackFilePath = backFilePath;
                virifyDatasToSubmitDataView();
            }
        });
        voterIdVh.getRoot().setVisibility(View.GONE);

        View passportView = findViewById(R.id.authenticate_address_passport_root);
        passportVh = new AddressCardAuthPassportVh(this, mMediaStoreCompat, passportView, new AddressCardAuthPassportVh.Callback() {
            @Override
            public void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath) {
                passportData = data;
                passportFrontFilePath = frontFilePath;
                passportBackFilePath = backFilePath;
                virifyDatasToSubmitDataView();
            }
        });
        passportVh.getRoot().setVisibility(View.GONE);

        View driverOcrView = findViewById(R.id.authenticate_address_driver_ocr_root);
        driverOcrVh = new AddressCardAuthDriverOcrVh(this, mMediaStoreCompat, driverOcrView, new AddressCardAuthDriverOcrVh.Callback() {
            @Override
            public void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath) {
                driverOcrData = data;
                driverOcrFrontFilePath = frontFilePath;
                driverOcrBackFilePath = backFilePath;
                virifyDatasToSubmitDataView();
            }
        });
        driverOcrVh.getRoot().setVisibility(View.GONE);

        View driverFillView = findViewById(R.id.authenticate_address_driver_fill_root);
        driverFillVh = new AddressCardAuthDriverFillVh(this, driverFillView, new AddressCardAuthDriverFillVh.Callback() {
            @Override
            public void onChange(String driverLicense, String birthday) {
                driverFillLicenseText = driverLicense;
                driverFillBirthday = birthday;
                virifyDatasToSubmitDataView();
            }
        });
        driverFillVh.getRoot().setVisibility(View.GONE);

        nextTxt = findViewById(R.id.authenticate_address_next_txt);
        nextTxt.setEnabled(false);

        restoreInstanceState(savedInstanceState);
    }

    private void cleanupHistoryDataAndDisplay(int currentType) {
        final int previousType = selectedType;
        if (previousType == currentType) {
            return;
        }

        aadhaarVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR == currentType ? View.VISIBLE : View.GONE);
        voterIdVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID == currentType ? View.VISIBLE : View.GONE);
        passportVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT == currentType ? View.VISIBLE : View.GONE);
        driverOcrVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER == currentType ? View.VISIBLE : View.GONE);
        driverFillVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER == currentType ? View.VISIBLE : View.GONE);

        switch (previousType) {
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR:
                aadhaarData = null;
                aadhaarBackFilePath = aadhaarFrontFilePath = null;
                aadhaarVh.restartVerify();
                break;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID:
                voterIdData = null;
                voterIdBackFilePath = voterIdFrontFilePath = null;
                voterIdVh.restartVerify();
                break;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT:
                passportData = null;
                passportFrontFilePath = passportBackFilePath = null;
                passportVh.restartVerify();
                break;
            case StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER:
                driverOcrData = null;
                driverOcrFrontFilePath = driverOcrBackFilePath = null;
                driverOcrVh.restartVerify();

                driverFillLicenseText = driverFillBirthday = null;
                driverFillVh.restartVerify();
                break;
            default:
                break;
        }
        selectedType = currentType;
    }

    private void virifyDatasToSubmitDataView() {
        boolean enabledForVoterId = addressCardAuthConfigResponseModel.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID) && voterIdData != null;
        boolean enabledForPassport = addressCardAuthConfigResponseModel.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT) && passportData != null;
        boolean enabledForDriver = addressCardAuthConfigResponseModel.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER)
                && driverOcrData != null && driverFillLicenseText != null && driverFillLicenseText.length() >= 5 && driverFillBirthday != null;

        boolean enabled;
        boolean enabledAadhaar = addressCardAuthConfigResponseModel.hasThisType(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR) && aadhaarData != null;
        enabled = enabledAadhaar || enabledForVoterId || enabledForPassport || enabledForDriver;
        nextTxt.setEnabled(enabled);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
        toolBar.setLeftClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View view) {
                finish();
            }
        });
    }

    /**
     * 认证失败，所有的认证项都可以重新进行认证了
     */
    private void restartVerify() {
        aadhaarOcrMustVh.restartVerify();
        aadhaarVh.restartVerify();
        voterIdVh.restartVerify();
        passportVh.restartVerify();
        driverOcrVh.restartVerify();
        driverFillVh.restartVerify();
        nextTxt.setEnabled(false);
    }


    @Override
    public void setupDatas() {
        // 被系统回收后，再次进入该页面
        if (addressCardAuthConfigResponseModel != null) {
            return;
        }
        //loadConfig
        HttpSender.get(new CommonResponsePendingCallback<AddressCardAuthConfigResponseModel>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 获取地址证明的配置信息
                String getAddressProofConfigUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_GET_ADDRESS_CARD_CONFIG);
                return new CommonRequestParams(getAddressProofConfigUrl);
            }

            @Override
            public void handleSuccess(AddressCardAuthConfigResponseModel resultData, int resultCode, String resultMessage) {
                addressCardAuthConfigResponseModel = resultData;
                refreshByConfigData();
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showTipDialog(resultMessage);
                addressCardAuthConfigResponseModel = new AddressCardAuthConfigResponseModel();
                refreshByConfigData();
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
            }
        });
    }

    private void refreshByConfigData() {
//        aadhaarOcrMustVh.getRoot().setVisibility(addressCardAuthConfigResponseModel.isAadhaarIsMust() ? View.VISIBLE : View.GONE);
        aadhaarOcrMustVh.getRoot().setVisibility(View.GONE);

        // 所有项都不需认证
        if (AddressCardAuthConfigResponseModel.goneAll(addressCardAuthConfigResponseModel)) {
            selectedType = StringConstant.ADDRESS_CARD_AUTH_RESPONSE_UNKNOWN;

            authTypeChooseVh.getRoot().setVisibility(View.GONE);
            aadhaarVh.getRoot().setVisibility(View.GONE);
            voterIdVh.getRoot().setVisibility(View.GONE);
            passportVh.getRoot().setVisibility(View.GONE);
            driverOcrVh.getRoot().setVisibility(View.GONE);
            driverFillVh.getRoot().setVisibility(View.GONE);
        }
        // 只有一项: 必须认证该项地址证明
        else if (AddressCardAuthConfigResponseModel.onlyShow(addressCardAuthConfigResponseModel)) {
            selectedType = addressCardAuthConfigResponseModel.getSelectorTypes().get(0);

            authTypeChooseVh.getRoot().setVisibility(View.GONE);
            aadhaarVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.onlyShowAadhaar(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            voterIdVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.onlyShowVoterId(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            passportVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.onlyShowPassport(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            driverOcrVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.onlyShowDriver(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            driverFillVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.onlyShowDriver(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
        }
        // 可以选择其中一项
        else {
            selectedType = addressCardAuthConfigResponseModel.isShowSelectorDefault() ? addressCardAuthConfigResponseModel.getSelectorTypes().get(0) : StringConstant.ADDRESS_CARD_AUTH_RESPONSE_UNKNOWN;

            authTypeChooseVh.getRoot().setVisibility(View.VISIBLE);
            authTypeChooseVh.bindViewData(addressCardAuthConfigResponseModel);
            authTypeChooseVh.refreshView(selectedType);

            aadhaarVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.defaultIsAadhaar(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            voterIdVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.defaultIsVoterId(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            passportVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.defaultIsPassport(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            driverOcrVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.defaultIsDriver(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
            driverFillVh.getRoot().setVisibility(AddressCardAuthConfigResponseModel.defaultIsDriver(addressCardAuthConfigResponseModel) ? View.VISIBLE : View.GONE);
        }
    }


    @Override
    public boolean dispatchTouchEvent(MotionEvent ev) {
        if (ev.getAction() == MotionEvent.ACTION_DOWN) {
            View v = getCurrentFocus();
            //当isShouldHideInput(v, ev)为true时，表示的是点击输入框区域，则需要显示键盘，同时显示光标，反之，需要隐藏键盘、光标
            if (isShouldHideInput(v, ev)) {
                InputMethodManager imm = (InputMethodManager) getSystemService(Context.INPUT_METHOD_SERVICE);
                if (imm != null) {
                    imm.hideSoftInputFromWindow(v.getWindowToken(), 0);
                    //处理Editext的光标隐藏、显示逻辑
                    driverFillVh.getmDriverLicenseEdit().clearFocus();
                }
            }
        }
        return super.dispatchTouchEvent(ev);
    }

    private boolean isShouldHideInput(View v, MotionEvent event) {
        if (v != null && (v instanceof EditText)) {
            int[] leftTop = {0, 0};
            //获取输入框当前的location位置
            v.getLocationInWindow(leftTop);
            int left = leftTop[0];
            int top = leftTop[1];
            int bottom = top + v.getHeight();
            int right = left + v.getWidth();
            if (event.getX() > left && event.getX() < right && event.getY() > top && event.getY() < bottom) {
                // 点击的是输入框区域，保留点击EditText的事件
                return false;
            } else {
                return true;
            }
        }
        return false;
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (aadhaarOcrMustVh != null && aadhaarOcrMustVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (aadhaarVh != null && aadhaarVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (voterIdVh != null && voterIdVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (passportVh != null && passportVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (driverOcrVh != null && driverOcrVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        // 先要处理图片请求的
        super.onActivityResult(requestCode, resultCode, data);
    }


    //<editor-fold  desc='内存紧张'>

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);

        outState.putSerializable("addressCardAuthConfigResponseModel", addressCardAuthConfigResponseModel);
        outState.putInt("selectedType", selectedType);

        outState.putSerializable("aadhaarData", aadhaarData);
        outState.putString("aadhaarFrontFilePath", aadhaarFrontFilePath);
        outState.putString("aadhaarBackFilePath", aadhaarBackFilePath);

        outState.putSerializable("voterIdData", voterIdData);
        outState.putString("voterIdFrontFilePath", voterIdFrontFilePath);
        outState.putString("voterIdBackFilePath", voterIdBackFilePath);

        outState.putSerializable("passportData", passportData);
        outState.putString("passportFrontFilePath", passportFrontFilePath);
        outState.putString("passportBackFilePath", passportBackFilePath);

        outState.putSerializable("driverOcrData", driverOcrData);
        outState.putString("driverOcrFrontFilePath", driverOcrFrontFilePath);
        outState.putString("driverOcrBackFilePath", driverOcrBackFilePath);
        outState.putString("driverFillLicenseText", driverFillLicenseText);
        outState.putString("driverFillBirthday", driverFillBirthday);
    }

    private void restoreInstanceState(Bundle savedInstanceState) {
        if (savedInstanceState == null) {
            return;
        }

        addressCardAuthConfigResponseModel = (AddressCardAuthConfigResponseModel) savedInstanceState.getSerializable("addressCardAuthConfigResponseModel");
        refreshByConfigData();

        int cacheSelectedType = savedInstanceState.getInt("selectedType");
        selectedType = cacheSelectedType;
        authTypeChooseVh.refreshView(cacheSelectedType);
        aadhaarVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_AADHAAR == cacheSelectedType ? View.VISIBLE : View.GONE);
        voterIdVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_VOTERID == cacheSelectedType ? View.VISIBLE : View.GONE);
        passportVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_PASSPORT == cacheSelectedType ? View.VISIBLE : View.GONE);
        driverOcrVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER == cacheSelectedType ? View.VISIBLE : View.GONE);
        driverFillVh.getRoot().setVisibility(StringConstant.ADDRESS_CARD_AUTH_RESPONSE_DRIVER == cacheSelectedType ? View.VISIBLE : View.GONE);

        aadhaarData = (UploadAddressCardAuthResponseModel) savedInstanceState.getSerializable("aadhaarData");
        aadhaarFrontFilePath = savedInstanceState.getString("aadhaarFrontFilePath");
        aadhaarBackFilePath = savedInstanceState.getString("aadhaarBackFilePath");
        if (aadhaarData != null && aadhaarFrontFilePath != null && aadhaarBackFilePath != null) {
            aadhaarVh.refreshView(aadhaarFrontFilePath, aadhaarBackFilePath);
        }

        voterIdData = (UploadAddressCardAuthResponseModel) savedInstanceState.getSerializable("voterIdData");
        voterIdFrontFilePath = savedInstanceState.getString("voterIdFrontFilePath");
        voterIdBackFilePath = savedInstanceState.getString("voterIdBackFilePath");
        if (voterIdData != null && voterIdFrontFilePath != null && voterIdBackFilePath != null) {
            voterIdVh.refreshView(voterIdFrontFilePath, voterIdBackFilePath);
        }

        passportData = (UploadAddressCardAuthResponseModel) savedInstanceState.getSerializable("passportData");
        passportFrontFilePath = savedInstanceState.getString("passportFrontFilePath");
        passportBackFilePath = savedInstanceState.getString("passportBackFilePath");
        if (passportData != null && passportFrontFilePath != null && passportBackFilePath != null) {
            passportVh.refreshView(passportFrontFilePath, passportBackFilePath);
        }

        driverOcrData = (UploadAddressCardAuthResponseModel) savedInstanceState.getSerializable("driverOcrData");
        driverOcrFrontFilePath = savedInstanceState.getString("driverOcrFrontFilePath");
        driverOcrBackFilePath = savedInstanceState.getString("driverOcrBackFilePath");
        if (driverOcrData != null && driverOcrFrontFilePath != null && driverOcrBackFilePath != null) {
            driverOcrVh.refreshView(driverOcrFrontFilePath, driverOcrBackFilePath);
        }

        driverFillLicenseText = savedInstanceState.getString("driverFillLicenseText");
        driverFillBirthday = savedInstanceState.getString("driverFillBirthday");
        if (driverFillLicenseText != null || driverFillBirthday != null) {
            driverFillVh.refreshView(driverFillLicenseText, driverFillBirthday);
        }

        virifyDatasToSubmitDataView();
    }

    @OnClick(R.id.authenticate_address_next_txt)
    public void onViewClicked() {
        //submit
        HttpSender.post(new CommonResponsePendingCallback<AddressCardAuthResponseModel>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 提交地址证明报告信息
                String saveAddressProofReportUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_SAVE_ADDRESS_CARD_REPORT);
                CommonRequestParams requestParams = new CommonRequestParams(saveAddressProofReportUrl);

                if (aadhaarData != null) {
                    requestParams.addBodyParameter("addressProofReportId", aadhaarData.getReportId());
                    requestParams.addBodyParameter("addressProofType", StringConstant.ADDRESS_PROOF_FILE_TYPE_AADHAAR);// 地址证明的提交类型
                } else if (voterIdData != null) {
                    requestParams.addBodyParameter("addressProofReportId", voterIdData.getReportId());
                    requestParams.addBodyParameter("addressProofType", StringConstant.ADDRESS_PROOF_FILE_TYPE_VOTER);// 地址证明的提交类型
                } else if (passportData != null) {
                    requestParams.addBodyParameter("addressProofReportId", passportData.getReportId());
                    requestParams.addBodyParameter("addressProofType", StringConstant.ADDRESS_PROOF_FILE_TYPE_PASSPORT);// 地址证明的提交类型
                } else if (driverOcrData != null) {
                    requestParams.addBodyParameter("addressProofReportId", driverOcrData.getReportId());
                    requestParams.addBodyParameter("addressProofType", StringConstant.ADDRESS_PROOF_FILE_TYPE_DRIVER);// 地址证明的提交类型
                    requestParams.addBodyParameter("driverLicense", driverFillLicenseText);
                    requestParams.addBodyParameter("birthday", driverFillBirthday); // 格式：dd/MM/yyyy
                }
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {
                super.handleUi(isStart);
                if (isStart) {
                    MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_ADDRESS_CLICK).build();
                }
            }

            @Override
            public void handleSuccess(AddressCardAuthResponseModel resultData, int resultCode, String resultMessage) {
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_ADDRESS_SUCCES).build();

                if (!StringUtil.isBlank(resultData.getJump())) {
                    JumpOperationHandler.convert(resultData.getJump()).createRequest().setDisplay(display()).jump();
                    finish();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                showTipDialog(resultMessage);
                // 可重新编辑
                restartVerify();
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
            }
        });
    }

    //</editor-fold  desc='内存紧张'>
}

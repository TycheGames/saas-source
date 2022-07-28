package com.bigshark.android.activities.authenticate;

import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.PersistableBundle;
import android.support.v7.app.AlertDialog;
import android.view.MotionEvent;
import android.view.View;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.TextView;

import com.bigshark.android.BuildConfig;
import com.bigshark.android.R;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.core.component.ui.edit.AlphabetReplaceMethod;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.KycDocumentsResponseModel;
import com.bigshark.android.http.model.authenticate.SaveLivenessResponseModel;
import com.bigshark.android.http.model.authenticate.SavePanResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.carema.CaptureStrategy;
import com.bigshark.android.utils.carema.MediaStoreCompat;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.bigshark.android.vh.authenticate.kycdocuments.KycDocumentsFaceAuthVh;
import com.bigshark.android.vh.authenticate.kycdocuments.KycDocumentsPanCardVh;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * kyc认证：pan卡认证 + 活体
 */
public class KycDocumentsActivity extends DisplayBaseActivity {

    private NavigationStatusLinearLayout toolBar;

    private KycDocumentsFaceAuthVh faceAuthVh;
    private KycDocumentsPanCardVh panCardVh;

    private View panNumberView;
    private EditText panNumberEdit;
    private TextView nextTxt;


    private MediaStoreCompat mMediaStoreCompat;

    private SavePanResponseModel panData;
    private String panCardPicPath;
    private SaveLivenessResponseModel livenessData;
    private byte[] livenessDetectImage;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_know_your_customer;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);

        mMediaStoreCompat = new MediaStoreCompat(this);
        mMediaStoreCompat.setCaptureStrategy(new CaptureStrategy(true, BuildConfig.FILE_PROVIDER));

        toolBar = findViewById(R.id.authenticate_kyc_title);

        panCardVh = new KycDocumentsPanCardVh(this, mMediaStoreCompat, findViewById(R.id.authenticate_kyc_pancard_root), new KycDocumentsPanCardVh.Callback() {
            @Override
            public void onSuccess(SavePanResponseModel data, String panCardPicPath) {
                panData = data;

                panNumberView.setVisibility(View.VISIBLE);
                panNumberEdit.setText(data.getPanCode());
                virifyDatasToSubmitDataView();
            }
        });
        panNumberView = findViewById(R.id.authenticate_kyc_pan_number_root);
        panNumberView.setVisibility(View.GONE);
        panNumberEdit = findViewById(R.id.authenticate_kyc_pan_number_edit);
        panNumberEdit.setTransformationMethod(new AlphabetReplaceMethod());

        faceAuthVh = new KycDocumentsFaceAuthVh(this, findViewById(R.id.authenticate_kyc_face_root), new KycDocumentsFaceAuthVh.Callback() {
            @Override
            public void onSuccess(SaveLivenessResponseModel data, byte[] detectImage) {
                livenessData = data;
                livenessDetectImage = detectImage;
                virifyDatasToSubmitDataView();
            }

            @Override
            public void onFailed() {
                livenessData = null;
            }
        });

        nextTxt = findViewById(R.id.authenticate_kyc_next_txt);
        nextTxt.setEnabled(false);
    }

    private void virifyDatasToSubmitDataView() {
        boolean enabled = panData != null && livenessData != null;
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


//    private final SaveKnowYourCustomerRequestData submitBean = new SaveKnowYourCustomerRequestData();

    /**
     * 认证失败，所有的认证项都可以重新进行认证了
     */
    private void restartVerify() {
        panCardVh.restartVerify();
        faceAuthVh.restartVerify();
        nextTxt.setEnabled(false);
    }


    @Override
    public void setupDatas() {
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (faceAuthVh != null && faceAuthVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        if (panCardVh != null && panCardVh.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        // 先要处理图片请求的
        super.onActivityResult(requestCode, resultCode, data);
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
                    panNumberEdit.clearFocus();
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


    // ***************** 内存紧张 *****************


    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);
//        KLog.d("panData:" + ConvertUtils.toString(panData));
//        KLog.d("panCardPicPath:" + panCardPicPath);
//        KLog.d("livenessData:" + ConvertUtils.toString(livenessData));

        outState.putSerializable("panData", panData);
        outState.putString("panCardPicPath", panCardPicPath);

        outState.putSerializable("livenessData", livenessData);
        outState.putByteArray("livenessDetectImage", livenessDetectImage);
    }

    @Override
    public void onSaveInstanceState(Bundle outState, PersistableBundle outPersistentState) {
        super.onSaveInstanceState(outState, outPersistentState);
    }


    @Override
    protected void onRestoreInstanceState(Bundle savedInstanceState) {
        super.onRestoreInstanceState(savedInstanceState);
        panData = (SavePanResponseModel) savedInstanceState.getSerializable("panData");
        panCardPicPath = savedInstanceState.getString("panCardPicPath");
        if (panData != null && panCardPicPath != null) {
            panCardVh.refreshView(panCardPicPath);
            panNumberView.setVisibility(View.VISIBLE);
            panNumberEdit.setText(panData.getPanCode());
        }

        livenessData = (SaveLivenessResponseModel) savedInstanceState.getSerializable("livenessData");
        livenessDetectImage = savedInstanceState.getByteArray("livenessDetectImage");
        if (livenessData != null && livenessDetectImage != null) {
            faceAuthVh.refreshView(livenessDetectImage);
        }

        virifyDatasToSubmitDataView();
//
//        KLog.d("panData:" + ConvertUtils.toString(panData));
//        KLog.d("panCardPicPath:" + panCardPicPath);
//        KLog.d("livenessData:" + ConvertUtils.toString(livenessData));
    }

    @Override
    public void onRestoreInstanceState(Bundle savedInstanceState, PersistableBundle persistentState) {
        super.onRestoreInstanceState(savedInstanceState, persistentState);
    }

    @OnClick(R.id.authenticate_kyc_next_txt)
    public void onViewClicked() {
        if (panData == null) {
            showToast(R.string.auth_kyc_tip_pan);
            return;
        }
        if (livenessData == null) {
            showToast(R.string.auth_kyc_tip_liveness);
            return;
        }

        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_EKYC).build();
        MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_KYC_CLICK).build();
        //submit
        HttpSender.post(new CommonResponsePendingCallback<KycDocumentsResponseModel>(display()) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 提交kyc报告信息
                String saveKeyReportUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_UPLOAD_USER_KYC_REPORT);
                CommonRequestParams requestParams = new CommonRequestParams(saveKeyReportUrl);
                requestParams.addBodyParameter("panReportId", panData.getReportId());// pan卡
                requestParams.addBodyParameter("frReportId", livenessData.getReportId());// 活体认证
                requestParams.addBodyParameter("panCode", panNumberEdit.getText().toString().trim().toUpperCase());// pan卡的number
//                requestParams.addBodyParameter("crossReportId", "");// 交叉对比报告编号（当用户选择aadOCR是第一次保存返回，默认值为字符串空）
                return requestParams;
            }

            @Override
            public void handleSuccess(KycDocumentsResponseModel resultData, int resultCode, String resultMessage) {
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_KYC_SUCCESS).build();

                if (!StringUtil.isBlank(resultData.getJump())) {
                    JumpOperationHandler.convert(resultData.getJump()).createRequest().setDisplay(display()).jump();
                    finish();
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                MaiDianUploaderUtils.Builder.create(display()).setEventName(StringConstant.EVENT_AUTH_KYC_FAILED).build();

                new AlertDialog.Builder(act()).setTitle("Tips").setMessage(resultMessage)
                        .setPositiveButton("OK, I GOT IT !", null)
                        .show();
                // 可重新编辑
                restartVerify();
            }

            @Override
            public void onCancelled(CancelledException cex) {
                super.onCancelled(cex);
            }
        });
    }
}

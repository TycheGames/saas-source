package com.bigshark.android.activities.authenticate;

import android.content.DialogInterface;
import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Bundle;
import android.support.v7.app.AlertDialog;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.core.component.navigator.NavigationStatusLinearLayout;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.PersonalFaceAuthenticateResponseModel;
import com.bigshark.android.http.model.authenticate.SaveLivenessResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.vh.authenticate.personalface.PersonalFaceAuthenticateUtils;

import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 复借时的活体认证
 */
public class PersonalFaceAuthenticateActivity extends DisplayBaseActivity {

    private NavigationStatusLinearLayout titleView;
    private ImageView selfieImage;
    private TextView submitView;

    private PersonalFaceAuthenticateUtils mPersonalFaceAuthenticateUtils;
    private SaveLivenessResponseModel livenessResponseModel;

    @Override
    protected int getLayoutId() {
        return R.layout.activity_face_authenticate;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        titleView = findViewById(R.id.authenticate_liveness_title);
        selfieImage = findViewById(R.id.authenticate_liveness_selfie_img);
        submitView = findViewById(R.id.authenticate_liveness_submit);
        submitView.setEnabled(false);

        mPersonalFaceAuthenticateUtils = new PersonalFaceAuthenticateUtils(this, true, new PersonalFaceAuthenticateUtils.Callback() {
            @Override
            public void onUploadSuccess(SaveLivenessResponseModel data, byte[] detectImage) {
                livenessResponseModel = data;
                selfieImage.setEnabled(false);
                submitView.setEnabled(true);
                refreshView(detectImage);
//                MaiDianUploaderUtils.authEkycLiveness(display());
            }

            @Override
            public void onUploadFailed() {
            }
        });
    }

    private void refreshView(byte[] detectImage) {
        //静默检测框内图片
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inPreferredConfig = Bitmap.Config.ARGB_8888;
        Bitmap bitmap = BitmapFactory.decodeByteArray(detectImage, 0, detectImage.length, options);
        selfieImage.setImageBitmap(bitmap);
//        picImage.setEnabled(false);
    }


    @Override
    public void bindListeners(Bundle savedInstanceState) {
        titleView.setLeftClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                showLeaveDialog();
            }
        });
    }


    private void showLeaveDialog() {
        AlertDialog leaveDialog = new AlertDialog.Builder(this)
                .setMessage("One step only, confirm to give up?")
                .setNeutralButton(" YES ", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        finish();
                    }
                })
                .setPositiveButton(" NO ", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                    }
                })
                .show();
        leaveDialog.getButton(DialogInterface.BUTTON_NEUTRAL).setTextColor(getResources().getColor(R.color.color_common_black));
        leaveDialog.getButton(DialogInterface.BUTTON_POSITIVE).setTextColor(getResources().getColor(R.color.theme_secondary_color));
    }

    @Override
    public void setupDatas() {
    }


    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (mPersonalFaceAuthenticateUtils != null && mPersonalFaceAuthenticateUtils.onActivityResult(requestCode, resultCode, data)) {
            return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }


    @Override
    public void onBackPressed() {
//        super.onBackPressed();
        showLeaveDialog();
    }

    @OnClick({R.id.authenticate_liveness_selfie_img, R.id.authenticate_liveness_submit})
    public void onViewClicked(View view) {
        switch (view.getId()) {
            case R.id.authenticate_liveness_selfie_img:
                mPersonalFaceAuthenticateUtils.startTakeSelfie();
                break;
            case R.id.authenticate_liveness_submit:
                if (livenessResponseModel == null) {
                    showToast(R.string.auth_kyc_tip_liveness);
                    return;
                }
//                MaiDianUploaderUtils.authEkyc(display());
                //submit
                HttpSender.post(new CommonResponsePendingCallback<PersonalFaceAuthenticateResponseModel>(display()) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 复借时，重做活体认证的reportid上报
                        String uploadLivenessRedoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_LIVENESS_REDO);
                        CommonRequestParams requestParams = new CommonRequestParams(uploadLivenessRedoUrl);
                        requestParams.addBodyParameter("reportId", livenessResponseModel.getReportId());// 活体认证
                        return requestParams;
                    }

                    @Override
                    public void handleSuccess(PersonalFaceAuthenticateResponseModel resultData, int resultCode, String resultMessage) {
                        JumpOperationHandler.convert(resultData.getJump()).createRequest().setDisplay(display()).jump();
                        finish();
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        showToast(resultMessage);
                        // 认证失败，可以重新进行认证
                        selfieImage.setEnabled(true);
                        selfieImage.setImageResource(R.drawable.user_authenticate_liveness_take);
                    }
                });
                break;
            default:
                break;
        }
    }
}

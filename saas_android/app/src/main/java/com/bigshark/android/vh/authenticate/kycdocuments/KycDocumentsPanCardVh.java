package com.bigshark.android.vh.authenticate.kycdocuments;

import android.Manifest;
import android.app.Activity;
import android.content.Intent;
import android.os.Environment;
import android.support.annotation.NonNull;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.KycDocumentsActivity;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.file.FileSizeUtil;
import com.bigshark.android.core.utils.image.ImageBitmapUtils;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.SavePanResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.carema.MediaStoreCompat;
import com.bigshark.android.utils.image.compress.ImageFileCompressUtils;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.socks.library.KLog;

import java.io.File;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.Locale;

/**
 * pan卡认证
 * Created by ytxu on 2019/9/3.
 */
public class KycDocumentsPanCardVh extends DisplayBaseVh<View, Void> {

    private MediaStoreCompat mMediaStoreCompat;
    private Callback mCallback;
    private TextView mTitleView;
    private ImageView mPicImage, mStatusImage;

    private SavePanResponseModel mSuccessData;// 上传成功才有的值

    private KycDocumentsActivity mKycDocumentsActivity;

    public KycDocumentsPanCardVh(KycDocumentsActivity activity, MediaStoreCompat mediaStoreCompat, View root, Callback mCallback) {
        super(activity);
        this.mKycDocumentsActivity = activity;
        this.mMediaStoreCompat = mediaStoreCompat;
        this.mCallback = mCallback;
        initViews(root);
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mTitleView = findViewById(R.id.authenticate_kyc_pancard_title);
        mPicImage = findViewById(R.id.authenticate_kyc_pancard_pic);
        mStatusImage = findViewById(R.id.authenticate_kyc_pancard_status);
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();
        mPicImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                PermissionTipInfo tip = PermissionTipInfo.getTip("Read External Storage", "Camera");
                PermissionsUtil.requestPermission(mKycDocumentsActivity, new PermissionListener() {
                    @Override
                    public void permissionGranted(@NonNull String[] permission) {
                        pickPhoto();
                    }

                    @Override
                    public void permissionDenied(@NonNull String[] permission) {
                        mKycDocumentsActivity.showToast(R.string.common_takePhoto_permission_denied_tip);
                    }
                }, tip, Manifest.permission.READ_EXTERNAL_STORAGE, Manifest.permission.CAMERA);
            }
        });
    }

    private void pickPhoto() {
        MaiDianUploaderUtils.Builder.create(mKycDocumentsActivity).setEventName(StringConstant.EVENT_AUTH_PAN_CLICK).build();

        String timeStamp = new SimpleDateFormat("yyyy_MM_dd_HH_mm_ss", Locale.getDefault()).format(new Date());
        String fileName = String.format("%s_%s.jpg", "pan_card", timeStamp);
        File pancardFile = new File(mKycDocumentsActivity.getExternalFilesDir(Environment.DIRECTORY_PICTURES), fileName);

        mMediaStoreCompat.dispatchCaptureIntent(mKycDocumentsActivity, pancardFile, RequestCodeType.PANCARD);
    }


    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode != RequestCodeType.PANCARD) {
            return false;
        }

        if (resultCode == Activity.RESULT_OK) {
            String originalImageFilePath = mMediaStoreCompat.getCurrentPhotoPath();
            String compressedImageFilePath = ImageFileCompressUtils.compressImageFileAndReturnCompressedImageFilePath(mKycDocumentsActivity, originalImageFilePath);
            KLog.d(FileSizeUtil.getAutoFileOrFilesSize(originalImageFilePath) + "：" + FileSizeUtil.getAutoFileOrFilesSize(compressedImageFilePath));

            //submit
            HttpSender.post(new CommonResponsePendingCallback<SavePanResponseModel>(mKycDocumentsActivity) {

                @Override
                public CommonRequestParams createRequestParams() {
                    // 保存用户Pan卡数据
                    String savePanInfoUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_UPLOAD_USER_PAN_CARD);
                    CommonRequestParams requestParams = new CommonRequestParams(savePanInfoUrl);

                    requestParams.setMultipart(true);
                    File panCardPicFile = new File(compressedImageFilePath);
                    requestParams.addBodyParameter("panPic", panCardPicFile, null, panCardPicFile.getName());// pan卡照片，文件流，压缩
                    return requestParams;
                }

                @Override
                public void handleUi(boolean isStart) {
                    super.handleUi(isStart);
                    if (isStart) {
                        MaiDianUploaderUtils.Builder.create(mKycDocumentsActivity).setEventName(StringConstant.EVENT_AUTH_PAN_UPLOAD).build();
                    }
                }

                @Override
                public void handleSuccess(SavePanResponseModel resultData, int resultCode, String resultMessage) {
                    mSuccessData = resultData;
                    refreshView(compressedImageFilePath);
                    MaiDianUploaderUtils.Builder.create(mKycDocumentsActivity).setEventName(StringConstant.EVENT_AUTH_EKYC_PAN).build();
                    MaiDianUploaderUtils.Builder.create(mKycDocumentsActivity).setEventName(StringConstant.EVENT_AUTH_PAN_UPLOAD_SUCCESS).build();
                    mCallback.onSuccess(resultData, compressedImageFilePath);
                }

                @Override
                public void handleFailed(int resultCode, String resultMessage) {
                    mKycDocumentsActivity.showToast(resultMessage);
                    mStatusImage.setImageResource(R.drawable.user_authenticate_status_failed);
                    MaiDianUploaderUtils.Builder.create(mKycDocumentsActivity).setEventName(StringConstant.EVENT_AUTH_PAN_UPLOAD_FAILED).build();
                }
            });
        }
        return true;
    }

    public void refreshView(String panCardPicPath) {
        mPicImage.setImageBitmap(ImageBitmapUtils.getCompressedBmp(panCardPicPath));
        mPicImage.setEnabled(false);
        mStatusImage.setImageResource(R.drawable.user_authenticate_status_successed);
    }


    public void restartVerify() {
        mPicImage.setEnabled(true);
        mStatusImage.setImageResource(R.drawable.user_authenticate_status_default);
    }


    public interface Callback {
        void onSuccess(SavePanResponseModel data, String panCardPicPath);
    }
}

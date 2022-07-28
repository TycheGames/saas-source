package com.bigshark.android.vh.authenticate.addresscard.passport;

import android.Manifest;
import android.app.Activity;
import android.content.Intent;
import android.os.Environment;
import android.support.annotation.NonNull;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.AddressCardAuthActivity;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.image.ImageBitmapUtils;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.UploadAddressCardAuthResponseModel;
import com.bigshark.android.http.model.param.AddressProofOcrType;
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
 * 地址证明--护照
 * Created by ytxu on 2019/9/3.
 */
public class AddressCardAuthPassportVh extends DisplayBaseVh<View, Void> {

    private MediaStoreCompat mMediaStoreCompat;
    private Callback mCallback;

    private View mFrontRoot;
    private ImageView mFrontPicImage, mFrontStatusImage;

    private View mBackRoot;
    private ImageView mBackPicImage, mBackStatusImage;

    private String mFrontFilePath, mBackFilePath;// 图片的路径
    private UploadAddressCardAuthResponseModel mSuccessData;// 上传成功才有的值

    private AddressCardAuthActivity mAddressCardAuthActivity;

    public AddressCardAuthPassportVh(AddressCardAuthActivity activity, MediaStoreCompat mMediaStoreCompat, View root, Callback mCallback) {
        super(activity);
        this.mAddressCardAuthActivity = activity;
        this.mMediaStoreCompat = mMediaStoreCompat;
        this.mCallback = mCallback;
        initViews(root);
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mFrontRoot = findViewById(R.id.authenticate_address_passport_front_root);
        mFrontPicImage = findViewById(R.id.authenticate_address_passport_front_pic);
        mFrontStatusImage = findViewById(R.id.authenticate_address_passport_front_status);

        mBackRoot = findViewById(R.id.authenticate_address_passport_back_root);
        mBackPicImage = findViewById(R.id.authenticate_address_passport_back_pic);
        mBackStatusImage = findViewById(R.id.authenticate_address_passport_back_status);
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();
        mFrontRoot.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                requestPermission(true);
            }
        });
        mBackRoot.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                requestPermission(false);
            }
        });
    }

    private void requestPermission(final boolean isFront) {
        PermissionTipInfo tip = PermissionTipInfo.getTip("Read External Storage", "Camera");
        PermissionsUtil.requestPermission(mAddressCardAuthActivity.act(), new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                pickPhoto(isFront);
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                mAddressCardAuthActivity.showToast(R.string.common_takePhoto_permission_denied_tip);
            }
        }, tip, Manifest.permission.READ_EXTERNAL_STORAGE, Manifest.permission.CAMERA);
    }

    private void pickPhoto(final boolean isFront) {
        takePhotoStatus = isFront ? TakePhotoStatus.TAKE_FRONT : TakePhotoStatus.TAKE_BACK;

        String fileTagName = "address_passport_" + (isFront ? "front" : "back");
        String timeStamp = new SimpleDateFormat("yyyy_MM_dd_HH_mm_ss", Locale.getDefault()).format(new Date());
        String fileName = String.format("%s_%s.jpg", fileTagName, timeStamp);
        File passportFile = new File(mAddressCardAuthActivity.getExternalFilesDir(Environment.DIRECTORY_PICTURES), fileName);

        mMediaStoreCompat.dispatchCaptureIntent(mAddressCardAuthActivity, passportFile, RequestCodeType.POSSPART);
    }


    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode != RequestCodeType.POSSPART) {
            return false;
        }

        if (resultCode == Activity.RESULT_OK) {
            final String originalImageFilePath = mMediaStoreCompat.getCurrentPhotoPath();
            String compressedImageFilePath = ImageFileCompressUtils.compressImageFileAndReturnCompressedImageFilePath(mAddressCardAuthActivity, originalImageFilePath);

            KLog.d("takePhotoStatus:" + takePhotoStatus + ", is front:" + (takePhotoStatus == TakePhotoStatus.TAKE_FRONT));
            if (takePhotoStatus == TakePhotoStatus.TAKE_FRONT) {
                mFrontFilePath = compressedImageFilePath;
                mFrontPicImage.setImageBitmap(ImageBitmapUtils.getCompressedBmp(mFrontFilePath));
                mFrontStatusImage.setImageResource(R.drawable.user_authenticate_status_default);
            } else {
                mBackFilePath = compressedImageFilePath;
                mBackPicImage.setImageBitmap(ImageBitmapUtils.getCompressedBmp(mBackFilePath));
                mBackStatusImage.setImageResource(R.drawable.user_authenticate_status_default);
            }

            if (mFrontFilePath != null && mBackFilePath != null) {
                //submit
                HttpSender.post(new CommonResponsePendingCallback<UploadAddressCardAuthResponseModel>(mAddressCardAuthActivity) {

                    @Override
                    public CommonRequestParams createRequestParams() {
                        // 上传 地址证明 ocr认证
                        String saveAddhaarOcrUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_SAVE_AADHAAR_OCR);
                        CommonRequestParams requestParams = new CommonRequestParams(saveAddhaarOcrUrl);

                        requestParams.setMultipart(true);
                        requestParams.addBodyParameter("addressProofType", AddressProofOcrType.PASSPORT);// 地址证明的提交类型
                        File frontFilePathFile = new File(mFrontFilePath);
                        requestParams.addBodyParameter("picFront", frontFilePathFile, null, frontFilePathFile.getName());// aadhaar卡人像面照片，压缩(<=5M)
                        File backFilePathFile = new File(mBackFilePath);
                        requestParams.addBodyParameter("picBack", backFilePathFile, null, backFilePathFile.getName());// aadhaar卡背面面照片，压缩(<=5M)
                        return requestParams;
                    }

                    @Override
                    public void handleUi(boolean isStart) {
                        super.handleUi(isStart);
                        if (isStart) {
                            MaiDianUploaderUtils.Builder.create(mAddressCardAuthActivity).setEventName(StringConstant.EVENT_AUTH_AADHAAR_SUBMIT).build();
                        }
                    }

                    @Override
                    public void handleSuccess(UploadAddressCardAuthResponseModel resultData, int resultCode, String resultMessage) {
                        MaiDianUploaderUtils.Builder.create(mAddressCardAuthActivity).setEventName(StringConstant.EVENT_AUTH_AADHAAR_SUBMIT_SUCCESS).build();
                        mSuccessData = resultData;
                        refreshView();
                        mCallback.onSuccess(resultData, mFrontFilePath, mBackFilePath);
                    }

                    @Override
                    public void handleFailed(int resultCode, String resultMessage) {
                        mAddressCardAuthActivity.showToast(resultMessage);

                        mFrontPicImage.setImageResource(R.drawable.user_authenticate_aadhaar_front);
                        mFrontStatusImage.setImageResource(R.drawable.user_authenticate_status_failed);
                        mBackPicImage.setImageResource(R.drawable.user_authenticate_aadhaar_back);
                        mBackStatusImage.setImageResource(R.drawable.user_authenticate_status_failed);
                        mFrontFilePath = null;
                        mBackFilePath = null;
                    }
                });
            }
        }
        return true;
    }

    private void refreshView() {
        mFrontRoot.setEnabled(false);
        mFrontPicImage.setImageBitmap(ImageBitmapUtils.getCompressedBmp(mFrontFilePath));
        mFrontStatusImage.setImageResource(R.drawable.user_authenticate_status_successed);

        mBackRoot.setEnabled(false);
        mBackPicImage.setImageBitmap(ImageBitmapUtils.getCompressedBmp(mBackFilePath));
        mBackStatusImage.setImageResource(R.drawable.user_authenticate_status_successed);
    }

    public void refreshView(String aadhaarFrontFilePath, String aadhaarBackFilePath) {
        this.mFrontFilePath = aadhaarFrontFilePath;
        this.mBackFilePath = aadhaarBackFilePath;
        refreshView();
    }


    private int takePhotoStatus = TakePhotoStatus.UN_TAKE;// 获取图片的状态

    private static final class TakePhotoStatus {
        public static final int UN_TAKE = 0;// 为进行图片获取
        public static final int TAKE_FRONT = 1;// 是否正在获取正面图片
        public static final int TAKE_BACK = 2;// 是否正在获取反面图片
    }


    public void restartVerify() {
        mFrontRoot.setEnabled(true);
        mFrontPicImage.setImageResource(R.drawable.user_authenticate_address_pic_default);
        mFrontStatusImage.setImageResource(R.drawable.user_authenticate_status_default);

        mBackRoot.setEnabled(true);
        mBackStatusImage.setImageResource(R.drawable.user_authenticate_address_pic_default);
        mBackStatusImage.setImageResource(R.drawable.user_authenticate_status_default);
    }


    public interface Callback {
        void onSuccess(UploadAddressCardAuthResponseModel data, String frontFilePath, String backFilePath);
    }
}

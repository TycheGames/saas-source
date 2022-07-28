package com.bigshark.android.vh.authenticate.personalface;

import android.Manifest;
import android.app.Activity;
import android.content.DialogInterface;
import android.content.Intent;
import android.support.annotation.NonNull;
import android.support.v7.app.AlertDialog;
import android.text.TextUtils;

import com.bigshark.android.R;
import com.bigshark.android.core.common.RequestCodeType;
import com.bigshark.android.core.permission.PermissionListener;
import com.bigshark.android.core.permission.PermissionTipInfo;
import com.bigshark.android.core.permission.PermissionsUtil;
import com.bigshark.android.core.utils.encry.Md5Util;
import com.bigshark.android.core.utils.file.FileUtil;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.http.HttpConfig;
import com.bigshark.android.http.model.authenticate.SaveLivenessResponseModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponsePendingCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.thirdsdk.MaiDianUploaderUtils;
import com.deepfinch.liveness.DFLivenessSDK;
import com.liveness.dflivenesslibrary.DFProductResult;
import com.liveness.dflivenesslibrary.DFTransferResultInterface;
import com.liveness.dflivenesslibrary.liveness.DFSilentLivenessActivity;
import com.socks.library.KLog;
import com.tencent.bugly.crashreport.CrashReport;

import java.io.File;

/**
 * 活体认证
 * Created by ytxu on 2019/9/3.
 */
public class PersonalFaceAuthenticateUtils {

    private DisplayBaseActivity mDisplayBaseActivity;
    private final Callback callback;
    private final boolean isAlone; // 是否为单独的活体认证：复借时使用

//    private Double qualityScoreThreshold;// 阈值：大于该值则为活体有问题，需要重新拍摄


    public PersonalFaceAuthenticateUtils(DisplayBaseActivity activity, boolean isAlone, @NonNull Callback callback) {
        this.mDisplayBaseActivity = activity;
        this.isAlone = isAlone;
        this.callback = callback;
    }

    public void startTakeSelfie() {
        MaiDianUploaderUtils.Builder.create(mDisplayBaseActivity).setEventName(StringConstant.EVENT_AUTH_LIVENESS_CLICK).build();

        PermissionTipInfo tip = PermissionTipInfo.getTip("Read External Storage", "Camera");
        PermissionsUtil.requestPermission(mDisplayBaseActivity, new PermissionListener() {
            @Override
            public void permissionGranted(@NonNull String[] permission) {
                openFaceLivenessAuthPage();
            }

            @Override
            public void permissionDenied(@NonNull String[] permission) {
                mDisplayBaseActivity.showToast(R.string.auth_face_permission_denied_tip);
            }
        }, tip, Manifest.permission.READ_EXTERNAL_STORAGE, Manifest.permission.CAMERA);
    }

    private void openFaceLivenessAuthPage() {
        Intent intent = new Intent(mDisplayBaseActivity, DFSilentLivenessActivity.class);
//        intent.putExtras(bundle);
        //enable to get image result
        intent.putExtra(DFSilentLivenessActivity.KEY_DETECT_IMAGE_RESULT, true);
        intent.putExtra(DFSilentLivenessActivity.KEY_HINT_MESSAGE_HAS_FACE, "Please hold still");
        intent.putExtra(DFSilentLivenessActivity.KEY_HINT_MESSAGE_NO_FACE, "Please place your face inside the circle");
        intent.putExtra(DFSilentLivenessActivity.KEY_HINT_MESSAGE_FACE_NOT_VALID, "Please move away from the screen");
        mDisplayBaseActivity.startActivityForResult(intent, RequestCodeType.GOTO_DF_FACE);
    }


    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        if (requestCode == RequestCodeType.GOTO_DF_FACE) {
            if (resultCode == Activity.RESULT_OK) {
                MaiDianUploaderUtils.Builder.create(mDisplayBaseActivity).setEventName(StringConstant.EVENT_AUTH_LIVENESS_SDK_SUCCESS).build();
                handleFace();
            }
            return true;
        }
        return false;
    }

    /**
     * DFProductResult
     * //获取静默检测加密数据
     * byte[] livenessEncryptResult = mResult.getLivenessEncryptResult()
     * //获取静默检测图片
     * DFLivenessSDK.DFLivenessImageResult[] imageResultArr = mResult.getLivenessImageResults();
     * //获取图片质量分数
     * float qualityScore = mResult.getQualityScore();
     * //忽略
     * mResult.getResultImages()
     * //忽略
     * mResult.getLivenessVideoResult()
     * <p>
     * DFLivenessImageResult
     * //静默检测框内图片
     * detectImage
     * //静默检测原图
     * image
     * //忽略
     * length
     * //忽略
     * motion
     */
    private void handleFace() {
        DFProductResult mResult = ((DFTransferResultInterface) mDisplayBaseActivity.getApplication()).getResult();
        if (mResult == null) {
            CrashReport.postCatchedException(new Throwable("liveness-->mResult is empty, isAlone:" + isAlone));
            tipUserRetake();
            return;
        }

        ///get key frame
        DFLivenessSDK.DFLivenessImageResult[] imageResults = mResult.getLivenessImageResults();
        if (imageResults == null || imageResults.length <= 0) {
            CrashReport.postCatchedException(new Throwable("liveness-->imageResults is empty, isAlone:" + isAlone));
            tipUserRetake();
            return;
        }
        KLog.d("imageResults length:" + imageResults.length);

        //get the storage path of video information: 获取静默检测加密数据
        byte[] livenessEncryptResult = mResult.getLivenessEncryptResult();
        if (livenessEncryptResult == null) {
            CrashReport.postCatchedException(new Throwable("liveness-->livenessEncryptResult is null, isAlone:" + isAlone));
            tipUserRetake();
            return;
        }
        KLog.d("livenessEncryptResult: " + livenessEncryptResult.length);

        if (!mResult.isAntiHackPass()) {
            CrashReport.postCatchedException(new Throwable("liveness-->isAntiHackPass, isAlone:" + isAlone + ", errorMessage:" + mResult.getErrorMessage()));
            tipUserRetake(mResult.getErrorMessage());
            return;
        }

//        //image quality score: 获取图片质量分数
//        float qualityScore = mResult.getQualityScore();
//        KLog.d("qualityScore:" + qualityScore);
//        if (qualityScoreThreshold != null && qualityScore >= qualityScoreThreshold) {// 活体有问题，需要重新拍摄
//            CrashReport.postCatchedException(new Throwable("liveness-->quality, isAlone:" + isAlone + ", currQualityScore:" + qualityScore + ", qualityScoreThreshold:" + qualityScoreThreshold));
//            tipUserRetake();
//            return;
//        }

        upload(imageResults[0], livenessEncryptResult);
    }

    /**
     * 提示用户重新拍照
     */
    private void tipUserRetake(String errorMessage) {
        String tipMessage = TextUtils.isEmpty(errorMessage) ? mDisplayBaseActivity.getString(R.string.auth_liveness_failed_tip_message) : errorMessage;
        new AlertDialog.Builder(mDisplayBaseActivity)
                .setMessage(tipMessage)
                .setNeutralButton("Try Again", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        startTakeSelfie();
                    }
                })
                .setPositiveButton("Cancel", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                    }
                })
                .show();
    }

    /**
     * 提示用户重新拍照
     */
    private void tipUserRetake() {
        String tipMessage = mDisplayBaseActivity.getString(R.string.auth_liveness_failed_tip_message);
        new AlertDialog.Builder(mDisplayBaseActivity)
                .setMessage(tipMessage)
                .setNeutralButton("Try Again", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        startTakeSelfie();
                    }
                })
                .setPositiveButton("Cancel", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                    }
                })
                .show();
    }

    private void upload(final DFLivenessSDK.DFLivenessImageResult imageResult, byte[] livenessEncryptResult) {
        String detectImageFileName = "df_detectImage.jpg";
        final byte[] detectImage = imageResult.detectImage;
        KLog.d("imageResults.detectImage: " + detectImage.length);
        File detectImageFile = FileUtil.getFile(detectImage, mDisplayBaseActivity.getFilesDir().getAbsolutePath(), detectImageFileName);

        String livenessEncryptResultFileName = "df_livenessEncryptResult.df";
        File livenessEncryptResultFile = FileUtil.getFile(livenessEncryptResult, mDisplayBaseActivity.getFilesDir().getAbsolutePath(), livenessEncryptResultFileName);

        HttpSender.post(new CommonResponsePendingCallback<SaveLivenessResponseModel>(mDisplayBaseActivity) {

            @Override
            public CommonRequestParams createRequestParams() {
                // 上传活体(人脸)识别
                String saveLivenessUrl = HttpConfig.getRealUrl(StringConstant.HTTP_AUTHENTICATE_UPLOAD_USER_LIVENESS);
                CommonRequestParams requestParams = new CommonRequestParams(saveLivenessUrl);

                requestParams.setMultipart(true);
                requestParams.addBodyParameter("frPic", detectImageFile, null, detectImageFile.getName());//	pan卡照片，文件流，压缩
                requestParams.addBodyParameter("frData", livenessEncryptResultFile, null, livenessEncryptResultFile.getName());//  人脸数据（liveness data）
                // 规则：md5(md5(frPic) + 'loan' + md5(frData))
                String frPicMd5 = Md5Util.md5(detectImageFile);
                String frDataMd5 = Md5Util.md5(livenessEncryptResultFile);
                String sign = Md5Util.md5(frPicMd5 + "loan" + frDataMd5);
                requestParams.addBodyParameter("sign", sign);
                return requestParams;
            }

            @Override
            public void handleSuccess(SaveLivenessResponseModel resultData, int resultCode, String resultMessage) {
                MaiDianUploaderUtils.Builder.create(mDisplayBaseActivity).setEventName(StringConstant.EVENT_AUTH_LIVENESS_UPLOAD_SUCCESS).build();
                callback.onUploadSuccess(resultData, detectImage);
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                mDisplayBaseActivity.showToast(resultMessage);
                MaiDianUploaderUtils.Builder.create(mDisplayBaseActivity).setEventName(StringConstant.EVENT_AUTH_LIVENESS_UPLOAD_FAILED).build();
                CrashReport.postCatchedException(new Throwable("liveness-->report failed, isAlone:" + isAlone));

                callback.onUploadFailed();
            }
        });
    }


//    public void setQualityScoreThreshold(double qualityScoreThreshold) {
//        this.qualityScoreThreshold = qualityScoreThreshold;
//    }


    public interface Callback {

        /**
         * 上传成功
         */
        void onUploadSuccess(SaveLivenessResponseModel data, byte[] detectImage);

        /**
         * 上传失败
         */
        void onUploadFailed();
    }
}

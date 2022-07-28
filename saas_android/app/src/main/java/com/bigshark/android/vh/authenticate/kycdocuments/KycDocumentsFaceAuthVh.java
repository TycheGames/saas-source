package com.bigshark.android.vh.authenticate.kycdocuments;

import android.content.Intent;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.authenticate.KycDocumentsActivity;
import com.bigshark.android.display.DisplayBaseVh;
import com.bigshark.android.http.model.authenticate.SaveLivenessResponseModel;
import com.bigshark.android.vh.authenticate.personalface.PersonalFaceAuthenticateUtils;

/**
 * 活体认证
 * Created by ytxu on 2019/9/3.
 */
public class KycDocumentsFaceAuthVh extends DisplayBaseVh<View, Void> {

    private Callback mCallback;
    private TextView mTitleView;
    private ImageView mPicImage, mStatusImage;

    private PersonalFaceAuthenticateUtils mPersonalFaceAuthenticateUtils;

    private KycDocumentsActivity mKycDocumentsActivity;

    public KycDocumentsFaceAuthVh(KycDocumentsActivity activity, View root, Callback mCallback) {
        super(activity);
        this.mKycDocumentsActivity = activity;
        this.mCallback = mCallback;
        initViews(root);
    }

    @Override
    protected void bindViews() {
        super.bindViews();
        mTitleView = findViewById(R.id.authenticate_kyc_face_title);
        mPicImage = findViewById(R.id.authenticate_kyc_face_pic);
        mStatusImage = findViewById(R.id.authenticate_kyc_face_status);

        mPersonalFaceAuthenticateUtils = new PersonalFaceAuthenticateUtils(mKycDocumentsActivity, false, new PersonalFaceAuthenticateUtils.Callback() {
            @Override
            public void onUploadSuccess(SaveLivenessResponseModel data, byte[] detectImage) {
                refreshView(detectImage);
                mCallback.onSuccess(data, detectImage);
            }

            @Override
            public void onUploadFailed() {
                mStatusImage.setImageResource(R.drawable.user_authenticate_status_failed);
                mCallback.onFailed();
            }
        });
    }

    @Override
    protected void bindListeners() {
        super.bindListeners();
        mPicImage.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                mPersonalFaceAuthenticateUtils.startTakeSelfie();
            }
        });
    }


    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        return mPersonalFaceAuthenticateUtils.onActivityResult(requestCode, resultCode, data);
    }


    public void refreshView(byte[] detectImage) {
        //静默检测框内图片
        BitmapFactory.Options options = new BitmapFactory.Options();
        options.inPreferredConfig = Bitmap.Config.ARGB_8888;
        Bitmap bitmap = BitmapFactory.decodeByteArray(detectImage, 0, detectImage.length, options);
        mPicImage.setImageBitmap(bitmap);
        mPicImage.setEnabled(false);
        mStatusImage.setImageResource(R.drawable.user_authenticate_status_successed);
    }


    public void restartVerify() {
        mPicImage.setEnabled(true);
        mStatusImage.setImageResource(R.drawable.user_authenticate_status_default);
    }


    public interface Callback {
        void onSuccess(SaveLivenessResponseModel data, byte[] detectImage);

        void onFailed();
    }
}

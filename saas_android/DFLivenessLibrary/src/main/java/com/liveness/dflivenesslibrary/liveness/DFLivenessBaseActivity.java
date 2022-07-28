package com.liveness.dflivenesslibrary.liveness;

import android.content.Intent;
import android.os.Bundle;
import android.os.Environment;

import com.deepfinch.liveness.DFLivenessSDK;
import com.liveness.dflivenesslibrary.DFAcitivityBase;
import com.liveness.dflivenesslibrary.DFProductResult;
import com.liveness.dflivenesslibrary.DFTransferResultInterface;
import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.callback.DFLivenessResultCallback;
import com.liveness.dflivenesslibrary.fragment.DFLivenessBaseFragment;
import com.liveness.dflivenesslibrary.fragment.DFProductFragmentBase;
import com.liveness.dflivenesslibrary.liveness.util.LivenessUtils;
import com.liveness.dflivenesslibrary.net.DFNetworkUtil;
import com.liveness.dflivenesslibrary.view.DFLivenessLoadingDialogFragment;

import java.io.File;
import java.util.concurrent.Callable;
import java.util.concurrent.ExecutionException;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;
import java.util.concurrent.Future;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public class DFLivenessBaseActivity extends DFAcitivityBase implements DFLivenessResultCallback {
    private static final String TAG = "LivenessActivity";

    /**
     * Error loading library file
     */
    public static final int RESULT_CREATE_HANDLE_ERROR = 1001;

    /**
     * Internal error
     */
    public static final int RESULT_INTERNAL_ERROR = 3;

    /**
     * Package name binding error
     */
    public static final int RESULT_SDK_INIT_FAIL_APPLICATION_ID_ERROR = 4;

    /**
     * License expired
     */
    public static final int RESULT_SDK_INIT_FAIL_OUT_OF_DATE = 5;

    /**
     * The file path where the result is saved is passed in
     */
    public static String EXTRA_RESULT_PATH = "com.deepfinch.liveness.resultPath";

    /**
     * The sequence of action motion
     */
    public static final String EXTRA_MOTION_SEQUENCE = "com.deepfinch.liveness.motionSequence";

    /**
     *  output type
     */
    public static final String OUTTYPE = "outType";

    /**
     * Sets whether to return picture results or not
     */
    public static final String KEY_DETECT_IMAGE_RESULT = "key_detect_image_result";

    /**
     * Sets whether to return the video result, and only the video mode returns
     */
    public static final String KEY_DETECT_VIDEO_RESULT = "key_detect_video_result";

    public static final String KEY_HINT_MESSAGE_HAS_FACE = "com.deepfinch.liveness.message.hasface";
    public static final String KEY_HINT_MESSAGE_NO_FACE = "com.deepfinch.liveness.message.noface";
    public static final String KEY_HINT_MESSAGE_FACE_NOT_VALID = "com.deepfinch.liveness.message.facenotvalid";

    public static final String KEY_HINT_MESSAGE_NOTE_BLINK = "com.deepfinch.liveness.message.note.blink";
    public static final String KEY_HINT_MESSAGE_NOTE_MOUTH = "com.deepfinch.liveness.message.note.mouth";
    public static final String KEY_HINT_MESSAGE_NOTE_NOD = "com.deepfinch.liveness.message.note.nod";
    public static final String KEY_HINT_MESSAGE_NOTE_YAW = "com.deepfinch.liveness.message.note.yaw";
    public static final String KEY_HINT_MESSAGE_NOTE_HOLD_STILL = "com.deepfinch.liveness.message.note.hold.still";

    public static final String LIVENESS_FILE_NAME = "livenessResult";
    public static final String LIVENESS_VIDEO_NAME = "livenessVideoResult.mp4";

    protected ExecutorService mThreadPool;
    private Future<DFNetworkUtil.DFNetResult> mLivenessFuture;
    protected DFProductResult mProductResult;

    private DFLivenessLoadingDialogFragment mProgressDialog;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        Bundle bundle = getIntent().getExtras();
        EXTRA_RESULT_PATH = bundle.getString(EXTRA_RESULT_PATH);

        if (EXTRA_RESULT_PATH == null) {
            EXTRA_RESULT_PATH = Environment
                    .getExternalStorageDirectory().getAbsolutePath()
                    + File.separator
                    + "liveness" + File.separator;
        }
        File livenessFolder = new File(EXTRA_RESULT_PATH);
        if (!livenessFolder.exists()) {
            livenessFolder.mkdirs();
        }
    }

    @Override
    protected DFProductFragmentBase getFrament() {
        return new DFLivenessBaseFragment();
    }

    @Override
    protected int getTitleString() {
        return R.string.string_liveness_base;
    }

    @Override
    public void saveFinalEncrytFile(byte[] livenessEncryptResult, DFLivenessSDK.DFLivenessImageResult[] imageResult) {
        showProgressDialog();
        mProductResult = getDetectResult(livenessEncryptResult, imageResult);
        initThreadPool();
        antiHack();
        getNetworkResult();
    }

    protected DFProductResult getDetectResult(byte[] livenessEncryptResult, DFLivenessSDK.DFLivenessImageResult[] imageResult) {
        DFProductResult result = new DFProductResult();
        boolean isReturnImage = getIntent().getBooleanExtra(KEY_DETECT_IMAGE_RESULT, false);
        if (isReturnImage) {
            result.setLivenessImageResults(imageResult);
        }
        if (livenessEncryptResult != null) {
            result.setLivenessEncryptResult(livenessEncryptResult);
        }
        return result;
    }

    private void showProgressDialog() {
        initProgressDialog();
        if (!mProgressDialog.isAdded()) {
            mProgressDialog.show(getFragmentManager(), "DFLivenessLoadingDialogFragment");
        }
    }

    private void hideProgressDialog() {
        if (mProgressDialog != null && mProgressDialog.isAdded()) {
            mProgressDialog.dismissAllowingStateLoss();
        }
    }

    private void initProgressDialog() {
        if (mProgressDialog == null) {
            mProgressDialog = DFLivenessLoadingDialogFragment.getInstance();
        }
    }

    private void antiHack() {
        mLivenessFuture = mThreadPool.submit(new Callable<DFNetworkUtil.DFNetResult>() {
            @Override
            public DFNetworkUtil.DFNetResult call() throws Exception {
                return networkProcess();
            }
        });
    }

    public DFNetworkUtil.DFNetResult networkProcess() {
        return DFNetworkUtil.doAntiHack(mProductResult.getLivenessEncryptResult());
    }

    protected void getNetworkResult() {
        new Thread(new Runnable() {
            @Override
            public void run() {
                try {
                    final DFNetworkUtil.DFNetResult result = mLivenessFuture.get();
                    mProductResult.setAntiHackPass(result.mNetworkResultStatus);
                    hideProgressDialog();
                    returnDetectResult();
                } catch (ExecutionException e) {
                    e.printStackTrace();
                } catch (InterruptedException e) {
                    e.printStackTrace();
                }
            }
        }).start();

    }

    protected void returnDetectResult() {
        Intent intent = new Intent();
        ((DFTransferResultInterface) getApplication()).setResult(mProductResult);
        setResult(RESULT_OK, intent);
        finish();
    }

    @Override
    public void deleteLivenessFiles() {
        LivenessUtils.deleteFiles(EXTRA_RESULT_PATH);
    }

    @Override
    public void saveFile(byte[] livenessEncryptResult) {
        LivenessUtils.saveFile(livenessEncryptResult, EXTRA_RESULT_PATH, LIVENESS_FILE_NAME);
    }

    private void initThreadPool() {
        if (mThreadPool == null) {
            mThreadPool = Executors.newFixedThreadPool(3);
        }
    }
}

package com.deepfinch.kyclib.base;

import android.app.Activity;
import android.app.DialogFragment;
import android.app.Fragment;
import android.app.FragmentManager;
import android.app.FragmentTransaction;
import android.content.Intent;
import android.os.Bundle;
import android.support.annotation.IdRes;
import android.support.annotation.Nullable;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.Window;
import android.widget.Toast;

import com.deepfinch.kyc.jni.DFSendUIDResult;
import com.deepfinch.kyc.jni.DFUIDResult;
import com.deepfinch.kyclib.R;
import com.deepfinch.kyclib.listener.DFFragmentArgumentListener;
import com.deepfinch.kyclib.listener.DFKYCListener;
import com.deepfinch.kyclib.presenter.DFKYCSDKPresenter;
import com.deepfinch.kyclib.presenter.model.DFProcessErrorCode;
import com.deepfinch.kyclib.presenter.model.DFProcessStepModel;
import com.deepfinch.kyclib.utils.KYCStatusBarCompat;
import com.deepfinch.kyclib.view.DFLoadingDialogFragment;
import com.deepfinch.kyclib.view.DFMessageFragment;
import com.deepfinch.kyclib.view.model.DFMessageFragmentModel;

/**
 * Copyright (c) 2019-2020 DeepFinch Corporation. All rights reserved.
 */

public abstract class DFKYCBaseFragment extends Fragment implements DFKYCSDKPresenter.DFKYCSDKView, DFFragmentArgumentListener {
    private static final String TAG = "DFKYCBaseFragment";
    private static final String KEY_PROCESS_DATA_MODEL = "key_process_data_model";

    protected View mRooterView;

    protected DFKYCListener<DFProcessStepModel> mKYCProcessListener;

    private DFLoadingDialogFragment mLoadingDialogFragment;

    @Nullable
    @Override
    public View onCreateView(LayoutInflater inflater, @Nullable ViewGroup container, Bundle savedInstanceState) {
        mRooterView = inflater.inflate(getRooterLayoutRes(), null);
        Window window = getActivity().getWindow();
        KYCStatusBarCompat.translucentStatusBar(window, false);
        return mRooterView;
    }

    protected abstract int getRooterLayoutRes();

    protected void setResult(int resultCode, Intent data) {
        Activity activity = getActivity();
        if (activity != null) {
            activity.setResult(resultCode, data);
        }
    }

    protected void showLoadingDialog() {
        if (mLoadingDialogFragment == null) {
            mLoadingDialogFragment = DFLoadingDialogFragment.getInstance();
        }
        if (!mLoadingDialogFragment.isShowing()) {
            showDialogFragment(mLoadingDialogFragment);
        }
    }

    protected void hideLoadingDialog() {
        hideDialogFragment(mLoadingDialogFragment);
    }

    protected void showDialogFragment(DialogFragment dialogFragment) {
        if (dialogFragment != null) {
            dialogFragment.show(getFragmentManager(), dialogFragment.getClass().getSimpleName());
        }
    }

    protected void hideDialogFragment(DialogFragment dialogFragment) {
        if (dialogFragment != null) {
            dialogFragment.dismissAllowingStateLoss();
        }
    }

    protected void replaceFragment(int containerViewId, Fragment fragment) {
        FragmentManager fragmentManager = getFragmentManager();
        FragmentTransaction fragmentTransaction = fragmentManager.beginTransaction();
        fragmentTransaction.replace(containerViewId, fragment);
        fragmentTransaction.commit();
    }

    protected void removeFragment(Fragment fragment) {
        FragmentManager fragmentManager = getFragmentManager();
        FragmentTransaction fragmentTransaction = fragmentManager.beginTransaction();
        fragmentTransaction.remove(fragment);
        fragmentTransaction.commit();
    }

    protected void finishActivity() {
        Activity activity = getActivity();
        if (activity != null) {
            activity.finish();
        }
    }

    public void hideView() {

    }


    public View getRooterView() {
        return mRooterView;
    }

    public <T extends View> T findViewById(@IdRes int id) {
        return mRooterView.findViewById(id);
    }

    protected void runOnUiThread(Runnable runnable) {
        Activity activity = getActivity();
        if (activity != null) {
            activity.runOnUiThread(runnable);
        }
    }

    @Override
    public void returnRequestPermission(boolean success) {

    }

    @Override
    public void returnUIDResult(DFSendUIDResult sendUIDResult) {

    }

    @Override
    public void returnGetUIDResult(DFUIDResult uidResult) {

    }

    @Override
    public void createFinish(int result) {

    }

    public void setKYCProcessListener(DFKYCListener<DFProcessStepModel> kycProcessListener) {
        this.mKYCProcessListener = kycProcessListener;
    }

    @Override
    public void setInputArguments(DFProcessStepModel processDataModel) {
        Bundle bundle = new Bundle();
        if (processDataModel != null) {
            bundle.putSerializable(KEY_PROCESS_DATA_MODEL, processDataModel);
        }
        setArguments(bundle);
    }

    private DFProcessStepModel mProcessDataModel;

    protected DFProcessStepModel getProcessDataModel() {
        if (mProcessDataModel == null) {
            Bundle bundle = getArguments();
            if (bundle != null) {
                mProcessDataModel = (DFProcessStepModel) bundle.getSerializable(KEY_PROCESS_DATA_MODEL);
            }
        }
        return mProcessDataModel;
    }

    protected void showToast(String message) {
        Toast.makeText(getActivity(), message, Toast.LENGTH_SHORT).show();
    }

    protected void showErrorView(int errorCode) {
        DFProcessErrorCode processError = DFProcessErrorCode.getProcessError(errorCode);
        int errorTitleId = R.string.kyc_aadhaar_number_error;
        int errorContentId = R.string.kyc_aadhaar_number_error_content;
        if (processError != null) {
            errorTitleId = processError.getErrorTitle();
            errorContentId = processError.getErrorContent();
            DFMessageFragmentModel messageFragmentModel = new DFMessageFragmentModel();
            messageFragmentModel.setHintTitle(getString(errorTitleId));
            messageFragmentModel.setHintContent(getString(errorContentId));

            DFMessageFragment messageFragment = DFMessageFragment.getInstance(messageFragmentModel);
            messageFragment.showMessage(getFragmentManager());
        }
    }
}

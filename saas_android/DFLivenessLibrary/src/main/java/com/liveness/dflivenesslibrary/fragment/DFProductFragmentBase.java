package com.liveness.dflivenesslibrary.fragment;

import android.app.Activity;
import android.app.Fragment;
import android.os.Bundle;
import android.util.Log;
import android.view.LayoutInflater;
import android.view.SurfaceView;
import android.view.View;
import android.view.ViewGroup;
import android.widget.TextView;
import android.widget.Toast;

import com.liveness.dflivenesslibrary.DFAcitivityBase;
import com.liveness.dflivenesslibrary.R;
import com.liveness.dflivenesslibrary.camera.CameraBase;
import com.liveness.dflivenesslibrary.utils.DFViewShowUtils;
import com.liveness.dflivenesslibrary.view.DFLivenessOverlayView;

/**
 * Copyright (c) 2017-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public abstract class DFProductFragmentBase extends Fragment {

    protected SurfaceView mSurfaceView;
    protected DFLivenessOverlayView mOverlayView;
    protected View mRootView;
    protected CameraBase mCameraBase;

    @Override
    public View onCreateView(LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        mRootView = inflater.inflate(getLayoutResourceId(), container,
                false);
        Log.e("releaseReSource", "_+_+_+_+_+_+_+_+_+onCreateView");
        mSurfaceView = (SurfaceView) mRootView.findViewById(R.id.surfaceViewCamera);
        mOverlayView = (DFLivenessOverlayView) mRootView.findViewById(R.id.id_ov_mask);
        initCamera();
        initialize();
        return mRootView;
    }

    protected void initialize() {

    }

    private void initCamera() {
        if (mSurfaceView != null) {
            mCameraBase = new CameraBase(getActivity(), mSurfaceView, mOverlayView, isFrontCamera());
        }
    }

    protected void initTitle(){
        String title = getActivity().getIntent().getStringExtra(DFAcitivityBase.KEY_ACTIVITY_TITLE);
        View backTitle = mRootView.findViewById(R.id.id_ll_back);
        backTitle.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                finishActivity();
            }
        });
        refreshTitle(true, title);
    }

    protected void refreshTitle(boolean showBackTitle, String title){
        View backTitle = mRootView.findViewById(R.id.id_ll_back);
        DFViewShowUtils.refreshVisibility(backTitle, showBackTitle);
        ((TextView) mRootView.findViewById(R.id.id_tv_title)).setText(title);
    }

    protected void showToast(int showHintResId) {
        String showHint = getActivity().getString(showHintResId);
        Toast.makeText(getActivity(), showHint, Toast.LENGTH_SHORT).show();
    }

    protected boolean isFrontCamera() {
        return true;
    }

    protected abstract int getLayoutResourceId();

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        if (mOverlayView != null) {
            mOverlayView.releaseReSource();
        }
    }

    protected void finishActivity(){
        Activity activity = getActivity();
        if (activity != null){
            activity.finish();
        }
    }
}

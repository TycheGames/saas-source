package com.bigshark.android.display;

import android.app.Activity;
import android.content.ComponentName;
import android.content.Context;
import android.content.Intent;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.support.annotation.NonNull;
import android.support.annotation.Nullable;
import android.support.annotation.StringRes;
import android.support.v4.app.Fragment;
import android.support.v7.app.AlertDialog;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.view.inputmethod.InputMethodManager;
import android.widget.Toast;

import com.bigshark.android.core.common.event.NetWorkCancelEvent;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;

import de.greenrobot.event.EventBus;
import me.jessyan.autosize.internal.CustomAdapt;

/**
 * Fragment的基类
 *
 * @author Administrator
 */
public abstract class DisplayBaseFragment extends Fragment implements IDisplay.FDisplay, CustomAdapt {

    protected View fragmentRoot;
    protected Activity attachedActivity;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, @Nullable ViewGroup container, @Nullable Bundle savedInstanceState) {
        fragmentRoot = inflater.inflate(getLayoutId(), container, false);

        bindViews(savedInstanceState);
        bindListeners();
        setupDatas();

        return fragmentRoot;
    }

    protected abstract int getLayoutId();

    protected abstract void bindViews(Bundle savedInstanceState);

    protected void bindListeners() {
    }

    protected abstract void setupDatas();

    @Override
    public void onAttach(Context context) {
        super.onAttach(context);
        this.attachedActivity = (Activity) context;
    }

    //<editor-fold desc="display">

    @Override
    public IDisplay display() {
        return this;
    }

    @Override
    public Context context() {
        return getActivity();
    }

    @Override
    public Activity act() {
        return getActivity();
    }

    @Override
    public Intent getIntent() {
        return getActivity().getIntent();
    }

    @Override
    public ComponentName startService(Intent service) {
        return getActivity().startService(service);
    }

    @Override
    public void showToast(@StringRes int messageResId) {
        showToast(getString(messageResId));
    }

    @Override
    public void showToast(String message) {
        if (!StringUtil.isBlank(message) && !ViewUtil.isFinishedForDisplay(this)) {
            Toast.makeText(context(), message, Toast.LENGTH_SHORT).show();
        }
    }

    @Override
    public void showTipDialog(String message) {
        if (StringUtil.isBlank(message) || ViewUtil.isFinishedForDisplay(this)) {
            return;
        }
        new AlertDialog.Builder(act())
                .setTitle("Tips")
                .setMessage(message)
                .setPositiveButton("OK, I GOT IT !", null)
                .show();
    }

    //</editor-fold>


    //<editor-fold desc="handler">

    private Handler mainHandler;

    @Override
    public Handler getMainHandler() {
        if (mainHandler == null) {
            mainHandler = new Handler(Looper.getMainLooper());
        }
        return mainHandler;
    }

    private void clearMainHandler() {
        if (mainHandler == null) {
            return;
        }
        mainHandler.removeCallbacksAndMessages(null);
        mainHandler = null;
    }

    //</editor-fold>


    //<editor-fold desc="life cycle">

    @Override
    public void onShow() {
    }

    @Override
    public void onResume() {
        super.onResume();
    }

    @Override
    public void onPause() {
        InputMethodManager imm = (InputMethodManager) act().getSystemService(Activity.INPUT_METHOD_SERVICE);
        if (imm != null && act().getCurrentFocus() != null) {
            imm.hideSoftInputFromWindow(act().getCurrentFocus().getWindowToken(), InputMethodManager.HIDE_NOT_ALWAYS);
        }
        super.onPause();
    }

    @Override
    public void onDestroyView() {
        clearMainHandler();
        JumpOperationHandler.detached(this);

        EventBus.getDefault().post(new NetWorkCancelEvent(this));
        HttpSender.cancel(this);

        super.onDestroyView();
    }

    @Override
    public void onActivityResult(int requestCode, int resultCode, Intent data) {
        if (JumpOperationHandler.onActivityResult(display(), requestCode, resultCode, data)) {
            return;
        }
        super.onActivityResult(requestCode, resultCode, data);
    }
    //</editor-fold>


    @Override
    public boolean isBaseOnWidth() {
        return true;
    }

    @Override
    public float getSizeInDp() {
        return 375;
    }

}

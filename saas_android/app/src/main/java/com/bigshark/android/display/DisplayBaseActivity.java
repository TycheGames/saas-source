package com.bigshark.android.display;

import android.app.Activity;
import android.content.Context;
import android.content.Intent;
import android.os.Build;
import android.os.Bundle;
import android.os.Handler;
import android.os.Looper;
import android.support.annotation.NonNull;
import android.support.annotation.StringRes;
import android.support.v4.content.ContextCompat;
import android.support.v7.app.AlertDialog;
import android.support.v7.app.AppCompatActivity;
import android.view.Window;
import android.view.inputmethod.InputMethodManager;

import com.bigshark.android.core.AppManager;
import com.bigshark.android.core.common.event.NetWorkCancelEvent;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.CustomToastUtils;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.core.utils.ViewUtil;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.jump.JumpOperationHandler;
import com.umeng.analytics.MobclickAgent;

import de.greenrobot.event.EventBus;
import me.jessyan.autosize.internal.CustomAdapt;

/**
 * activity的基类
 *
 * @author Administrator
 */
public abstract class DisplayBaseActivity extends AppCompatActivity implements IDisplay.ADisplay, CustomAdapt {

    /**
     * 设置是否添加到Activity管理列表，以免启动页等弹出Dialog
     */
    public boolean canAddToDisaplays = true;

    //<editor-fold desc="init">

    @Override
    protected void onSaveInstanceState(Bundle outState) {
        super.onSaveInstanceState(outState);

        outState.putParcelable("android:support:fragments", null);
    }

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        // 防止Activity被回收后，重新启动时，将fragment再次启动造成的空指针bug
        if (savedInstanceState != null) {
            savedInstanceState.putParcelable("android:support:fragments", null);
        }

        super.onCreate(savedInstanceState);
        AppManager.getInstance().addActivity(this);

        // 虚拟导航栏 背景色
        if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.LOLLIPOP) {
            getWindow().setNavigationBarColor(ContextCompat.getColor(this, android.R.color.black));
        }
        supportRequestWindowFeature(Window.FEATURE_NO_TITLE);

        setContentView(getLayoutId());

        bindViews(savedInstanceState);
        bindListeners(savedInstanceState);
        setupDatas();
    }

    protected abstract int getLayoutId();

    public abstract void bindViews(Bundle savedInstanceState);

    public abstract void bindListeners(Bundle savedInstanceState);

    public abstract void setupDatas();

    //</editor-fold>

    //<editor-fold desc="handler">

    private Handler uiHandler;

    @Override
    public Handler getMainHandler() {
        if (uiHandler == null) {
            uiHandler = new Handler(Looper.getMainLooper());
        }
        return uiHandler;
    }

    private void clearUiHandler() {
        if (uiHandler == null) {
            return;
        }
        uiHandler.removeCallbacksAndMessages(null);
        uiHandler = null;
    }

    //</editor-fold>


    //<editor-fold desc="life cycle">

    @Override
    protected void onResume() {
        super.onResume();
        if (getJumpByPushIfNeed()) {
            JumpOperationHandler.jumpByPushIfNeed(this);
        }
        MobclickAgent.onResume(this);
    }

    /**
     * 设置是否执行Push/OpenApp 唤醒某个页面的操作，引导页、闪屏页、登录页、注册页 不需要执行唤醒
     */
    public boolean getJumpByPushIfNeed() {
        return true;
    }

    @Override
    protected void onPause() {
        InputMethodManager imm = (InputMethodManager) getSystemService(INPUT_METHOD_SERVICE);
        if (imm != null && getCurrentFocus() != null) {
            imm.hideSoftInputFromWindow(getCurrentFocus().getWindowToken(), InputMethodManager.HIDE_NOT_ALWAYS);
        }
        super.onPause();
        MobclickAgent.onPause(this);
    }

    @Override
    protected void onDestroy() {
        clearUiHandler();
        JumpOperationHandler.detached(display());

        EventBus.getDefault().post(new NetWorkCancelEvent(display()));
        HttpSender.cancel(display());

        AppManager.getInstance().removeActivity(this);
        super.onDestroy();
    }

    @Override
    protected void onActivityResult(int requestCode, int resultCode, Intent data) {
        JumpOperationHandler.onActivityResult(display(), requestCode, resultCode, data);
        super.onActivityResult(requestCode, resultCode, data);
    }

    @Override
    public void onRequestPermissionsResult(int requestCode, @NonNull String[] permissions, @NonNull int[] grantResults) {
        super.onRequestPermissionsResult(requestCode, permissions, grantResults);
        JumpOperationHandler.onRequestPermissionsResult(display(), requestCode, permissions, grantResults);
    }

    //</editor-fold>


    //<editor-fold desc="display">

    @Override
    public IDisplay display() {
        return this;
    }

    @Override
    public Context context() {
        return this;
    }

    @Override
    public Activity act() {
        return this;
    }

    @Override
    public void showToast(@StringRes int messageResId) {
        showToast(getString(messageResId));
    }

    @Override
    public void showToast(String message) {
        if (!StringUtil.isBlank(message) && !ViewUtil.isFinishedForDisplay(this)) {
            CustomToastUtils.showToast(this, message, isShowCenterForToast());
        }
    }

    protected boolean isShowCenterForToast() {
        return false;
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

    //<editor-fold desc="autosize">

    @Override
    public boolean isBaseOnWidth() {
        return true;
    }

    @Override
    public float getSizeInDp() {
        return 375;
    }

    //</editor-fold>
}

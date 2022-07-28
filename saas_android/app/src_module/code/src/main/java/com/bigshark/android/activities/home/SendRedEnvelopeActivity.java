package com.bigshark.android.activities.home;

import android.os.Bundle;

import com.bigshark.android.R;
import com.gyf.immersionbar.ImmersionBar;


/**
 * 发红包页面
 */
public class SendRedEnvelopeActivity  extends com.bigshark.android.display.DisplayBaseActivity{

    @Override
    protected int getLayoutId() {
        return R.layout.activity_send_red_envelope;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
//设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {

    }

    @Override
    public void setupDatas() {

    }
}

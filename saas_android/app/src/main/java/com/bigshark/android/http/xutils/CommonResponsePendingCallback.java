package com.bigshark.android.http.xutils;

import android.support.annotation.NonNull;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.core.utils.LoadingDialogUtils;

/**
 * 封装了loading弹框的逻辑
 */
public abstract class CommonResponsePendingCallback<T> extends CommonResponseCallback<T> {


    public CommonResponsePendingCallback(@NonNull IDisplay display) {
        super(display);
    }

    @Override
    public void handleUi(boolean isStart) {
        if (isStart) {
            LoadingDialogUtils.showLoadingDialog(display.act());
        } else {
            LoadingDialogUtils.hideLoadingDialog();
        }
    }

}

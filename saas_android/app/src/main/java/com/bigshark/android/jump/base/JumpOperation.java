package com.bigshark.android.jump.base;

import android.content.Intent;
import android.support.annotation.NonNull;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.JumpOperationRequest;
import com.bigshark.android.utils.StringConstant;

/**
 * @author Administrator
 * @date 2017/7/13
 */
public abstract class JumpOperation<Data extends JumpModel> {

    public JumpOperationRequest<Data> request;

    public JumpOperation() {
    }


    public void setRequest(JumpOperationRequest<Data> request) {
        this.request = request;
    }

    public abstract void start();

    @NonNull
    public String path() {
        final String path = request.getData().getPath();
        if (path == null) {
            return StringConstant.JUMP_OPERATION_BINDER_UNKNOWN;
        }
        return path;
    }

    /**
     * 处理activity与fragment的onActivityResult
     *
     * @return 是否处理了该回调
     */
    public boolean onActivityResult(int requestCode, int resultCode, Intent data) {
        return false;
    }

    /**
     * 处理activity与fragment的onRequestPermissionsResult
     *
     * @return 是否处理了该回调
     */
    public boolean onRequestPermissionsResult(int requestCode, String[] permissions, int[] grantResults) {
        return false;
    }


}

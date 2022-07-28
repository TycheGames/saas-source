package com.bigshark.android.jump.operations.uis;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.uis.ToastJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * Toast提示
 */
public class ToastJumpOperation extends JumpOperation<ToastJumpModel> {

    static {
        JumpOperationBinder.bind(
                ToastJumpOperation.class,
                ToastJumpModel.class,
                // Toast提示
                StringConstant.JUMP_TIP_TOAST
        );
    }

    @Override
    public void start() {
        request.showToast(request.getData().getText());
    }
}

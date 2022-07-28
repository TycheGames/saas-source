package com.bigshark.android.jump.operations.main;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

/**
 * 返回原生的功能
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class DisplayCloseJumpOperation extends JumpOperation<JumpModel> {
    static {
        JumpOperationBinder.bind(
                DisplayCloseJumpOperation.class,
                JumpModel.class,
                StringConstant.JUMP_APP_BACK
        );
    }

    @Override
    public void start() {
        request.activity().finish();
    }
}

package com.bigshark.android.jump.operations.main;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.services.ServiceUtils;
import com.bigshark.android.utils.StringConstant;

/**
 * 上报数据
 */
public class UploadAuthDataJumpOperation extends JumpOperation<JumpModel> {
    static {
        JumpOperationBinder.bind(
                UploadAuthDataJumpOperation.class,
                JumpModel.class,
                StringConstant.JUMP_APP_UPLOAD_DATA
        );
    }

    @Override
    public void start() {
        ServiceUtils.reportServiceDatas(request.getDisplay(), false);
    }

}

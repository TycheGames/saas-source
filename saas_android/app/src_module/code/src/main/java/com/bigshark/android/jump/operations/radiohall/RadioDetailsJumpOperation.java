package com.bigshark.android.jump.operations.radiohall;


import android.content.Intent;

import com.bigshark.android.activities.radiohall.RadioDetailsActivity;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.radiohall.RadioHallJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到RadioHall相关界面的功能
 *
 * @author JayChang
 * @date 2020/1/7 11:58
 */
public class RadioDetailsJumpOperation extends JumpOperation<RadioHallJumpModel> {
    static {
        JumpOperationBinder.bind(
                RadioDetailsJumpOperation.class,
                RadioHallJumpModel.class,
                //广播详情
                StringConstant.JUMP_RADIO_HALL_RADIO_DETAILS
        );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), RadioDetailsActivity.class));
    }

}

package com.bigshark.android.jump.operations.messagecenter;


import android.content.Intent;

import com.bigshark.android.activities.messagecenter.EvaluationNotificationListActivity;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.messagecenter.MessageCenterJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到MessageCenter相关界面的功能
 *
 * @author JayChang
 * @date 2020/1/7 11:58
 */
public class EvaluateNotifyJumpOperation extends JumpOperation<MessageCenterJumpModel> {
    static {
        JumpOperationBinder.bind(
                EvaluateNotifyJumpOperation.class,
                MessageCenterJumpModel.class,
                // 评价通知
                StringConstant.JUMP_MESSAGE_CENTER_EVALUATE_NOTIFY
        );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), EvaluationNotificationListActivity.class));
    }

}

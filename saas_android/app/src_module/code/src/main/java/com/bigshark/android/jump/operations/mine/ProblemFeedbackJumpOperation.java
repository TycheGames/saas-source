package com.bigshark.android.jump.operations.mine;


import android.content.Intent;

import com.bigshark.android.activities.mine.ProblemFeedbackActivity;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.mine.MineJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到Mine相关界面的功能
 *
 * @author JayChang
 * @date 2020/1/7 11:58
 */
public class ProblemFeedbackJumpOperation extends JumpOperation<MineJumpModel> {
    static {
        JumpOperationBinder.bind(
                ProblemFeedbackJumpOperation.class,
                MineJumpModel.class,
                //问题反馈
                StringConstant.JUMP_MINE_PROBLEM_FEEDBACK
        );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), ProblemFeedbackActivity.class));
    }

}

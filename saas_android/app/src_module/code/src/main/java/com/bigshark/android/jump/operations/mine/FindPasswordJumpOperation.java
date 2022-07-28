package com.bigshark.android.jump.operations.mine;


import android.content.Intent;

import com.bigshark.android.activities.mine.FindPasswordActivity;
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
public class FindPasswordJumpOperation extends JumpOperation<MineJumpModel> {
    static {
        JumpOperationBinder.bind(
                FindPasswordJumpOperation.class,
                MineJumpModel.class,
                // 找回密码
                StringConstant.JUMP_MINE_FIND_PASSWORD
                );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), FindPasswordActivity.class));
    }
}

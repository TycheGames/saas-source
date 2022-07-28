package com.bigshark.android.jump.operations.mine;


import android.content.Intent;

import com.bigshark.android.activities.mine.ChangePasswordActivity;
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
public class ChangePasswordJumpOperation extends JumpOperation<MineJumpModel> {
    static {
        JumpOperationBinder.bind(
                ChangePasswordJumpOperation.class,
                MineJumpModel.class,
                // 修改密码
                StringConstant.JUMP_MINE_CHANGE_PASSWORD
        );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), ChangePasswordActivity.class));
    }

}

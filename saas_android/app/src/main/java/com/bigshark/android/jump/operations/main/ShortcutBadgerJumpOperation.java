package com.bigshark.android.jump.operations.main;

import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.model.main.ShortcutBadgerJumpModel;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.utils.StringConstant;

import me.leolin.shortcutbadger.ShortcutBadger;

/**
 * 跳转到网页的
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class ShortcutBadgerJumpOperation extends JumpOperation<ShortcutBadgerJumpModel> {
    static {
        JumpOperationBinder.bind(
                ShortcutBadgerJumpOperation.class,
                ShortcutBadgerJumpModel.class,
                StringConstant.JUMP_APP_SHORTCUT_BADGER
        );
    }

    @Override
    public void start() {
        ShortcutBadger.applyCount(request.getDisplay().act(), request.getData().getBadgeCount());
    }


}

package com.bigshark.android.jump.operations.uis;

import android.view.View;

import com.bigshark.android.dialog.CommonJumpDialog;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.JumpOperationHandler;
import com.bigshark.android.jump.model.uis.DialogDataModel;
import com.bigshark.android.jump.model.uis.DialogJumpModel;
import com.bigshark.android.core.utils.StringUtil;
import com.bigshark.android.utils.StringConstant;

/**
 * Dialog提示
 */
public class TextDialogJumpOperation extends JumpOperation<DialogJumpModel> {

    static {
        JumpOperationBinder.bind(
                TextDialogJumpOperation.class,
                DialogJumpModel.class,
                // Dialog提示
                StringConstant.JUMP_TIP_DIALOG
        );
    }

    @Override
    public void start() {
        showDialog();
    }

    private void showDialog() {
        final DialogDataModel data = request.getData().getDialog();
        new CommonJumpDialog(request.getDisplay(), data.isBtnClose())
                .setTitle(data.getTitle())
                .setContent(data.getText())
                .setCancleText(data.getLeftBtn()).setOkText(data.getRightBtn())
                .setCancleBtnClick(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        if (!StringUtil.isBlank(data.getLeftJump())) {
                            JumpOperationHandler.convert(data.getLeftJump()).createRequest().setDisplay(request.getDisplay()).jump();
                        }
                        if (request.getData().isFinishPage()) {
                            request.getDisplay().act().finish();
                        }
                    }
                })
                .setOkClick(new View.OnClickListener() {
                    @Override
                    public void onClick(View view) {
                        if (!StringUtil.isBlank(data.getRightJump())) {
                            JumpOperationHandler.convert(data.getRightJump()).createRequest().setDisplay(request.getDisplay()).jump();
                        }
                        if (request.getData().isFinishPage()) {
                            request.getDisplay().act().finish();
                        }
                    }
                }).show();
    }
}

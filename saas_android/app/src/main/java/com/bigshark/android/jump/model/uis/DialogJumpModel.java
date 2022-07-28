package com.bigshark.android.jump.model.uis;


import com.bigshark.android.jump.base.JumpModel;

/**
 * Created by User on 2018/3/22.
 * 跳转 Dialog 提示
 */

public class DialogJumpModel extends JumpModel {

    private DialogDataModel dialog;

    public DialogDataModel getDialog() {
        return dialog;
    }

    public void setDialog(DialogDataModel dialog) {
        this.dialog = dialog;
    }
}

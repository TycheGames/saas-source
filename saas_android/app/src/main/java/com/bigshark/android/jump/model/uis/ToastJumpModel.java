package com.bigshark.android.jump.model.uis;


import com.bigshark.android.jump.base.JumpModel;

/**
 * Created by User on 2018/3/22.
 * 跳转 Toast提示
 */

public class ToastJumpModel extends JumpModel {
    /**
     * 提示信息
     */
    private String text;

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }
}

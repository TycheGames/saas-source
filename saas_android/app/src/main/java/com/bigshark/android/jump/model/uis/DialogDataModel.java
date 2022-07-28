package com.bigshark.android.jump.model.uis;

/**
 * Created by User on 2018/3/22.
 * dialog 提示数据
 */

public class DialogDataModel {
    /**
     * 标题
     */
    private String title;
    /**
     * 提示内容
     */
    private String text;
    /**
     * 左侧按钮
     */
    private String leftBtn;
    /**
     * 右侧按钮
     */
    private String rightBtn;
    /**
     * 左侧按钮跳转
     */
    private String leftJump;
    /**
     * 左侧按钮跳转
     */
    private String rightJump;

    /**
     * 按钮关闭
     */
    private boolean isBtnClose;

    public String getTitle() {
        return title;
    }

    public void setTitle(String title) {
        this.title = title;
    }

    public String getText() {
        return text;
    }

    public void setText(String text) {
        this.text = text;
    }

    public String getLeftBtn() {
        return leftBtn;
    }

    public void setLeftBtn(String leftBtn) {
        this.leftBtn = leftBtn;
    }

    public String getRightBtn() {
        return rightBtn;
    }

    public void setRightBtn(String rightBtn) {
        this.rightBtn = rightBtn;
    }

    public String getLeftJump() {
        return leftJump;
    }

    public void setLeftJump(String leftJump) {
        this.leftJump = leftJump;
    }

    public String getRightJump() {
        return rightJump;
    }

    public void setRightJump(String rightJump) {
        this.rightJump = rightJump;
    }

    public boolean isBtnClose() {
        return isBtnClose;
    }

    public void setBtnClose(boolean btnClose) {
        isBtnClose = btnClose;
    }
}

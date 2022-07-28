package com.bigshark.android.jump.model.main;


import com.bigshark.android.jump.base.JumpModel;

/**
 * @author Administrator
 * @date 2017/12/20
 */
public class AuthJumpModel extends JumpModel {

    private int totalNum;//总个数

    private int currentPosition;//当前位置

    private boolean isCheck;//是否选中

    public int getTotalNum() {
        return totalNum;
    }

    public void setTotalNum(int totalNum) {
        this.totalNum = totalNum;
    }

    public int getCurrentPosition() {
        return currentPosition;
    }

    public void setCurrentPosition(int currentPosition) {
        this.currentPosition = currentPosition;
    }

    public boolean isCheck() {
        return isCheck;
    }

    public void setCheck(boolean check) {
        isCheck = check;
    }
}

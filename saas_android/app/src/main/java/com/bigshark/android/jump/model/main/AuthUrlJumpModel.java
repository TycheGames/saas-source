package com.bigshark.android.jump.model.main;


import com.bigshark.android.jump.base.JumpModel;

/**
 * @author Administrator
 * @date 2017/12/20
 */
public class AuthUrlJumpModel extends JumpModel {
    private String url;
    private int totalNum;//总个数
    private int currentPosition;//当前位置
    private boolean isCheck;//是否选中

    public String getUrl() {
        return url;
    }

    public void setUrl(String url) {
        this.url = url;
    }

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

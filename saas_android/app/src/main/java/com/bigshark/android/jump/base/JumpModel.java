package com.bigshark.android.jump.base;


import com.bigshark.android.jump.JumpOperationRequest;

/**
 * @author Administrator
 * @date 2017/12/20
 */
public class JumpModel {
    /**
     * 新版本的指令类型：用于替换type
     * 通过命名即可知道指令的功能
     */
    private String path = null;

    private boolean isFinishPage = false;

    public String getPath() {
        return path;
    }

    public void setPath(String path) {
        this.path = path;
    }

    public boolean isFinishPage() {
        return isFinishPage;
    }

    public void setFinishPage(boolean finishPage) {
        isFinishPage = finishPage;
    }

    public JumpOperationRequest createRequest() {
        return new JumpOperationRequest<>(this);
    }


}

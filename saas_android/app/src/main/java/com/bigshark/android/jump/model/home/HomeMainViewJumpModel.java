package com.bigshark.android.jump.model.home;


import com.bigshark.android.jump.base.JumpModel;

/**
 * @author Administrator
 * @date 2017/12/20
 */
public class HomeMainViewJumpModel extends JumpModel {
    /**
     * 首页tab的tag，可以根据该tag值，跳转到目标tab页面，防止多个weex页面或h5页面造成只能跳转到第一个页面的问题
     */
    private int tag = -1;

    public int getTag() {
        return tag;
    }

    public void setTag(int tag) {
        this.tag = tag;
    }
}


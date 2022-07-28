package com.bigshark.android.jump.operations.home;


import android.content.Intent;

import com.bigshark.android.events.BaseDisplayEventModel;
import com.bigshark.android.events.TabEventModel;
import com.bigshark.android.events.RefreshDisplayEventModel;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.home.HomeMainViewJumpModel;
import com.bigshark.android.activities.home.MainActivity;
import com.bigshark.android.utils.StringConstant;

import de.greenrobot.event.EventBus;

/**
 * 跳转到主界面的功能
 *
 * @author Administrator
 * @date 2017/7/17
 */
public class HomeMainViewJumpOperation extends JumpOperation<HomeMainViewJumpModel> {
    static {
        JumpOperationBinder.bind(
                HomeMainViewJumpOperation.class,
                HomeMainViewJumpModel.class,
                // 返回首页
                StringConstant.JUMP_MAIN_VIEW_HOME,
                // 跳转更多
                StringConstant.JUMP_MAIN_VIEW_TAB,
                //刷新 tabList
                StringConstant.JUMP_MAIN_VIEW_REFRESH_TABLIST
        );
    }

    @Override
    public void start() {
        switch (path()) {
            // 返回首页
            case StringConstant.JUMP_MAIN_VIEW_HOME:
                gotoMain();
                break;
            // 跳转更多
            case StringConstant.JUMP_MAIN_VIEW_TAB:
                gotoMain();
                EventBus.getDefault().post(new TabEventModel(request.getData().getTag()));
                break;
            // 刷新TabList
            case StringConstant.JUMP_MAIN_VIEW_REFRESH_TABLIST:
                EventBus.getDefault().post(new RefreshDisplayEventModel(BaseDisplayEventModel.EVENT_REFRESH_MAIN_TAB_LIST));
                break;
            default:
                break;
        }
    }

    private void gotoMain() {
        Intent intent = new Intent(request.activity(), MainActivity.class);
        intent.addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP | Intent.FLAG_ACTIVITY_NEW_TASK);
        request.startActivity(intent);
    }

}

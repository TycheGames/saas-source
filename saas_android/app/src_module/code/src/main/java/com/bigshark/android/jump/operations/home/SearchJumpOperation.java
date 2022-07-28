package com.bigshark.android.jump.operations.home;


import android.content.Intent;

import com.bigshark.android.activities.home.SearchActivity;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.home.HomeJumpModel;
import com.bigshark.android.utils.StringConstant;

/**
 * 跳转到home相关界面的功能
 *
 * @author JayChang
 * @date 2020/1/7 11:58
 */
public class SearchJumpOperation extends JumpOperation<HomeJumpModel> {
    static {
        JumpOperationBinder.bind(
                SearchJumpOperation.class,
                HomeJumpModel.class,
                //搜索
                StringConstant.JUMP_HOME_SEARCH
        );
    }

    @Override
    public void start() {
        request.startActivity(new Intent(request.activity(), SearchActivity.class));
    }

}

package com.bigshark.android.vh.main.tab;

import android.support.annotation.IdRes;
import android.support.design.widget.TabLayout;
import android.support.v4.app.FragmentManager;

import com.bigshark.android.R;
import com.bigshark.android.activities.home.MainActivity;
import com.bigshark.android.events.TabEventModel;
import com.bigshark.android.http.model.app.MainTabItemResponseModel;
import com.bigshark.android.utils.StringConstant;

import java.util.ArrayList;
import java.util.List;

/**
 * tabs 帮助类
 */
public class MainTabUtils {
    private final MainFragmentUtils mainFragmentUtils;
    private MainTabLayoutUtils tabLayoutUtils;

    private List<MainTabItemResponseModel> bottomTabDatas = new ArrayList<>();
    private int currentDisplayTabTag = StringConstant.MAIN_FRAGMENT_TAB_TYPE_MAIN;// 当前tab的tag

    private MainActivity mMainActivity;

    public MainTabUtils(MainActivity activity, FragmentManager fragmentManager, @IdRes final int fragmentContainerId, TabLayout tabLayout) {
        this.mMainActivity = activity;

        this.mainFragmentUtils = new MainFragmentUtils(fragmentManager);

        this.tabLayoutUtils = new MainTabLayoutUtils(mMainActivity, tabLayout, new MainTabLayoutUtils.Callback() {
            @Override
            public boolean needIntercept(MainTabItemViewHolder tabVh, int position) {
                return false;
            }

            @Override
            public void onTabSelected(MainTabItemViewHolder tabVh, int position) {
                MainTabItemResponseModel tabItemData = tabVh.getData();
                mainFragmentUtils.switchCurrentFragment(tabItemData.getTag(), fragmentContainerId, tabItemData);
                currentDisplayTabTag = tabItemData.getTag();
            }
        });


        // initTabbarList
        List<MainTabItemResponseModel> mainTabItemResponseModels = new ArrayList<>(1);

        MainTabItemResponseModel mainTabItemResponseModel = new MainTabItemResponseModel();
        mainTabItemResponseModel.setTag(StringConstant.MAIN_FRAGMENT_TAB_TYPE_MAIN);
        mainTabItemResponseModel.setTitle("Loan");
        mainTabItemResponseModel.setUnSelectIcon(R.drawable.main_tab_home_default_icon);
        mainTabItemResponseModel.setSelectIcon(R.drawable.main_tab_home_target_icon);
        mainTabItemResponseModels.add(mainTabItemResponseModel);

        mainFragmentUtils.switchCurrentFragment(mainTabItemResponseModel.getTag(), fragmentContainerId, mainTabItemResponseModel);
        addDisplayTabList(mainTabItemResponseModels, mainTabItemResponseModel.getTag());
    }

    /**
     * @param useCurrentTag 是否使用当前显示的tag，还是使用第一个tab的tag
     */
    public void addDisplayTabList(List<MainTabItemResponseModel> tabItemResponseModels, boolean useCurrentTag) {
        // 显示网络请求数据中给定的tag页面
        if (!useCurrentTag) {
            addDisplayTabList(tabItemResponseModels, getMainDisplayTabTag(tabItemResponseModels));
            return;
        }

        boolean isContainsCurrentDisplayTab = false;
        for (MainTabItemResponseModel itemBean : tabItemResponseModels) {
            if (itemBean.getTag() == currentDisplayTabTag) {
                isContainsCurrentDisplayTab = true;
                break;
            }
        }

        if (isContainsCurrentDisplayTab) {
            addDisplayTabList(tabItemResponseModels, currentDisplayTabTag);
        } else {
            addDisplayTabList(tabItemResponseModels, getMainDisplayTabTag(tabItemResponseModels));
        }
    }

    private int getMainDisplayTabTag(List<MainTabItemResponseModel> mainTabItemResponseModels) {
        int mainTabTag = mainTabItemResponseModels.get(0).getTag();
        for (MainTabItemResponseModel mainTabItemResponseModel : mainTabItemResponseModels) {
            if (mainTabItemResponseModel.isDefaultShow()) {
                mainTabTag = mainTabItemResponseModel.getTag();
                break;
            }
        }
        return mainTabTag;
    }

    /**
     * 添加 tabBar
     */
    private void addDisplayTabList(List<MainTabItemResponseModel> mainTabItemResponseModels, int mainTabId) {
        bottomTabDatas.clear();
        bottomTabDatas.addAll(mainTabItemResponseModels);
        currentDisplayTabTag = mainTabId;

        tabLayoutUtils.clear();
        boolean hasMainTab = false;
        for (int i = 0; i < bottomTabDatas.size(); i++) {
            MainTabItemResponseModel tabItemData = bottomTabDatas.get(i);
            boolean isSelected = tabItemData.getTag() == currentDisplayTabTag;
            if (isSelected) {
                hasMainTab = true;
            }
            tabLayoutUtils.addTab(tabItemData, isSelected);
        }

        // 下发 新tabBar 没有当前显示的tag
        if (!hasMainTab) {
            // 默认显示第一个 tabBar 对应的 tag
            tabLayoutUtils.setSelectedTab(0);
        }

        tabLayoutUtils.resetRedPoint();

        mainFragmentUtils.removeUnusedFragments(mainTabItemResponseModels);
    }


    public void changeCurrentDisplayTab(TabEventModel event) {
        for (int tabIndex = 0; tabIndex < bottomTabDatas.size(); tabIndex++) {
            MainTabItemResponseModel tabModel = bottomTabDatas.get(tabIndex);
            if (tabModel.getTag() == event.getTag()) {
                tabLayoutUtils.setSelectedTab(tabIndex);
                break;
            }
        }
    }


    public void onDestroy() {
        mainFragmentUtils.clear();
    }


}

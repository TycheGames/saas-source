package com.bigshark.android.vh.main.tab;

import android.support.annotation.NonNull;
import android.support.design.widget.TabLayout;

import com.bigshark.android.activities.home.MainActivity;
import com.bigshark.android.http.model.app.MainTabItemResponseModel;

/**
 * @author Administrator
 * @date 2018/9/3
 * <p>
 * Description
 */
public class MainTabLayoutUtils {
    private MainActivity mMainActivity;
    private TabLayout tabLayout;
    private Callback callback;

    public MainTabLayoutUtils(MainActivity activity, final TabLayout tabLayout, @NonNull final Callback callback) {
        this.mMainActivity = activity;
        this.tabLayout = tabLayout;
        this.callback = callback;
        tabLayout.addOnTabSelectedListener(new TabLayout.OnTabSelectedListener() {
            @Override
            public void onTabSelected(TabLayout.Tab tab) {
                noticeTabChange(tab);
                if (tab.getCustomView() != null) {
                    MainTabItemViewHolder tabCell = (MainTabItemViewHolder) tab.getTag();
                    callback.onTabSelected(tabCell, tab.getPosition());
                }
            }

            @Override
            public void onTabUnselected(TabLayout.Tab tab) {
                noticeTabChange(tab);
            }

            @Override
            public void onTabReselected(TabLayout.Tab tab) {
                noticeTabChange(tab);
            }
        });
    }

    private void noticeTabChange(TabLayout.Tab tab) {
        if (tab != null && tab.getCustomView() != null && tab.getTag() != null && tab.getTag() instanceof MainTabItemViewHolder) {
            MainTabItemViewHolder tabCellVh = (MainTabItemViewHolder) tab.getTag();
            tabCellVh.refreshBySelectState(tab.getCustomView().isSelected());
        }
    }


    public void setSelectedTab(int position) {
        TabLayout.Tab tab = tabLayout.getTabAt(position);
        if (tab != null) {
            tab.select();
        }
    }


    public void resetRedPoint() {
        TabLayout.Tab tab = tabLayout.getTabAt(tabLayout.getSelectedTabPosition());
        if (tab == null) {
            return;
        }
        MainTabItemViewHolder tabCellVh = (MainTabItemViewHolder) tab.getTag();
        if (tabCellVh != null) {
            tabCellVh.resetRedPointTip();
        }
    }


    public void addTab(@NonNull final MainTabItemResponseModel itemBean, boolean isSelected) {
        MainTabItemViewHolder tabHolder = new MainTabItemViewHolder(mMainActivity, tabLayout.getTabCount(), new MainTabItemViewHolder.Callback() {
            @Override
            public boolean intercept(MainTabItemViewHolder tabVh, int position) {
                return callback.needIntercept(tabVh, position);
            }
        });
        tabHolder.bindViewData(itemBean);

        TabLayout.Tab tabCall = tabLayout.newTab();
        tabCall.setTag(tabHolder);
        tabCall.setCustomView(tabHolder.getRoot());

        // 已选中的会执行onTabSelected 方法
        if (!isSelected) {
            tabHolder.refreshBySelectState(false);
        }
        tabLayout.addTab(tabCall, isSelected);
    }


    public void clear() {
        tabLayout.removeAllTabs();
    }


    public void setCallback(Callback callback) {
        this.callback = callback;
    }


    public interface Callback {
        /**
         * 点击拦截事件 登录判断
         */
        boolean needIntercept(MainTabItemViewHolder tabVh, int position);

        /**
         * tabBar 选中事件
         */
        void onTabSelected(MainTabItemViewHolder tabVh, int position);

    }
}

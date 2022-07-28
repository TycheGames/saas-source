package com.bigshark.android.vh.main.tab;

import android.support.annotation.IdRes;
import android.support.annotation.IntDef;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentTransaction;

import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.fragments.home.BrowserFragment;
import com.bigshark.android.fragments.home.HomeMenUserFragment;
import com.bigshark.android.fragments.home.HomeWomenUserFragment;
import com.bigshark.android.fragments.home.MainFragment;
import com.bigshark.android.fragments.messagecenter.MessageCenterFragment;
import com.bigshark.android.fragments.messagecenter.MyMessageListFragment;
import com.bigshark.android.fragments.mine.HomeFragment;
import com.bigshark.android.fragments.radiohall.RadioDetailCommentsListFragment;
import com.bigshark.android.fragments.radiohall.RadioDetailPraiseListFragment;
import com.bigshark.android.fragments.radiohall.RadioHallFragment;
import com.bigshark.android.http.model.app.MainTabItemResponseModel;
import com.bigshark.android.utils.StringConstant;
import com.socks.library.KLog;

import java.lang.annotation.Retention;
import java.lang.annotation.RetentionPolicy;
import java.util.ArrayList;
import java.util.HashMap;
import java.util.List;
import java.util.Map;
import java.util.Set;

public class MainFragmentUtils {

    private static Fragment lastShowFragment;

    private final FragmentManager fragmentManager;
    private Map<Integer, Fragment> fragmentCache = new HashMap<>(4);
    @MainTabType
    private int tabType = StringConstant.MAIN_FRAGMENT_TAB_TYPE_MAIN;

    public MainFragmentUtils(FragmentManager manager) {
        this.fragmentManager = manager;
    }

    public void switchCurrentFragment(@MainTabType int tabType, @IdRes int frameLayoutId, MainTabItemResponseModel data) {
        this.tabType = tabType;
        FragmentTransaction transaction = fragmentManager.beginTransaction();

        if (lastShowFragment != null) {
            transaction.hide(lastShowFragment);
        }

        Fragment currentFragment;
        if (fragmentCache.containsKey(tabType)) {
            currentFragment = fragmentCache.get(tabType);
            transaction.show(currentFragment);
        } else {
            currentFragment = createFragment(tabType, data);
            transaction.add(frameLayoutId, currentFragment);
            fragmentCache.put(tabType, currentFragment);
        }

        transaction.commitAllowingStateLoss();

        lastShowFragment = currentFragment;
        if (lastShowFragment instanceof IDisplay.FDisplay) {
            ((IDisplay.FDisplay) lastShowFragment).onShow();
        }
    }

    private Fragment createFragment(int tabTag, MainTabItemResponseModel data) {
        switch (tabTag) {
            case StringConstant.MAIN_FRAGMENT_TAB_TYPE_MAIN:
                return new MainFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_HOME_MEN_USER:
                return new HomeMenUserFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_HOME_WOMEN_USER:
                return new HomeWomenUserFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_MESSAGE_CENTER:
                return new MessageCenterFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_MESSAGE_LIST:
                return new MyMessageListFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_MINE_HOME:
                return new HomeFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_RADIO_DETAIL_COMMENTS:
                return new RadioDetailCommentsListFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_RADIO_DETAIL_PRAISE:
                return new RadioDetailPraiseListFragment();
            case StringConstant.MAIN_FRAGMENT_TAB_RADIO_HALL:
                return new RadioHallFragment();
        }

        // TODO 其他代码

        if (isWebBrowserTab(tabTag)) {
            BrowserFragment browserFragment = new BrowserFragment();
            browserFragment.setFragmentUrl(data.getUrl());
            return browserFragment;
        }

        BrowserFragment fragment = new BrowserFragment();
        fragment.setFragmentUrl(data.getUrl());
        return fragment;
    }

    // h5页面
    public static boolean isWebBrowserTab(@MainTabType int tabId) {
        return 2000 <= tabId && tabId < 3000;
    }


    @MainTabType
    public int getCurrentTabId() {
        return tabType;
    }

    public void clear() {
        fragmentCache.clear();
        lastShowFragment = null;
    }


    /**
     * 删除掉不再被使用的fragment
     */
    public void removeUnusedFragments(List<MainTabItemResponseModel> currTabBeans) {
        Set<Integer> cacheTags = fragmentCache.keySet();
        List<Integer> currentTags = new ArrayList<>(currTabBeans.size());
        for (MainTabItemResponseModel currTabBean : currTabBeans) {
            currentTags.add(currTabBean.getTag());
        }
        // 删除后就是未使用的fragment的tag
        List<Integer> unusedTags = new ArrayList<>(cacheTags);
        unusedTags.removeAll(currentTags);

        KLog.d("cacheTags:" + cacheTags + ", currentTags:" + currentTags + ", unusedTags:" + unusedTags);

        if (unusedTags.isEmpty()) {
            return;
        }
        FragmentTransaction transaction = fragmentManager.beginTransaction();
        for (Integer unusedTag : unusedTags) {
            Fragment unusedFragment = fragmentCache.remove(unusedTag);
            transaction.remove(unusedFragment);
        }
        transaction.commitAllowingStateLoss();
    }


    @IntDef({
            StringConstant.MAIN_FRAGMENT_TAB_TYPE_MAIN,
            StringConstant.MAIN_FRAGMENT_TAB_HOME_MEN_USER,
            StringConstant.MAIN_FRAGMENT_TAB_HOME_WOMEN_USER,
            StringConstant.MAIN_FRAGMENT_TAB_MESSAGE_CENTER,
            StringConstant.MAIN_FRAGMENT_TAB_MESSAGE_LIST,
            StringConstant.MAIN_FRAGMENT_TAB_MINE_HOME,
            StringConstant.MAIN_FRAGMENT_TAB_RADIO_DETAIL_COMMENTS,
            StringConstant.MAIN_FRAGMENT_TAB_RADIO_DETAIL_PRAISE,
            StringConstant.MAIN_FRAGMENT_TAB_RADIO_HALL,
    })
    @Retention(RetentionPolicy.SOURCE)
    public @interface MainTabType {
    }

}




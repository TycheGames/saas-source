package com.bigshark.android.fragments.home;

import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;

import com.bigshark.android.R;
import com.bigshark.android.display.DisplayBaseFragment;
import com.flyco.tablayout.SlidingTabLayout;

import java.util.ArrayList;

/**
 * 男性首页
 */
public class HomeMenUserFragment extends DisplayBaseFragment {

    private ViewPager mViewPager;
    private SlidingTabLayout mTab_layout_home;
    private ArrayList<Fragment> mMenFragments = new ArrayList<>();
    private String[] mMenTitles = {"Ordinary men", "VIP men"};
    private int[] mMenType = {1, 2};
    private MyPagerAdapter mAdapter;

    private static HomeMenUserFragment sFragment;

    public static HomeMenUserFragment getInstance() {
        if (sFragment == null) {
            sFragment = new HomeMenUserFragment();
        }
        return sFragment;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_home_women_user;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        mViewPager = fragmentRoot.findViewById(R.id.vp_homepager);
        mTab_layout_home = fragmentRoot.findViewById(R.id.tab_layout_home);
//        for (int type : mMenType) {
//            mMenFragments.add(HomeRecommendListFragment.getInstance(type, 1));
//        }
        mAdapter = new MyPagerAdapter(getFragmentManager());
        mViewPager.setAdapter(mAdapter);
        mViewPager.setOffscreenPageLimit(1);
        mTab_layout_home.setViewPager(mViewPager, mMenTitles, getActivity(), mMenFragments);
        mViewPager.setCurrentItem(0);
    }

    @Override
    public void onHiddenChanged(boolean hidden) {
        super.onHiddenChanged(hidden);
        if (hidden) {

        } else {

        }
    }

    @Override
    protected void bindListeners() {

    }

    @Override
    protected void setupDatas() {

    }

    private class MyPagerAdapter extends FragmentPagerAdapter {

        public MyPagerAdapter(FragmentManager fm) {
            super(fm);
        }

        @Override
        public int getCount() {
            return mMenFragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return mMenTitles[position];
        }

        @Override
        public Fragment getItem(int position) {
            return mMenFragments.get(position);
        }

    }
}

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
 * 女性首页
 */
public class HomeWomenUserFragment extends DisplayBaseFragment {

    private ViewPager mViewPager;
    private SlidingTabLayout mTab_layout_home;

    private ArrayList<Fragment> mWomenFragments = new ArrayList<>();
    private String[] mWomenTitles = {"nearby", "New coming", "Certification"};
    private int[] mWomenType = {1, 2, 3};

    private MyPagerAdapter mAdapter;

    private static HomeWomenUserFragment sFragment;


    public static HomeWomenUserFragment getInstance() {
        if (sFragment == null) {
            sFragment = new HomeWomenUserFragment();
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
//        for (int type : mWomenType) {
//            mWomenFragments.add(HomeRecommendListFragment.getInstance(type, 2));
//        }
        mAdapter = new MyPagerAdapter(getFragmentManager());
        mViewPager.setAdapter(mAdapter);
        mViewPager.setOffscreenPageLimit(2);
        mTab_layout_home.setViewPager(mViewPager, mWomenTitles, getActivity(), mWomenFragments);
        mViewPager.setCurrentItem(0);
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
            return mWomenFragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return mWomenTitles[position];
        }

        @Override
        public Fragment getItem(int position) {
            return mWomenFragments.get(position);
        }

    }
}

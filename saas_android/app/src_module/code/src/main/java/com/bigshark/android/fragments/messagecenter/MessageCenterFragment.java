package com.bigshark.android.fragments.messagecenter;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.View;
import android.widget.ImageView;

import com.bigshark.android.R;
import com.bigshark.android.activities.messagecenter.PushSettingsActivity;
import com.bigshark.android.display.DisplayBaseFragment;
import com.flyco.tablayout.SlidingTabLayout;

import java.util.ArrayList;

//import com.netease.nim.uikit.business.recent.RecentContactsFragment;

/**
 * 消息中心 fragment
 */
public class MessageCenterFragment extends DisplayBaseFragment {

    private ArrayList<Fragment> mFragments = new ArrayList<>();
    private String[] mTitles = {"chat", "message"};
    private String mTitle;
    private ImageView iv_message_setting;
    private ViewPager mViewPager;
    private SlidingTabLayout mSlidingTabLayout;
    private MyPagerAdapter mAdapter;

    public static MessageCenterFragment getInstance(String title) {
        MessageCenterFragment sf = new MessageCenterFragment();
        sf.mTitle = title;
        return sf;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_message_center;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        iv_message_setting = fragmentRoot.findViewById(R.id.iv_message_setting);
        mViewPager = fragmentRoot.findViewById(R.id.vp_message);
        mSlidingTabLayout = fragmentRoot.findViewById(R.id.tab_layout_message);
//        mFragments.add(new RecentContactsFragment());
        mFragments.add(new MyMessageListFragment());
        mAdapter = new MyPagerAdapter(getFragmentManager());
        mViewPager.setAdapter(mAdapter);
        mSlidingTabLayout.setViewPager(mViewPager, mTitles, getActivity(), mFragments);
        mViewPager.setCurrentItem(0);
    }

    @Override
    protected void setupDatas() {

    }

    @Override
    protected void bindListeners() {
        iv_message_setting.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                startActivity(new Intent(act(), PushSettingsActivity.class));
            }
        });
    }

    private class MyPagerAdapter extends FragmentPagerAdapter {

        public MyPagerAdapter(FragmentManager fm) {
            super(fm);
        }

        @Override
        public int getCount() {
            return mFragments.size();
        }

        @Override
        public CharSequence getPageTitle(int position) {
            return mTitles[position];
        }

        @Override
        public Fragment getItem(int position) {
            return mFragments.get(position);
        }

    }

    @Override
    public void onHiddenChanged(boolean hidden) {
        super.onHiddenChanged(hidden);
        if (hidden) {// // 不在最前端显示 相当于调用了onPause();

        } else {// 在最前端显示 相当于调用了onResume();

        }
    }


}

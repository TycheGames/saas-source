package com.bigshark.android.fragments.mine;


import android.content.Intent;
import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v4.app.FragmentManager;
import android.support.v4.app.FragmentPagerAdapter;
import android.support.v4.view.ViewPager;
import android.view.Gravity;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.activities.home.SearchActivity;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.events.TargetJumpEvent;
import com.bigshark.android.http.model.app.ProvinceModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.listener.OnSelectCityListener;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.mmkv.MmkvGroup;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.widget.popupwindow.ChoiceCityPopup;
import com.flyco.tablayout.SlidingTabLayout;

import java.util.ArrayList;
import java.util.List;

import de.greenrobot.event.EventBus;

/**
 * 首页 fragment
 */
public class HomeFragment extends DisplayBaseFragment implements View.OnClickListener {


    private TextView mTv_home_titlebar_region;
    private ChoiceCityPopup mChoiceCityPopup;
    private CheckBox mCb_home_titlebar_switch_gender;
    private LinearLayout ll_men, ll_women;

    private ViewPager vp_homepager_women;
    private SlidingTabLayout tab_layout_home_women;
    private ArrayList<Fragment> mWomenFragments = new ArrayList<>();
    private String[] mWomenTitles = {"nearby", "New coming", "Certification"};
    private int[] mWomenType = {1, 2, 3};
    private MyWomenPagerAdapter mWomenAdapter;

    private ViewPager vp_homepager_men;
    private SlidingTabLayout tab_layout_home_men;
    private ArrayList<Fragment> mMenFragments = new ArrayList<>();
    private String[] mMenTitles = {"Ordinary men", "VIP men"};
    private int[] mMenType = {1, 2};
    private MyMenPagerAdapter mMenAdapter;

    private String mTitle;
    private int mSex;

    private View rootview;

    public static HomeFragment getInstance(int title) {
        HomeFragment sf = new HomeFragment();
        sf.mSex = title;
        return sf;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_home;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        rootview = LayoutInflater.from(getContext()).inflate(R.layout.fragment_home, null);
        ImageView iv_home_titlebar_search = fragmentRoot.findViewById(R.id.iv_home_titlebar_search);
        mTv_home_titlebar_region = fragmentRoot.findViewById(R.id.tv_home_titlebar_region);
        mCb_home_titlebar_switch_gender = fragmentRoot.findViewById(R.id.cb_home_titlebar_switch_gender);
        iv_home_titlebar_search.setOnClickListener(this);
        mTv_home_titlebar_region.setOnClickListener(this);
        mCb_home_titlebar_switch_gender.setOnClickListener(this);
        ll_men = fragmentRoot.findViewById(R.id.ll_men);
        ll_women = fragmentRoot.findViewById(R.id.ll_women);

        initWomenViewPager();
        initMenViewPager();
        getCityJson();
        if (1 == mSex) {
            mCb_home_titlebar_switch_gender.setChecked(true);
            ll_men.setVisibility(View.GONE);
            ll_women.setVisibility(View.VISIBLE);
        } else {
            mCb_home_titlebar_switch_gender.setChecked(false);
            ll_men.setVisibility(View.VISIBLE);
            ll_women.setVisibility(View.GONE);
        }
    }


    private void initWomenViewPager() {
        vp_homepager_women = fragmentRoot.findViewById(R.id.vp_homepager_women);
        tab_layout_home_women = fragmentRoot.findViewById(R.id.tab_layout_home_women);
//        for (int type : mWomenType) {
//            mWomenFragments.add(HomeRecommendListFragment.getInstance(type, 2));
//        }
        mWomenAdapter = new MyWomenPagerAdapter(getFragmentManager());
        vp_homepager_women.setAdapter(mWomenAdapter);
        vp_homepager_women.setOffscreenPageLimit(2);
        tab_layout_home_women.setViewPager(vp_homepager_women, mWomenTitles, getActivity(), mWomenFragments);
        vp_homepager_women.setCurrentItem(0);
    }

    private void initMenViewPager() {
        vp_homepager_men = fragmentRoot.findViewById(R.id.vp_homepager_men);
        tab_layout_home_men = fragmentRoot.findViewById(R.id.tab_layout_home_men);
//        for (int type : mMenType) {
//            mMenFragments.add(HomeRecommendListFragment.getInstance(type, 1));
//        }
        mMenAdapter = new MyMenPagerAdapter(getFragmentManager());
        vp_homepager_men.setAdapter(mMenAdapter);
        vp_homepager_men.setOffscreenPageLimit(1);
        tab_layout_home_men.setViewPager(vp_homepager_men, mMenTitles, getActivity(), mMenFragments);
        vp_homepager_men.setCurrentItem(0);
    }

    /**
     * 请求城市列表
     */
    private void getCityJson() {
//        HttpApi.app().getRegion(this, new BaseRequestBean(), new HttpCallback<List<ProvinceModel>>() {
//            @Override
//            public void onSuccess(int code, String message, List<ProvinceModel> data) {
//                if (data != null) {
//                    //保存
//                    MmkvGroup.global().setCityJson(JSON.toJSONString(data));
//                    mChoiceCityPopup = new ChoiceCityPopup(act(), data);
//                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
//                        @Override
//                        public void onConfirmClick(String name) {
//                            EventBus.getDefault().post(new TargetJumpEvent(TargetJumpEvent.EVENT_REFRESH_INDEX_CITY, name));
//                            mTv_home_titlebar_region.setText(name);
//                            mChoiceCityPopup.dismiss();
//
//                        }
//
//                        @Override
//                        public void onUnlimitedClick(String str) {
//                            EventBus.getDefault().post(new TargetJumpEvent(TargetJumpEvent.EVENT_REFRESH_INDEX_CITY, ""));
//                            mTv_home_titlebar_region.setText(str);
//                            mChoiceCityPopup.dismiss();
//                        }
//                    });
//                }
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//
//            }
//        });
        HttpSender.post(new CommonResponseCallback<List<ProvinceModel>>(display()) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_GETREGION_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(List<ProvinceModel> data, int resultCode, String resultMessage) {
                if (data != null) {
                    //保存
                    MmkvGroup.global().setCityJson(JSON.toJSONString(data));
                    mChoiceCityPopup = new ChoiceCityPopup(act(), data);
                    mChoiceCityPopup.setOnSelectCityListener(new OnSelectCityListener() {
                        @Override
                        public void onConfirmClick(String name) {
                            EventBus.getDefault().post(new TargetJumpEvent(TargetJumpEvent.EVENT_REFRESH_INDEX_CITY, name));
                            mTv_home_titlebar_region.setText(name);
                            mChoiceCityPopup.dismiss();

                        }

                        @Override
                        public void onUnlimitedClick(String str) {
                            EventBus.getDefault().post(new TargetJumpEvent(TargetJumpEvent.EVENT_REFRESH_INDEX_CITY, ""));
                            mTv_home_titlebar_region.setText(str);
                            mChoiceCityPopup.dismiss();
                        }
                    });
                }
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
            }
        });
    }

    @Override
    protected void bindListeners() {

    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            //搜索
            case R.id.iv_home_titlebar_search:
                startActivity(new Intent(act(), SearchActivity.class));
                break;
            //选择地区
            case R.id.tv_home_titlebar_region:
                if (mChoiceCityPopup != null) {
//                    new XPopup.Builder(act()).asCustom(mChoiceCityPopup).show();
                    mChoiceCityPopup.showAtLocation(rootview, Gravity.CENTER, 0, 0);
                }
                break;
            //切换性别
            case R.id.cb_home_titlebar_switch_gender:
                EventBus.getDefault().post(new TargetJumpEvent(TargetJumpEvent.EVENT_REFRESH_INDEX_SEX, mCb_home_titlebar_switch_gender.isChecked() ? "2" : "1"));
                if (mCb_home_titlebar_switch_gender.isChecked()) {
                    ll_men.setVisibility(View.GONE);
                    ll_women.setVisibility(View.VISIBLE);
                } else {
                    ll_men.setVisibility(View.VISIBLE);
                    ll_women.setVisibility(View.GONE);
                }
                break;
            default:
                break;
        }
    }

    @Override
    protected void setupDatas() {

    }

    @Override
    public void onHiddenChanged(boolean hidden) {
        super.onHiddenChanged(hidden);
        if (hidden) {// // 不在最前端显示 相当于调用了onPause();

        } else {// 在最前端显示 相当于调用了onResume();

        }
    }

    private class MyWomenPagerAdapter extends FragmentPagerAdapter {

        public MyWomenPagerAdapter(FragmentManager fm) {
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

    private class MyMenPagerAdapter extends FragmentPagerAdapter {

        public MyMenPagerAdapter(FragmentManager fm) {
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

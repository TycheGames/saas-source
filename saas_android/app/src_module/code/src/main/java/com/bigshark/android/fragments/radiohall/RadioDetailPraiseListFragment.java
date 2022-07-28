package com.bigshark.android.fragments.radiohall;


import android.os.Bundle;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.radiohall.PraiseListAdapter;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;

import java.util.List;

/**
 * 广播详情 点赞list
 */
public class RadioDetailPraiseListFragment extends DisplayBaseFragment {


    private static RadioDetailPraiseListFragment sFragment;
    private RecyclerView mRecyclerView;
    private PraiseListAdapter mPraiseListAdapter;

    public static RadioDetailPraiseListFragment getInstance() {
        if (sFragment == null) {
            sFragment = new RadioDetailPraiseListFragment();
        }
        return sFragment;
    }

    @Override
    protected int getLayoutId() {
        return R.layout.fragment_radio_detail_comments_list;
    }

    @Override
    protected void bindViews(Bundle savedInstanceState) {
        mRecyclerView = fragmentRoot.findViewById(R.id.recycler_view);
        mRecyclerView.setLayoutManager(new LinearLayoutManager(act()));
        mPraiseListAdapter = new PraiseListAdapter(act());
        mRecyclerView.setAdapter(mPraiseListAdapter);
    }

    @Override
    protected void bindListeners() {

    }

    @Override
    protected void setupDatas() {

    }

    public void setData(List<RaidoDetailsModel.ClickGoodBean> click_good) {
        if (mPraiseListAdapter != null) {
            mPraiseListAdapter.setNewData(click_good);
        }
    }
}

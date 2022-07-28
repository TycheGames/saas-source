package com.bigshark.android.fragments.radiohall;


import android.os.Bundle;
import android.support.v4.app.Fragment;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.radiohall.CommentsListAdapter;
import com.bigshark.android.display.DisplayBaseFragment;
import com.bigshark.android.http.model.radiohall.RaidoDetailsModel;

import java.util.List;

/**
 * 广播详情 评论list
 */
public class RadioDetailCommentsListFragment extends DisplayBaseFragment {

    private static RadioDetailCommentsListFragment sFragment;
    private RecyclerView mRecyclerView;
    private CommentsListAdapter mCommentsListAdapter;

    public static Fragment getInstance() {
        if (sFragment == null) {
            sFragment = new RadioDetailCommentsListFragment();
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
        mCommentsListAdapter = new CommentsListAdapter(act());
        mRecyclerView.setAdapter(mCommentsListAdapter);

    }

    @Override
    protected void bindListeners() {

    }

    @Override
    protected void setupDatas() {

    }

    public void setData(List<RaidoDetailsModel.CommentedListBean> commented_list) {
        if (mCommentsListAdapter != null) {
            mCommentsListAdapter.setNewData(commented_list);
        }
    }
}

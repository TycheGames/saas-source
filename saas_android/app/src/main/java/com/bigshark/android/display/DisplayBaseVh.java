package com.bigshark.android.display;

import android.support.annotation.IdRes;
import android.support.annotation.LayoutRes;
import android.view.View;
import android.view.ViewGroup;

import com.bigshark.android.core.display.IDisplay;

/**
 * 替换掉BaseViewHolder
 *
 * @author Administrator
 */
public abstract class DisplayBaseVh<Root extends View, Data> {

    protected final IDisplay mDisplay;
    private Root mRoot;
    protected Data mData;

    /**
     * 有些需要延迟inflater mRoot view 的情况
     */
    public DisplayBaseVh(IDisplay display) {
        this.mDisplay = display;
    }

    public DisplayBaseVh(IDisplay display, Root root) {
        this.mDisplay = display;
        initViews(root);
    }

    protected void initViews(Root root) {
        this.mRoot = root;

        bindViews();
        bindListeners();
    }

    protected void bindViews() {
    }

    /**
     *
     */
    protected void bindListeners() {
    }

    /**
     * @param data
     */
    public void bindViewData(Data data) {
        this.mData = data;
    }

    public Root getRoot() {
        return mRoot;
    }

    public void setVisibility(int visibility) {
        mRoot.setVisibility(visibility);
    }

    protected <T extends View> T findViewById(@IdRes int id) {
        return mRoot.findViewById(id);
    }


    /**
     * 给adapter使用
     *
     * @param <T> 数据类型
     */
    public static class AdapterVh<T> extends DisplayBaseVh<View, T> {
        protected DisplayBaseAdapter<T> mAdapter;
        protected int mPosition;

        public AdapterVh(final DisplayBaseAdapter<T> mAdapter, ViewGroup parent, @LayoutRes int adapterLayoutId) {
            super(mAdapter.mDisplay, mAdapter.mLayoutInflater.inflate(adapterLayoutId, parent, false));
            this.mAdapter = mAdapter;

            if (mAdapter.getOnItemClickHandler() != null) {
                getRoot().setOnClickListener(new View.OnClickListener() {
                    @Override
                    public void onClick(View v) {
                        mAdapter.getOnItemClickHandler().selected(mData, mPosition);
                    }
                });
            }
        }

        @Override
        protected void bindListeners() {
            super.bindListeners();

        }

        public int getPosition() {
            return mPosition;
        }

        public void setPosition(int position) {
            this.mPosition = position;
        }
    }
}
package com.bigshark.android.display;

import android.support.annotation.LayoutRes;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.AdapterView;
import android.widget.BaseAdapter;

import com.bigshark.android.core.display.IDisplay;

import java.util.ArrayList;
import java.util.List;

/**
 * 替换掉MyBaseAdapter与BasePageAdapter两个adapter
 *
 * @author Administrator
 * @date 2018/1/15
 */
public abstract class DisplayBaseAdapter<T> extends BaseAdapter {
    protected final IDisplay mDisplay;
    protected final LayoutInflater mLayoutInflater;
    protected final List<T> mDatas;

    public DisplayBaseAdapter(IDisplay display) {
        this(display, new ArrayList<T>());
    }

    public DisplayBaseAdapter(IDisplay display, @NonNull List<T> datas) {
        this.mDisplay = display;
        mLayoutInflater = LayoutInflater.from(display.act());
        this.mDatas = datas;
    }

    @Override
    public int getCount() {
        return mDatas.size();
    }

    @Override
    public T getItem(int position) {
        return mDatas.get(position);
    }

    @Override
    public long getItemId(int position) {
        return position;
    }


    //****************** view holder ******************

    @Override
    public View getView(int position, View convertView, ViewGroup parent) {
        DisplayBaseVh.AdapterVh<T> viewHolder;
        T data = mDatas.get(position);
        if (convertView == null) {
            viewHolder = createViewHolder(parent, position);
            convertView = viewHolder.getRoot();
            convertView.setTag(viewHolder);
        } else {
            viewHolder = (DisplayBaseVh.AdapterVh<T>) convertView.getTag();
        }

        viewHolder.setPosition(position);
        viewHolder.bindViewData(data);
        return convertView;
    }

    /**
     * 获取ViewHolder
     *
     * @param parent 父布局
     * @return ViewHolder
     */
    protected abstract DisplayBaseVh.AdapterVh<T> createViewHolder(ViewGroup parent, int position);

    /**
     * 进一步封装减少通用代码
     */
    public abstract class Vh extends DisplayBaseVh.AdapterVh<T> {
        public Vh(ViewGroup parent, @LayoutRes int resId) {
            super(DisplayBaseAdapter.this, parent, resId);
        }
    }


    //****************** data ******************

    public void addData(T newData) {
        mDatas.add(newData);
        notifyDataSetChanged();
    }

    public void append(List<T> newDatas) {
        if (newDatas != null && !newDatas.isEmpty()) {
            mDatas.addAll(newDatas);
        }
        notifyDataSetChanged();
    }

    public void refresh(List<T> newDatas) {
        mDatas.clear();
        append(newDatas);
    }

    public boolean remove(T newData) {
        boolean success = mDatas.remove(newData);
        if (success) {
            notifyDataSetChanged();
        }
        return success;
    }

    public boolean removeAll(List<T> newDatas) {
        boolean success = mDatas.removeAll(newDatas);
        if (success) {
            notifyDataSetChanged();
        }
        return success;
    }

    public void remove(int position) {
        mDatas.remove(position);
        notifyDataSetChanged();
    }

    public List<T> getDatas() {
        return mDatas;
    }

    public void clear() {
        if (mDatas.isEmpty()) {
            return;
        }
        mDatas.clear();
        notifyDataSetChanged();
    }

    //****************** empty view ******************

    private View emptyView;

    public void setEmptyView(View emptyView) {
        this.emptyView = emptyView;
    }

    public void showEmptyIfNeed(AdapterView adapterView) {
        if (!mDatas.isEmpty()) {
            return;
        }
        if (adapterView.getEmptyView() == null) {
            adapterView.setEmptyView(emptyView);
        }
    }


    //****************** item click ******************

    protected OnItemClickHandler<T> mOnItemClickHandler;

    public void setOnItemSelectEvent(OnItemClickHandler<T> onItemClickHandler) {
        this.mOnItemClickHandler = onItemClickHandler;
    }

    public OnItemClickHandler<T> getOnItemClickHandler() {
        return mOnItemClickHandler;
    }

    public interface OnItemClickHandler<T> {
        void selected(T itemData, int posistion);
    }


}

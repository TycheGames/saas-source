package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.support.v7.widget.LinearLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.adapter.PopOneChoiceListAdapter;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.chad.library.adapter.base.BaseQuickAdapter;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 10:42
 * @描述 单选 list 应用在：约会主题
 */
public class OneChoiceListPopup extends PopupWindow {

    private List<String> mStringList;
    private String mTitle;
    private Context mContext;
    private OnConfirmClickListener mOnConfirmClickListener;
    private TextView mTv_popup_title;
    private PopOneChoiceListAdapter mAdapter;

    public OneChoiceListPopup(@NonNull Context context, String title, List<String> stringList) {
        super(context);
        this.mContext = context;
        this.mTitle = title;
        this.mStringList = stringList;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_choice_list, null);
        //设置View
        setContentView(mPopView);
        mTv_popup_title = mPopView.findViewById(R.id.tv_popup_title);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        RecyclerView recycler_view = mPopView.findViewById(R.id.recycler_view);
        iv_popup_close.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
            }
        });
        if (mTitle != null) {
            mTv_popup_title.setText(mTitle);
        }

        recycler_view.setLayoutManager(new LinearLayoutManager(mContext));
        mAdapter = new PopOneChoiceListAdapter();
        recycler_view.setAdapter(mAdapter);
        mAdapter.setNewData(mStringList);
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                String str = (String) adapter.getData().get(position);
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick(str);
                }
            }
        });
    }

    public void setListData(List<String> listData) {
        this.mStringList = listData;
        mAdapter.setNewData(listData);
    }

    public void setTitleString(String title) {
        this.mTitle = title;
        if (mTv_popup_title != null && mTitle != null) {
            mTv_popup_title.setText(mTitle);
        }
    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }

}

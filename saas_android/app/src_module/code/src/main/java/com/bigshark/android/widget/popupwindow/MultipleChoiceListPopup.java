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
import com.bigshark.android.adapters.adapter.MultipleChoiceListAdapter;
import com.bigshark.android.http.model.bean.MultipleChoiceTextModel;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.chad.library.adapter.base.BaseQuickAdapter;

import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/23 10:42
 * @描述 多选 list 应用在：约会期望
 */
public class MultipleChoiceListPopup extends PopupWindow {

    private List<MultipleChoiceTextModel> mStringList;
    private String mTitle;
    private Context mContext;
    private OnConfirmClickListener mOnConfirmClickListener;
    private TextView mTv_popup_title;
    private MultipleChoiceListAdapter mAdapter;

    public MultipleChoiceListPopup(@NonNull Context context, String title, List<MultipleChoiceTextModel> stringList) {
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
        TextView tv_confirm = mPopView.findViewById(R.id.tv_confirm);
        tv_confirm.setVisibility(View.VISIBLE);
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
        mAdapter = new MultipleChoiceListAdapter();
        recycler_view.setAdapter(mAdapter);
        mAdapter.setNewData(mStringList);
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                MultipleChoiceTextModel bean = (MultipleChoiceTextModel) adapter.getData().get(position);
                bean.setIs_selected(!bean.isIs_selected());
                mAdapter.notifyDataSetChanged();
            }
        });

        tv_confirm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                StringBuffer stringBuffer = new StringBuffer();
                List<MultipleChoiceTextModel> beanList = mAdapter.getData();
                for (MultipleChoiceTextModel bean : beanList) {
                    if (bean.isIs_selected()) {
                        stringBuffer.append(bean.getText() + "/");
                    }
                }
                if (mOnConfirmClickListener != null) {
                    if (stringBuffer.length() > 1) {
                        mOnConfirmClickListener.OnConfirmClick(stringBuffer.substring(0, stringBuffer.length() - 1));
                    } else {
                        mOnConfirmClickListener.OnConfirmClick("Multiple choice");
                    }
                }
            }
        });
    }

    public void setListData(List<MultipleChoiceTextModel> listData) {
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

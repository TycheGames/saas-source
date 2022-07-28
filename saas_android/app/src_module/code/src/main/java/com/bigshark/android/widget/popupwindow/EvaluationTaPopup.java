package com.bigshark.android.widget.popupwindow;

import android.app.Activity;
import android.content.Context;
import android.support.annotation.NonNull;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.adapters.adapter.EvaluationTaListAdapter;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.home.EvaluationItemModel;
import com.bigshark.android.http.model.user.UserInfoModel;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.ArrayList;
import java.util.List;

import static android.view.View.GONE;
import static android.view.View.VISIBLE;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/28 20:35
 * @描述 评价ta
 */
public class EvaluationTaPopup extends PopupWindow {

    private ImageView mIv_evaluation_pop_close, iv_evaluation_pop_head_portrait;
    private TextView iv_evaluation_pop_confirm, tv_title;
    private RecyclerView mRecyclerView;
    private IDisplay mDisplay;
    private EvaluationTaListAdapter mAdapter;
    private List<EvaluationItemModel> mList;
    private List<String> checkIdList = new ArrayList<>();
    private OnConfirmClickListener mOnConfirmClickListener;
    private String mUserId;
    private UserInfoModel myInfobean;
    private String mHeadImg;
    private int mSex;

    public EvaluationTaPopup(@NonNull IDisplay display, List<EvaluationItemModel> list, String userid, String headImg, int sex) {
        super(display.context());
        this.mDisplay = display;
        this.mList = list;
        this.mUserId = userid;
        this.mHeadImg = headImg;
        this.mSex = sex;
        init();
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init() {
        LayoutInflater inflater = LayoutInflater.from(mDisplay.context());
        View mPopView = inflater.inflate(R.layout.popup_evaluation_ta, null);
        //设置View
        setContentView(mPopView);
        mIv_evaluation_pop_close = mPopView.findViewById(R.id.iv_evaluation_pop_close);
        iv_evaluation_pop_head_portrait = mPopView.findViewById(R.id.iv_evaluation_pop_head_portrait);
        iv_evaluation_pop_confirm = mPopView.findViewById(R.id.iv_evaluation_pop_confirm);
        mRecyclerView = mPopView.findViewById(R.id.iv_evaluation_pop_recycler);
        tv_title = mPopView.findViewById(R.id.tv_title);
//        myInfobean = MmkvGroup.loginInfo().getUserInfo();
        if (mUserId.equals(myInfobean.getUser_id())) {
            iv_evaluation_pop_confirm.setVisibility(GONE);
            tv_title.setText("- Get real reviews -");
        } else {
            iv_evaluation_pop_confirm.setVisibility(VISIBLE);
            tv_title.setText("- Her true evaluation -");
        }
        initRecyclerView();
        initListener();
        setAvatar(mHeadImg, mSex);
    }

    private void initRecyclerView() {
        mRecyclerView.setLayoutManager(new GridLayoutManager(mDisplay.context(), 3));
        mAdapter = new EvaluationTaListAdapter(mDisplay);
        mRecyclerView.setAdapter(mAdapter);
        if (mList != null) {
            mAdapter.setNewData(mList);
        }
    }

    private void initListener() {
        mIv_evaluation_pop_close.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                dismiss();
            }
        });
        mAdapter.setOnItemClickListener(new BaseQuickAdapter.OnItemClickListener() {
            @Override
            public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                if (mUserId.equals(myInfobean.getUser_id())) {
                    return;
                }
                EvaluationItemModel itemBean = (EvaluationItemModel) adapter.getData().get(position);
                if (checkIdList.contains(itemBean.getId())) {
                    return;
                }
                checkIdList.add(itemBean.getId());
                itemBean.setCount(itemBean.getCount() + 1);
                mAdapter.notifyDataSetChanged();
            }
        });

        iv_evaluation_pop_confirm.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (checkIdList.size() > 0) {
                    if (mOnConfirmClickListener != null) {
                        mOnConfirmClickListener.OnConfirmClick(JSON.toJSONString(checkIdList));
                    }
                } else {
                    mDisplay.showToast("Please select feature evaluation");
                }
            }
        });
    }

    //头像
    public void setAvatar(String avatar, int sex) {
        if (iv_evaluation_pop_head_portrait != null) {
            if (1 == sex) {
                x.image().bind(
                        iv_evaluation_pop_head_portrait,
                        avatar,
                        new ImageOptions.Builder()
                                .setUseMemCache(true)//设置使用缓存
                                .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                                .build()
                );
            } else if (2 == sex) {
                x.image().bind(
                        iv_evaluation_pop_head_portrait,
                        avatar,
                        new ImageOptions.Builder()
                                .setUseMemCache(true)//设置使用缓存
                                .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                                .build()
                );
            }
        }
    }

    public void setData(List<EvaluationItemModel> list) {
        this.mList = list;
        if (mAdapter != null) {
            mAdapter.setNewData(mList);
        }
    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        mOnConfirmClickListener = onConfirmClickListener;
    }

}

package com.bigshark.android.widget.popupwindow;

import android.content.ClipData;
import android.content.ClipboardManager;
import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.PopupWindow;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.user.ViewUserProfileBean;
import com.bigshark.android.listener.OnConfirmClickListener;
import com.bigshark.android.utils.ToastUtil;

import static android.view.View.GONE;
import static android.view.View.VISIBLE;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/10 14:57
 * @描述 社交账号
 */
public class SocialAccountPopup extends PopupWindow implements View.OnClickListener {

    private OnConfirmClickListener mOnConfirmClickListener;
    private TextView mTv_qq_number, mTv_weixin_number;
    private LinearLayout mLl_qq;
    private LinearLayout mLl_weixin;

    public static final int TYPE_QQ = 1;
    public static final int TYPE_WEIXIN = 2;
    public static final int TYPE_ALL = 3;

    private static Context mContext;
    private int mType;
    private ViewUserProfileBean mUserProfileBean;

    public SocialAccountPopup(@NonNull Context context) {
        super(context);
        this.mContext = context;
        init(mContext);
    }

    public SocialAccountPopup(@NonNull Context context, int type, ViewUserProfileBean userProfileBean) {
        super(context);
        this.mContext = context;
        this.mType = type;
        this.mUserProfileBean = userProfileBean;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    // 执行初始化操作，比如：findView，设置点击，或者任何你弹窗内的业务逻辑
    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_social_account, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_popup_close = mPopView.findViewById(R.id.iv_popup_close);
        TextView tv_popup_confirm = mPopView.findViewById(R.id.tv_popup_confirm);
        mTv_qq_number = mPopView.findViewById(R.id.tv_qq_number);
        TextView tv_copy_qq = mPopView.findViewById(R.id.tv_copy_qq);
        mTv_weixin_number = mPopView.findViewById(R.id.tv_weixin_number);
        TextView tv_copy_weixin = mPopView.findViewById(R.id.tv_copy_weixin);
        mLl_qq = mPopView.findViewById(R.id.ll_qq);
        mLl_weixin = mPopView.findViewById(R.id.ll_weixin);
        if (mType != 0 && mUserProfileBean != null) {
            setType(mType);
        }

        iv_popup_close.setOnClickListener(this);
        tv_popup_confirm.setOnClickListener(this);
        tv_copy_qq.setOnClickListener(this);
        tv_copy_weixin.setOnClickListener(this);

    }

    public void setOnConfirmClickListener(OnConfirmClickListener onConfirmClickListener) {
        this.mOnConfirmClickListener = onConfirmClickListener;
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            case R.id.tv_popup_confirm:
                if (mOnConfirmClickListener != null) {
                    mOnConfirmClickListener.OnConfirmClick(null);
                }
                break;
            case R.id.tv_copy_qq:
                copyToClipboard(mTv_qq_number.getText().toString().trim());
                break;
            case R.id.tv_copy_weixin:
                copyToClipboard(mTv_weixin_number.getText().toString().trim());
                break;
            default:
                break;
        }
    }

    public void setType(int type) {
        if (mLl_qq == null || mLl_weixin == null) {
            return;
        }
        switch (type) {
            case TYPE_QQ:
                mLl_qq.setVisibility(VISIBLE);
                mLl_weixin.setVisibility(GONE);
                mTv_qq_number.setText(mUserProfileBean.getQq());
                break;
            case TYPE_WEIXIN:
                mLl_qq.setVisibility(GONE);
                mLl_weixin.setVisibility(VISIBLE);
                mTv_weixin_number.setText(mUserProfileBean.getWeixin());
                break;
            case TYPE_ALL:
                mLl_qq.setVisibility(VISIBLE);
                mLl_weixin.setVisibility(VISIBLE);
                mTv_qq_number.setText(mUserProfileBean.getQq());
                mTv_weixin_number.setText(mUserProfileBean.getWeixin());

                break;
            default:
                break;
        }
    }

    public void copyToClipboard(String text) {
        //获取剪贴板管理器：
        ClipboardManager cm = (ClipboardManager) mContext.getSystemService(Context.CLIPBOARD_SERVICE);
        // 创建普通字符型ClipData
        ClipData mClipData = ClipData.newPlainText("Label", text);
        // 将ClipData内容放到系统剪贴板里。
        cm.setPrimaryClip(mClipData);
        ToastUtil.showToast(mContext, "已复制");
        dismiss();
    }
}

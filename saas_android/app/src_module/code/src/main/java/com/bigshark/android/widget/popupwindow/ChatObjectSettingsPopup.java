package com.bigshark.android.widget.popupwindow;

import android.content.Context;
import android.support.annotation.NonNull;
import android.view.LayoutInflater;
import android.view.View;
import android.widget.CheckBox;
import android.widget.ImageView;
import android.widget.PopupWindow;

import com.bigshark.android.R;
//import com.netease.nim.uikit.common.ToastHelper;
//import com.netease.nimlib.sdk.NIMClient;
//import com.netease.nimlib.sdk.RequestCallback;
//import com.netease.nimlib.sdk.friend.FriendService;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/31 18:01
 * @描述 针对聊天对象的设置
 */
public class ChatObjectSettingsPopup extends PopupWindow implements View.OnClickListener {

    private CheckBox cb_turnoff_message_reminders, cb_shielding;
    private Context mContext;
    private String mAccid;

    public ChatObjectSettingsPopup(@NonNull Context context, String accid) {
        super(context);
        this.mContext = context;
        this.mAccid = accid;
        init(mContext);
        PopupWindowUtil.setPopupWindow(this);
    }

    protected void init(Context context) {
        LayoutInflater inflater = LayoutInflater.from(context);
        View mPopView = inflater.inflate(R.layout.popup_chat_object_settings, null);
        //设置View
        setContentView(mPopView);
        ImageView iv_share_close = mPopView.findViewById(R.id.iv_popup_close);
        cb_turnoff_message_reminders = mPopView.findViewById(R.id.cb_turnoff_message_reminders);
        cb_shielding = mPopView.findViewById(R.id.cb_shielding);
        iv_share_close.setOnClickListener(this);
//        cb_turnoff_message_reminders.setChecked(NIMClient.getService(FriendService.class).isNeedMessageNotify(mAccid));
//        cb_shielding.setChecked(NIMClient.getService(FriendService.class).isInBlackList(mAccid));
        //关闭消息提醒
        cb_turnoff_message_reminders.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                messageReminderSettings();
            }
        });
        //拉黑(屏蔽双方)
        cb_shielding.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                shielding();
            }
        });
    }

    @Override
    public void onClick(View v) {
        switch (v.getId()) {
            case R.id.iv_popup_close:
                dismiss();
                break;
            default:
                break;
        }
    }

    /**
     * 消息提醒设置
     */
    private void messageReminderSettings() {
//        NIMClient.getService(FriendService.class).setMessageNotify(mAccid, cb_turnoff_message_reminders.isChecked()).setCallback(new RequestCallback<Void>() {
//            @Override
//            public void onSuccess(Void param) {
//                if (cb_turnoff_message_reminders.isChecked()) {
//                    ToastHelper.showToast(mContext, "关闭消息提醒成功");
//                } else {
//                    ToastHelper.showToast(mContext, "开启消息提醒成功");
//                }
//            }
//
//            @Override
//            public void onFailed(int code) {
//                if (code == 408) {
//                    ToastHelper.showToast(mContext, R.string.network_is_not_available);
//                } else {
//                    ToastHelper.showToast(mContext, "on failed:" + code);
//                }
//                cb_turnoff_message_reminders.setChecked(!cb_turnoff_message_reminders.isChecked());
//            }
//
//            @Override
//            public void onException(Throwable exception) {
//
//            }
//        });

    }

    /**
     * 拉黑操作
     */
    private void shielding() {
//        if (cb_shielding.isChecked()) {
//            NIMClient.getService(FriendService.class).addToBlackList(mAccid).setCallback(new RequestCallback<Void>() {
//                @Override
//                public void onSuccess(Void param) {
//                    ToastHelper.showToast(mContext, "加入黑名单成功");
//                }
//
//                @Override
//                public void onFailed(int code) {
//                    if (code == 408) {
//                        ToastHelper.showToast(mContext, R.string.network_is_not_available);
//                    } else {
//                        ToastHelper.showToast(mContext, "on failed：" + code);
//                    }
//                    cb_shielding.setChecked(!cb_shielding.isChecked());
//                }
//
//                @Override
//                public void onException(Throwable exception) {
//
//                }
//            });
//        } else {
//            NIMClient.getService(FriendService.class).removeFromBlackList(mAccid).setCallback(new RequestCallback<Void>() {
//                @Override
//                public void onSuccess(Void param) {
//                    ToastHelper.showToast(mContext, "移除黑名单成功");
//                }
//
//                @Override
//                public void onFailed(int code) {
//                    if (code == 408) {
//                        ToastHelper.showToast(mContext, R.string.network_is_not_available);
//                    } else {
//                        ToastHelper.showToast(mContext, "on failed:" + code);
//                    }
//                    cb_shielding.setChecked(!cb_shielding.isChecked());
//                }
//
//                @Override
//                public void onException(Throwable exception) {
//
//                }
//            });
//        }
    }
}

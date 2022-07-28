package com.bigshark.android.widget;

import android.app.Activity;
import android.content.Context;
import android.content.res.Resources;
import android.graphics.Rect;
import android.os.Handler;
import android.support.annotation.Nullable;
import android.util.AttributeSet;
import android.util.TypedValue;
import android.view.LayoutInflater;
import android.view.MotionEvent;
import android.view.View;
import android.view.animation.Animation;
import android.view.animation.TranslateAnimation;
import android.view.inputmethod.InputMethodManager;
import android.widget.EditText;
import android.widget.FrameLayout;
import android.widget.ImageView;
import android.widget.LinearLayout;

import com.bigshark.android.R;
import com.bigshark.android.contexts.AppContext;
import com.bigshark.android.listener.OnKeyBoardStateListener;
import com.bigshark.android.listener.radiohall.OnSendClickListener;
import com.bigshark.android.utils.ViewUtil;

import java.lang.reflect.Method;


public class NoEmojiPanelView extends LinearLayout implements OnKeyBoardStateListener {


    private LinearLayout mLayoutPanel;
    private EditText mEditText;
    private FrameLayout mLayoutNull;
    private LinearLayout mLayoutEmojiPanel;
    private ImageView iv_send_comments;
    private int mKeyBoardHeight;
    private int mDisplayHeight;
    private OnSendClickListener mOnSendClickListener;
    private boolean isKeyBoardShow;

    public NoEmojiPanelView(Context context) {
        super(context);
        init();
    }

    public NoEmojiPanelView(Context context, @Nullable AttributeSet attrs) {
        super(context, attrs);
        init();
    }

    public NoEmojiPanelView(Context context, @Nullable AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
        init();
    }

    @Override
    public boolean onTouchEvent(MotionEvent event) {
        if (event.getY() < ViewUtil.getScreenHeight() - dp2px(254f) && isShowing()) {
            dismiss();
        }
        return super.onTouchEvent(event);
    }


    public static int dp2px(float dpValue) {
        return (int) TypedValue.applyDimension(TypedValue.COMPLEX_UNIT_DIP, dpValue, AppContext.app.getResources().getDisplayMetrics());
    }


    public boolean isShowing() {
        return mLayoutPanel != null && mLayoutPanel.getVisibility() == VISIBLE;
    }

    private void showSoftKeyBoard() {
        InputMethodManager inputMethodManager = (InputMethodManager) getContext().getSystemService(Context.INPUT_METHOD_SERVICE);
        if (inputMethodManager != null && mEditText != null) {
            mEditText.post(() -> {
                mEditText.requestFocus();
                inputMethodManager.showSoftInput(mEditText, 0);
            });
            new Handler().postDelayed(() -> {
                //                changeLayoutNullParams(true);
            }, 200);
        }
    }


    private void hideSoftKeyBoard() {
        InputMethodManager inputMethodManager = (InputMethodManager) getContext().getSystemService(Context.INPUT_METHOD_SERVICE);
        if (inputMethodManager != null && mEditText != null) {
            inputMethodManager.hideSoftInputFromWindow(mEditText.getWindowToken(), 0);
        }
    }

    private void init() {
        View itemView = LayoutInflater.from(getContext()).inflate(R.layout.view_emoji_panel, this, false);
        mEditText = itemView.findViewById(R.id.edit_text);
        mEditText.setOnTouchListener((v, event) -> {
            showSoftKeyBoard();
            return true;
        });

        mLayoutNull = itemView.findViewById(R.id.layout_null);
        mLayoutPanel = itemView.findViewById(R.id.layout_panel);
        mLayoutEmojiPanel = itemView.findViewById(R.id.layout_emoji_panel);
        iv_send_comments = itemView.findViewById(R.id.iv_send_comments);
        addOnSoftKeyBoardVisibleListener((Activity) getContext(), this);
        addView(itemView);
        iv_send_comments.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
                String content = mEditText.getText().toString().trim();
                if (mOnSendClickListener != null) {
                    mOnSendClickListener.onSendContent(content);
                }
            }
        });
    }

    public void clearInputContent() {
        if (mEditText != null) {
            mEditText.setText(null);
        }
    }

    private void changeLayoutNullParams(boolean isShowSoftKeyBoard) {
        LayoutParams params = (LayoutParams) mLayoutNull.getLayoutParams();
        if (isShowSoftKeyBoard) {
            params.weight = 1;
            params.height = 0;
            mLayoutNull.setLayoutParams(params);
        } else {
            params.weight = 0;
            params.height = mDisplayHeight;
            mLayoutNull.setLayoutParams(params);
        }
    }

    private void changeEmojiPanelParams(int keyboardHeight) {
        if (mLayoutEmojiPanel != null) {
            LinearLayout.LayoutParams params = (LayoutParams) mLayoutEmojiPanel.getLayoutParams();
            params.height = keyboardHeight;
            mLayoutEmojiPanel.setLayoutParams(params);
        }
    }

    boolean isVisiableForLast = false;

    public void addOnSoftKeyBoardVisibleListener(Activity activity, final OnKeyBoardStateListener listener) {
        final View decorView = activity.getWindow().getDecorView();
        decorView.getViewTreeObserver().addOnGlobalLayoutListener(() -> {
            Rect rect = new Rect();
            decorView.getWindowVisibleDisplayFrame(rect);
            //计算出可见屏幕的高度
            int displayHight = rect.bottom - rect.top;
            //获得屏幕整体的高度
            int hight = decorView.getHeight();
            //获得键盘高度
            int keyboardHeight = hight - displayHight - calcStatusBarHeight(getContext());
            boolean visible = (double) displayHight / hight < 0.8;
            if (visible != isVisiableForLast) {
                listener.onSoftKeyBoardState(visible, keyboardHeight, displayHight - dp2px(49f));
            }
            isVisiableForLast = visible;
        });
    }

    private static int calcStatusBarHeight(Context context) {
        int statusHeight = -1;
        try {
            Class<?> clazz = Class.forName("com.android.internal.R$dimen");
            Object object = clazz.newInstance();
            int height = Integer.parseInt(clazz.getField("status_bar_height").get(object).toString());
            statusHeight = context.getResources().getDimensionPixelSize(height);
        } catch (Exception e) {
            e.printStackTrace();
        }
        return statusHeight;
    }


    @Override
    public void onSoftKeyBoardState(boolean visible, int keyboardHeight, int displayHeight) {
        this.isKeyBoardShow = visible;
        if (visible) {
            mKeyBoardHeight = keyboardHeight;
            mDisplayHeight = displayHeight;
            if (checkDeviceHasNavigationBar()) {
            } else {
                changeEmojiPanelParams(mKeyBoardHeight - dp2px(49f));
            }
        } else {
            changeEmojiPanelParams(0);
        }
    }

    private boolean checkDeviceHasNavigationBar() {
        boolean hasNavigationBar = false;
        Resources rs = AppContext.app.getResources();
        int id = rs.getIdentifier("config_showNavigationBar", "bool", "android");
        if (id > 0) {
            hasNavigationBar = rs.getBoolean(id);
        }
        try {
            Class systemPropertiesClass = Class.forName("android.os.SystemProperties");
            Method m = systemPropertiesClass.getMethod("get", String.class);
            String navBarOverride = (String) m.invoke(systemPropertiesClass, "qemu.hw.mainkeys");
            if ("1".equals(navBarOverride)) {
                hasNavigationBar = false;
            } else if ("0".equals(navBarOverride)) {
                hasNavigationBar = true;
            }
        } catch (Exception e) {
            e.printStackTrace();
        }
        return hasNavigationBar;
    }


    public void showEmojiPanel() {
        if (mLayoutPanel != null) {
            mLayoutPanel.setVisibility(VISIBLE);
        }
        showOrHideAnimation(true);
        showSoftKeyBoard();
    }

    private void showOrHideAnimation(final boolean isShow) {
        TranslateAnimation animation = new TranslateAnimation(Animation.RELATIVE_TO_PARENT, 0.0f,
                Animation.RELATIVE_TO_PARENT, 0.0f, Animation.RELATIVE_TO_PARENT, isShow ? 1.0f : 0.0f,
                Animation.RELATIVE_TO_PARENT, isShow ? 0.0f : 1.0f);
        animation.setDuration(200);
        mLayoutPanel.startAnimation(animation);
    }

    public void dismiss() {
        showOrHideAnimation(false);
        if (mLayoutPanel != null) {
            mLayoutPanel.setVisibility(GONE);
        }
        hideSoftKeyBoard();
    }

    public void setOnSendClickListener(OnSendClickListener onSendClickListener) {
        this.mOnSendClickListener = onSendClickListener;
    }
}

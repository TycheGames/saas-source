package com.bigshark.android.core.component.navigator;

import android.app.Activity;
import android.content.Context;
import android.content.res.TypedArray;
import android.support.annotation.ColorInt;
import android.support.annotation.ColorRes;
import android.support.annotation.DrawableRes;
import android.text.TextUtils;
import android.util.AttributeSet;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewTreeObserver;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.ProgressBar;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.core.R;
import com.bigshark.android.core.component.browser.IBrowserWebView;
import com.bigshark.android.core.utils.statusbar.SystemToolBarUtils;
import com.socks.library.KLog;

import java.lang.reflect.Field;

public class NavigationStatusLinearLayout extends LinearLayout implements IBrowserWebView.BrowserPageTitle {

    private static final int WHITE = 100;//有标题栏 白色
    private static final int IMG = 101;//有标题栏 图片
    private static final int TRAN_WHITE = 102;//有标题栏 白色字体 状态栏透明
    private static final int TRAN_BLCAK = 103;//有标题栏 黑色字体 状态栏透明
    private static final int THEME = 104;//有标题栏 主题色
    private static final int NO_TITLE_WHITE = 105;//无标题 白色字体 状态栏透明
    private static final int NO_TITLE_BLCAK = 106;//无标题 黑色字体 状态栏透明
    private static final int WHITEBG_BLACKTEXT = 107;//有标题栏 白色背景 黑色字体 灰色状态栏
    private static final int COLOR_WHITE = 108;//有标题栏 设置背景颜色 状态栏白色字体
    private static final int COLOR_BLACK = 109;//有标题栏 设置背景颜色 状态栏黑色字体
    private static final int NO_TITLE_THEME = 110;//无标题栏 主题色


    private SystemToolBarUtils toolbarHelper;

    private LinearLayout parentView;//父部局
    private View toolBarRoot;//状态视图
    private RelativeLayout titleRoot;

    private LinearLayout leftTitleRoot;
    private ImageView leftImage;
    private TextView leftText;

    private ImageView clooseImage;
    private TextView titleText;

    private LinearLayout rightTitleRoot;
    private ImageView rightImage;
    private TextView rightText;

    private View bottomLine;
    private ProgressBar progressBar;


    private int defultStyle = IMG;
    private int defaultImageBackground;
    private int titleBarBackground;
    public static int themeColorResId;


    public NavigationStatusLinearLayout(Context context) {
        this(context, null);
    }

    public NavigationStatusLinearLayout(Context context, AttributeSet attrs) {
        this(context, attrs, 0);
    }

    public NavigationStatusLinearLayout(Context context, AttributeSet attrs, int defStyle) {
        super(context, attrs, defStyle);

        toolbarHelper = new SystemToolBarUtils((Activity) context);

        LayoutInflater inflater = (LayoutInflater) context.getSystemService(Context.LAYOUT_INFLATER_SERVICE);
        inflater.inflate(R.layout.core_navi_status_layout, this, true);

        initView();
        initStyle(attrs);
        autoAdapterHeight();
    }

    private void initView() {
        parentView = findViewById(R.id.common_navigation_status_parent);
        toolBarRoot = findViewById(R.id.common_navigation_status_toolBar_root);
        titleRoot = findViewById(R.id.common_navigation_status_title_root);

        leftTitleRoot = findViewById(R.id.common_navigation_status_title_left_root);
        leftImage = findViewById(R.id.common_navigation_status_title_left_image);
        leftText = findViewById(R.id.common_navigation_status_title_left_text);

        clooseImage = findViewById(R.id.common_navigation_status_title_cloose_image);
        titleText = findViewById(R.id.common_navigation_status_title_title);

        rightTitleRoot = findViewById(R.id.common_navigation_status_title_right_root);
        rightImage = findViewById(R.id.common_navigation_status_title_right_image);
        rightText = findViewById(R.id.common_navigation_status_title_right_text);

        progressBar = findViewById(R.id.common_navigation_status_title_progressBar);
        bottomLine = findViewById(R.id.common_navigation_status_title_bottomLine);

        leftImage.setVisibility(View.GONE);
        leftText.setVisibility(View.GONE);
        clooseImage.setVisibility(View.GONE);
        titleText.setVisibility(View.GONE);
        rightImage.setVisibility(View.GONE);
        rightText.setVisibility(View.GONE);
        progressBar.setVisibility(View.GONE);
        bottomLine.setVisibility(View.INVISIBLE);
    }

    private void initStyle(AttributeSet attrs) {
        TypedArray attributes = getContext().obtainStyledAttributes(attrs, R.styleable.NavigationStatusLinearLayout);
        if (attributes != null) {
            //处理titleBar背景色
            titleBarBackground = attributes.getResourceId(R.styleable.NavigationStatusLinearLayout_title_background, -1);
            defultStyle = attributes.getInt(R.styleable.NavigationStatusLinearLayout_defult_style, defultStyle);
            initTheme();

            //标题
            String titleText = attributes.getString(R.styleable.NavigationStatusLinearLayout_title_text);
            if (!TextUtils.isEmpty(titleText)) {
                setTitle(titleText);
            }

            //设置左边按钮的文字
            String leftButtonText = attributes.getString(R.styleable.NavigationStatusLinearLayout_left_button_text);
            if (!TextUtils.isEmpty(leftButtonText)) {
                setLeftButtonText(leftButtonText);
                //设置左边按钮文字颜色
                int leftButtonTextColor = attributes.getColor(R.styleable.NavigationStatusLinearLayout_left_button_text_color, -1);
                if (leftButtonTextColor != -1) {
                    leftText.setTextColor(leftButtonTextColor);
                }
            }
            //设置左边图片icon
            int leftButtonDrawable = attributes.getResourceId(R.styleable.NavigationStatusLinearLayout_left_button_drawable, -1);
            if (leftButtonDrawable != -1) {
                setLeftButtonImg(leftButtonDrawable);
            }

            //先处理右边按钮
            //设置右边按钮的文字
            String rightButtonText = attributes.getString(R.styleable.NavigationStatusLinearLayout_right_button_text);
            if (!TextUtils.isEmpty(rightButtonText)) {
                setRightButtonText(rightButtonText);
                //设置右边按钮文字颜色
                int rightButtonTextColor = attributes.getColor(R.styleable.NavigationStatusLinearLayout_right_button_text_color, -1);
                if (rightButtonTextColor != -1) {
                    rightText.setTextColor(rightButtonTextColor);
                }
            }
            //设置右边图片icon 这里是二选一 要么只能是文字 要么只能是图片
            int rightButtonDrawable = attributes.getResourceId(R.styleable.NavigationStatusLinearLayout_right_button_drawable, -1);
            if (rightButtonDrawable != -1) {
                setRightButtonImg(rightButtonDrawable);
            }

            //处理关闭按钮
            boolean closeImageIsVisible = attributes.getBoolean(R.styleable.NavigationStatusLinearLayout_close_iv_visible, false);
            if (closeImageIsVisible) {
                showCloseView();
            }

            //显示底部line
            boolean bottomLineVisible = attributes.getBoolean(R.styleable.NavigationStatusLinearLayout_bottom_line_visible, false);
            if (bottomLineVisible) {
                showBottomLine();
            }

            attributes.recycle();
        }
    }

    private void initTheme() {
        switch (defultStyle) {
            case WHITE:
                initWhiteBackground();
                break;
            case IMG:
                if (titleBarBackground == -1) {
                    initImageBackground(defaultImageBackground);
                } else {
                    initImageBackground(titleBarBackground);
                }
                break;
            case TRAN_WHITE:
                initTransparentWhiteTextBackground();
                break;
            case TRAN_BLCAK:
                initTransparentBlackTextBackground();
                break;
            case NO_TITLE_WHITE:
                initNoTitleWhiteTextBackground();
                break;
            case NO_TITLE_BLCAK:
                initNoTitleBlackTextBackground();
                break;
            case WHITEBG_BLACKTEXT:
                initWhiteBackgroundWhiteText();
                break;
            case COLOR_WHITE:
                initColorBackgroundWhiteText();
                break;
            case COLOR_BLACK:
                initColorBackgroundBlackText();
                break;
            case NO_TITLE_THEME:
                initNoTitleWhiteTextThemeBackground();
                break;
            case THEME:
            default:
                initThemeBackground();
                break;
        }
    }

    /**
     * 设置 透明背景 白色字体
     */
    private void initTransparentWhiteTextBackground() {
        setToolBarTitleViewColorBackground(android.R.color.transparent);
        setTextColor(android.R.color.white);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }

    /**
     * 设置 透明背景 黑色字体
     */
    private void initTransparentBlackTextBackground() {
        setToolBarTitleViewColorBackground(android.R.color.transparent);
        setTextColor(android.R.color.black);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(true);
    }

    private void initTransparentNoTitleBlackTextBackground() {
        titleRoot.setVisibility(GONE);
        setToolBarTitleViewColorBackground(android.R.color.white);
        setTextColor(android.R.color.black);
        toolBarRoot.setBackgroundColor(getResources().getColor(android.R.color.white));
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(true);
        hideBottomLine();
    }

    /**
     * 设置 透明背景 黑色字体
     */
    private void initNoTitleBlackTextBackground() {
        titleRoot.setVisibility(GONE);
        setToolBarTitleViewColorBackground(android.R.color.transparent);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(true);
    }

    /**
     * 设置 透明背景 白色字体
     */
    private void initNoTitleWhiteTextBackground() {
        titleRoot.setVisibility(GONE);
        setToolBarTitleViewColorBackground(android.R.color.transparent);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }


    /**
     * 设置 透明背景 白色字体
     */
    private void initNoTitleWhiteTextThemeBackground() {
        titleRoot.setVisibility(GONE);
        setToolBarTitleViewColorBackground(themeColorResId);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }

    /**
     * 设置 主题色背景
     */
    private void initThemeBackground() {
        setToolBarTitleViewColorBackground(themeColorResId);
        setTextColor(android.R.color.white);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }

    /**
     * 设置 白色背景
     */
    private void initWhiteBackground() {
        setToolBarTitleViewColorBackground(android.R.color.white);
        setTextColor(themeColorResId);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(true);
    }

    /**
     * 设置 图片背景
     */
    private void initImageBackground(int imageResId) {
        setToolBarTitleViewImageBackground(imageResId);
        setTextColor(android.R.color.white);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }

    /**
     * 白色背景 黑色文字 灰色Bar
     */
    private void initWhiteBackgroundWhiteText() {
        setToolBarTitleViewColorBackground(android.R.color.white);
        setTextColor(android.R.color.black);
        toolBarRoot.setBackgroundColor(getResources().getColor(R.color.color_b2b2b2));
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
        showBottomLine();
    }

    /**
     * 设置颜色背景 白色文字
     */
    private void initColorBackgroundWhiteText() {
        setToolBarTitleViewColorBackground(titleBarBackground);
        setTextColor(android.R.color.white);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(false);
    }

    /**
     * 白色背景 黑色文字
     */
    private void initColorBackgroundBlackText() {
        setToolBarTitleViewColorBackground(titleBarBackground);
        setTextColor(android.R.color.black);
        toolbarHelper.setTranslucentStatus();
        toolbarHelper.setStatusTextColor(true);
    }

    /**
     * 设置状态栏高度
     */
    private void autoAdapterHeight() {
        titleRoot.getViewTreeObserver().addOnPreDrawListener(new ViewTreeObserver.OnPreDrawListener() {
            @Override
            public boolean onPreDraw() {
                final int statusBarHeight = getStatusBarHeight();
                KLog.d("ToolBar", "Title Height " + titleRoot.getHeight() + " Parent Height " + parentView.getHeight() + " ToolBar Height " + toolBarRoot.getHeight() + " SYSBAR Height" + statusBarHeight);
                titleRoot.getViewTreeObserver().removeOnPreDrawListener(this);
                LayoutParams p_params = (LayoutParams) parentView.getLayoutParams();//获取当前控件的布局对象
                p_params.height = statusBarHeight + titleRoot.getHeight();//设置当前控件布局的高度
                parentView.setLayoutParams(p_params);
                LayoutParams tool_params = (LayoutParams) toolBarRoot.getLayoutParams();//获取当前控件的布局对象
                tool_params.height = statusBarHeight;//设置当前控件布局的高度
                toolBarRoot.setLayoutParams(tool_params);
                KLog.d("ToolBar", "Title Height " + titleRoot.getHeight() + " Parent Height " + parentView.getHeight() + " ToolBar Height " + toolBarRoot.getHeight() + " SYSBAR Height" + statusBarHeight);
                return true;
            }
        });
    }

    /**
     * 得到状态栏的高度
     */
    private int getStatusBarHeight() {
        Class<?> c = null;
        Object obj = null;
        Field field = null;
        int x = 0, sbar = 0;
        try {
            c = Class.forName("com.android.internal.R$dimen");
            obj = c.newInstance();
            field = c.getField("status_bar_height");
            x = Integer.parseInt(field.get(obj).toString());
            sbar = getContext().getResources().getDimensionPixelSize(x);
        } catch (Exception e1) {
            e1.printStackTrace();
        }
        return sbar;
    }

    /**
     * 设置 背景色
     *
     * @param bgResId
     */
    private void setToolBarTitleViewImageBackground(@DrawableRes int bgResId) {
        if (parentView != null) {
            parentView.setBackgroundResource(bgResId);
        }
    }

    public void addCustomHeader(View headerView) {
        titleRoot.addView(headerView, titleRoot.getChildCount());
    }

    /**
     * 设置 背景色
     *
     * @param bgid
     */
    private void setToolBarTitleViewColorBackground(@ColorRes int bgid) {
        if (parentView != null) {
            parentView.setBackgroundColor(getContext().getResources().getColor(bgid));
        }
    }

    private void setToolBarTitleViewColor(@ColorInt int color) {
        if (parentView != null) {
            parentView.setBackgroundColor(color);
        }
    }


    private void setBottomViewPaddingTop(View view, boolean isPadding) {
        if (isPadding) {
            view.setPadding(0, getToolBarTitleViewHeight(), 0, 0);
        } else {
            view.setPadding(0, 0, 0, 0);
        }
    }

    /**
     * 返回 状态栏 + 导航栏高度
     */
    private int getToolBarTitleViewHeight() {
        return parentView.getHeight();
    }


    /**
     * 设置字体颜色
     *
     * @param colorRes
     */
    private void setTextColor(@ColorRes int colorRes) {
        int color = getResources().getColor(colorRes);
        leftText.setTextColor(color);
        titleText.setTextColor(color);
        rightText.setTextColor(color);
    }

    @Override
    public void setTitle(String text) {
        titleText.setVisibility(View.VISIBLE);
        titleText.setText(text);
    }

    public void setTitle(int textid) {
        titleText.setVisibility(View.VISIBLE);
        titleText.setText(textid);
    }

    public void setLeftButtonText(String text) {
        leftText.setVisibility(View.VISIBLE);
        leftText.setText(text);
    }

    public void setLeftButtonText(int textid) {
        leftText.setVisibility(View.VISIBLE);
        leftText.setText(textid);
    }

    public void setLeftButtonImg(int imgId) {
        leftImage.setVisibility(View.VISIBLE);
        leftImage.setImageResource(imgId);
    }

    @Override
    public void setLeftClickListener(OnClickListener listener) {
        leftTitleRoot.setOnClickListener(listener);
    }

    public void setRightButtonText(String text) {
        rightText.setVisibility(View.VISIBLE);
        rightText.setText(text);
    }

    public void setRightButtonText(int textid) {
        rightText.setVisibility(View.VISIBLE);
        rightText.setText(textid);
    }

    public void setRightButtonImg(int imgId) {
        rightImage.setVisibility(View.VISIBLE);
        rightImage.setImageResource(imgId);
    }

    public void setRightClickListener(OnClickListener listener) {
        rightTitleRoot.setOnClickListener(listener);
    }

    public void setRightImageClickListener(OnClickListener listener) {
        rightImage.setOnClickListener(listener);
    }

    /**
     * 隐藏底部线
     */
    public void hideBottomLine() {
        bottomLine.setVisibility(View.INVISIBLE);
    }

    /**
     * 显示底部线
     */
    public void showBottomLine() {
        bottomLine.setVisibility(View.VISIBLE);
    }


    @Override
    public ImageView getCloseImage() {
        return clooseImage;
    }

    @Override
    public void setCloseClickListener(OnClickListener listener) {
        clooseImage.setOnClickListener(listener);
    }

    @Override
    public void showCloseView() {
        clooseImage.setVisibility(View.VISIBLE);
    }

    @Override
    public void hideCloseView() {
        clooseImage.setVisibility(View.GONE);
    }

    public void showProgress() {
        progressBar.setVisibility(View.VISIBLE);
    }


    public void hideProgress() {
        progressBar.setVisibility(View.GONE);
    }


    public void setAnimProgress(int progress) {
        progressBar.setVisibility(View.VISIBLE);
        progressBar.setProgress(progress);
    }

    public LinearLayout getParentView() {
        return parentView;
    }

    public View getToolBarRoot() {
        return toolBarRoot;
    }

    public RelativeLayout getTitleRoot() {
        return titleRoot;
    }

    public LinearLayout getLeftTitleRoot() {
        return leftTitleRoot;
    }

    public LinearLayout getRightTitleRoot() {
        return rightTitleRoot;
    }

    public ImageView getLeftImage() {
        return leftImage;
    }

    public TextView getLeftText() {
        return leftText;
    }

    public ImageView getClooseImage() {
        return clooseImage;
    }

    public TextView getTitleText() {
        return titleText;
    }

    public ImageView getRightImage() {
        return rightImage;
    }

    @Override
    public TextView getRightTextView() {
        return rightText;
    }

    public View getBottomLine() {
        return bottomLine;
    }


    @Override
    public ProgressBar getProgressBar() {
        showProgress();
        return progressBar;
    }

    public ProgressBar getProgressBarView() {
        return progressBar;
    }
}

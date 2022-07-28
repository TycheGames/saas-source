package com.bigshark.android.activities.radiohall;

import android.app.Activity;
import android.content.Intent;
import android.os.Bundle;
import android.support.v4.view.ViewPager;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.adapters.radiohall.RadioPhotoDetailsAdapter;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.display.DisplayBaseActivity;
import com.bigshark.android.widget.PhotoViewPager;
import com.gyf.immersionbar.ImmersionBar;

import java.util.ArrayList;

//import com.dinuscxj.progressbar.CircleProgressBar;
import butterknife.ButterKnife;
import butterknife.OnClick;

/**
 * 查看广播图片
 */
public class ViewRadioPhotoActivity extends DisplayBaseActivity {

    private static final String EXTRA_LIST = "extra_list";
    private static final String EXTRA_POS = "extra_pos";
    private ImageView mIv_titlebar_left_back;
    private TextView tv_page;
    private PhotoViewPager view_pager;
    private int mCurrentPosition;
    private ArrayList<String> mPhotoList;
    private RadioPhotoDetailsAdapter mRadioPhotoDetailsAdapter;
    //    private CircleProgressBar custom_progress;
    private RelativeLayout rl_set_shadow, rl_burn_after_reading;
    private LinearLayout ll_has_burned, ll_send_red_envelope;

    public static void openIntent(IDisplay display, ArrayList<String> imgList, int pos) {
        Intent intent = new Intent(display.context(), ViewRadioPhotoActivity.class);
        intent.putStringArrayListExtra(EXTRA_LIST, imgList);
        intent.putExtra(EXTRA_POS, pos);
        display.startActivity(intent);
    }

    @Override
    protected int getLayoutId() {
        return R.layout.activity_view_user_photo;
    }

    @Override
    public void bindViews(Bundle savedInstanceState) {
        ButterKnife.bind(this);
        //设置共同沉浸式样式
        ImmersionBar.with(this).fitsSystemWindows(true).statusBarDarkFont(true).statusBarColor(R.color.white).init();
        if (getIntent() != null) {
            mPhotoList = getIntent().getStringArrayListExtra(EXTRA_LIST);
            mCurrentPosition = getIntent().getIntExtra(EXTRA_POS, 0);
        }
        mIv_titlebar_left_back = findViewById(R.id.iv_titlebar_left_back);
        tv_page = findViewById(R.id.tv_page);
        view_pager = findViewById(R.id.view_pager);
    }

    @Override
    public void bindListeners(Bundle savedInstanceState) {
    }

    @Override
    public void setupDatas() {
        mRadioPhotoDetailsAdapter = new RadioPhotoDetailsAdapter(this, mPhotoList);
        view_pager.setAdapter(mRadioPhotoDetailsAdapter);
        view_pager.setCurrentItem(mCurrentPosition, false);
        tv_page.setText(mCurrentPosition + 1 + "/" + mPhotoList.size());
        view_pager.addOnPageChangeListener(new ViewPager.OnPageChangeListener() {
            @Override
            public void onPageScrolled(int i, float v, int i1) {

            }

            @Override
            public void onPageSelected(int i) {
                mCurrentPosition = i;
                tv_page.setText(mCurrentPosition + 1 + "/" + mPhotoList.size());

            }

            @Override
            public void onPageScrollStateChanged(int i) {

            }
        });
    }

    @OnClick(R.id.iv_titlebar_left_back)
    public void onViewClicked() {
        finish();
    }
}

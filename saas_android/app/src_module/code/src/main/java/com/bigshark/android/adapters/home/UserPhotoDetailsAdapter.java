package com.bigshark.android.adapters.home;

import android.app.Activity;
import android.support.annotation.NonNull;
import android.support.v4.view.PagerAdapter;
import android.view.View;
import android.view.ViewGroup;
import android.widget.LinearLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.http.model.user.PicsModel;
import com.github.chrisbanes.photoview.PhotoView;

import org.xutils.common.util.DensityUtil;
import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.List;

//import com.dinuscxj.progressbar.CircleProgressBar;
//import com.bigshark.android.activities.usercenter.UserCenter;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 11:05
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class UserPhotoDetailsAdapter extends PagerAdapter {

    private Activity mActivity;
    private List<PicsModel> mPicsModelList;
    private View mRootView;
    private View mCurrentView;

    private RelativeLayout rl_set_shadow, rl_burn_after_reading, rl_big_photo_bg;
    private LinearLayout ll_has_burned, ll_to_certification_prompt, ll_send_red_envelope, ll_red_envelope;
    private TextView tv_vip_describe, tv_vip;
//    private CircleProgressBar custom_progress;
    private PhotoView mPhotoView;


    public UserPhotoDetailsAdapter(Activity activity, List<PicsModel> list) {
        this.mActivity = activity;
        this.mPicsModelList = list;
    }

    @NonNull
    @Override
    public Object instantiateItem(@NonNull ViewGroup container, int position) {
        mRootView = View.inflate(mActivity, R.layout.view_user_photo_details, null);
        mPhotoView = mRootView.findViewById(R.id.photoview);
        initListener(position);
        container.addView(mRootView);
        return mRootView;
    }

    private void initListener(int pos) {
        PicsModel picsModel = mPicsModelList.get(pos);
        rl_set_shadow = mRootView.findViewById(R.id.rl_set_shadow);
        rl_big_photo_bg = mRootView.findViewById(R.id.rl_big_photo_bg);
        ll_has_burned = mRootView.findViewById(R.id.ll_has_burned);
        ll_to_certification_prompt = mRootView.findViewById(R.id.ll_to_certification_prompt);
        rl_burn_after_reading = mRootView.findViewById(R.id.rl_burn_after_reading);
        ll_send_red_envelope = mRootView.findViewById(R.id.ll_send_red_envelope);
        ll_red_envelope = mRootView.findViewById(R.id.ll_red_envelope);
        tv_vip_describe = mRootView.findViewById(R.id.tv_vip_describe);
        tv_vip = mRootView.findViewById(R.id.tv_vip);
//        custom_progress = mRootView.findViewById(R.id.custom_progress);

        if (picsModel.isIs_burn_after_reading()) {
            x.image().bind(
                    mPhotoView,
                    picsModel.getPic_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setRadius(DensityUtil.dip2px(10))
                            .build());
            rl_big_photo_bg.setVisibility(View.VISIBLE);
            rl_set_shadow.setVisibility(View.VISIBLE);
            if (picsModel.isIs_burn()) {
                //已焚毁
                ll_has_burned.setVisibility(View.VISIBLE);
                rl_burn_after_reading.setVisibility(View.GONE);
                ll_send_red_envelope.setVisibility(View.GONE);
//                if (1 == UserCenter.instance().getUserGender()) {
//                    if (MmkvGroup.loginInfo().getVipState() == 1) {
//                        ll_to_certification_prompt.setVisibility(View.GONE);
//                    } else {
//                        ll_to_certification_prompt.setVisibility(View.VISIBLE);
//                    }
//                } else if (2 == UserCenter.instance().getUserGender()) {
//                    if (MmkvGroup.loginInfo().getIdentifyState() == 1) {
//                        ll_to_certification_prompt.setVisibility(View.GONE);
//                    } else {
//                        ll_to_certification_prompt.setVisibility(View.VISIBLE);
//                    }
//                }
            } else {
                //阅后即焚 未焚毁 长按查看
                ll_has_burned.setVisibility(View.GONE);
                rl_burn_after_reading.setVisibility(View.VISIBLE);
                if (picsModel.isIs_red_pack()) {
                    ll_send_red_envelope.setVisibility(View.VISIBLE);
                } else {
                    ll_send_red_envelope.setVisibility(View.GONE);
                }
            }
        } else if (picsModel.isIs_red_pack()) {
            rl_big_photo_bg.setVisibility(View.VISIBLE);
            rl_set_shadow.setVisibility(View.VISIBLE);
            //红包照片已付款
            if (picsModel.isIs_pay()) {
                if (picsModel.isIs_burn_after_reading()) {
                    x.image().bind(
                            mPhotoView,
                            picsModel.getPic_url(),
                            new ImageOptions.Builder()
                                    .setUseMemCache(true)//设置使用缓存
                                    .setRadius(DensityUtil.dip2px(10))
                                    .build()
                    );
                    if (picsModel.isIs_burn()) {
                        //已焚毁
                        ll_has_burned.setVisibility(View.VISIBLE);
                        rl_burn_after_reading.setVisibility(View.GONE);
                        ll_send_red_envelope.setVisibility(View.GONE);
                    } else {
                        //阅后即焚 未焚毁 长按查看
                        ll_has_burned.setVisibility(View.GONE);
                        rl_burn_after_reading.setVisibility(View.VISIBLE);
                        ll_send_red_envelope.setVisibility(View.GONE);
                    }
                } else {
                    x.image().bind(
                            mPhotoView,
                            picsModel.getPic_url(),
                            new ImageOptions.Builder()
                                    .setUseMemCache(true)//设置使用缓存
                                    .setRadius(DensityUtil.dip2px(10))
                                    .build()
                    );
                    //已付款、没有设置阅后即焚
                    rl_set_shadow.setVisibility(View.GONE);
                    rl_big_photo_bg.setVisibility(View.GONE);
                }
            } else {
                x.image().bind(
                        mPhotoView,
                        picsModel.getPic_url(),
                        new ImageOptions.Builder()
                                .setUseMemCache(true)//设置使用缓存
                                .setRadius(DensityUtil.dip2px(10))
                                .build()
                );
                //红包照片未付款
                ll_has_burned.setVisibility(View.GONE);
                rl_burn_after_reading.setVisibility(View.GONE);
                ll_send_red_envelope.setVisibility(View.VISIBLE);
            }
        } else {
            x.image().bind(
                    mPhotoView,
                    picsModel.getPic_url(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setRadius(DensityUtil.dip2px(10))
                            .build()
            );
            rl_big_photo_bg.setVisibility(View.GONE);
            rl_set_shadow.setVisibility(View.GONE);
        }

//        if (1 == UserCenter.instance().getUserGender()) {
//            tv_vip_describe.setText("成为VIP后可延长查看时间达6秒");
//            tv_vip.setText("成为VIP");
//        } else if (2 == UserCenter.instance().getUserGender()) {
//            tv_vip_describe.setText("认证后可延长查看时间达6秒");
//            tv_vip.setText("立即认证");
//        }
        tv_vip.setOnClickListener(new android.view.View.OnClickListener() {
            @Override
            public void onClick(View view) {
//                if (1 == UserCenter.instance().getUserGender()) {
//                    //成为vip
//                    Intent intent = new Intent(attachedActivity, MyWebViewActivity.class);
//                    intent.putExtra(MyWebViewActivity.EXTRA_URL, MmkvGroup.global().getVipLink());
//                    attachedActivity.startActivity(intent);
//                } else if (2 == UserCenter.instance().getUserGender()) {
//                    //去认证
//                    Intent intent = new Intent(attachedActivity, MyWebViewActivity.class);
//                    intent.putExtra(MyWebViewActivity.EXTRA_URL, MmkvGroup.global().getAuthCenterLink());
//                    attachedActivity.startActivity(intent);
//                }
            }
        });
    }

    @Override
    public int getCount() {
        return mPicsModelList.size();
    }

    @Override
    public void destroyItem(@NonNull ViewGroup container, int position, @NonNull Object object) {
        container.removeView((View) object);
    }

    @Override
    public boolean isViewFromObject(@NonNull View view, @NonNull Object o) {
        return view == o;
    }

    @Override
    public int getItemPosition(@NonNull Object object) {
        return POSITION_NONE;
    }

    @Override
    public void setPrimaryItem(@NonNull ViewGroup container, int position, @NonNull Object object) {
        super.setPrimaryItem(container, position, object);
        mCurrentView = (View) object;
    }

    public View getPrimaryItem() {
        return mCurrentView;
    }

}

package com.bigshark.android.adapters.radiohall;

import android.app.Activity;
import android.support.annotation.NonNull;
import android.support.v4.view.PagerAdapter;
import android.view.View;
import android.view.ViewGroup;

import com.bigshark.android.R;
import com.github.chrisbanes.photoview.PhotoView;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.List;

//import com.bumptech.glide.Glide;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/22 11:05
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class RadioPhotoDetailsAdapter extends PagerAdapter {

    private Activity mActivity;
    private List<String> mPicsBeanList;
    private View mRootView;
    private View mCurrentView;

    public RadioPhotoDetailsAdapter(Activity activity, List<String> list) {
        this.mActivity = activity;
        this.mPicsBeanList = list;
    }

    @NonNull
    @Override
    public Object instantiateItem(@NonNull ViewGroup container, int position) {
        mRootView = View.inflate(mActivity, R.layout.view_radio_photo_details, null);
        PhotoView photoView = mRootView.findViewById(R.id.photoview);
//        Glide.with(attachedActivity).load(mPicsBeanList.get(position)).into(photoView);
        x.image().bind(
                photoView,
                mPicsBeanList.get(position),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .build()
        );
        container.addView(mRootView);
        return mRootView;
    }

    @Override
    public int getCount() {
        return mPicsBeanList.size();
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

}

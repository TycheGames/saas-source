package com.bigshark.android.listener.radiohall;

import android.view.View;
import android.widget.ImageView;

/**
 * @创建者 wenqi
 * @创建时间 2019/6/5 16:13
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public interface OnRadioClickListener {

    public void onCommentsClick(int pos);

    public void onApplyClick(int pos);

    public void onPraiseClick(int pos,ImageView imageView);

    public void onAvatarClick(int pos);

    public void onCheckRegistrationClick(int pos);

    public void onReportClick(int pos, View view);


}

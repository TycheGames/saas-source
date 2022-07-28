package com.bigshark.android.adapters.radiohall;

import android.support.v4.content.ContextCompat;
import android.support.v7.widget.GridLayoutManager;
import android.support.v7.widget.RecyclerView;
import android.view.View;
import android.widget.ImageView;
import android.widget.LinearLayout;
import android.widget.TextView;

import com.bigshark.android.R;
import com.bigshark.android.activities.radiohall.ViewRadioPhotoActivity;
import com.bigshark.android.adapters.home.ViewUserPhotoAdapter;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.radiohall.RadioListItemModel;
import com.bigshark.android.http.model.user.PicsModel;
import com.bigshark.android.listener.radiohall.OnRadioClickListener;
import com.bigshark.android.utils.StringUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.ArrayList;
import java.util.List;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/8 16:47
 * @描述 广播大厅 推荐的广播 list adapter
 */
public class RadioHallListAdapter extends BaseQuickAdapter<RadioListItemModel, BaseViewHolder> {

    private IDisplay display;

    private OnRadioClickListener mOnRadioClickListener;

    private ImageView mIv_radiohall_item_to_report, mIv_radiohall_item_like_icon, iv_radiohall_item_head,
            iv_radiohall_item_vip, iv_radiohall_item_gender, iv_radiohall_item_comment_icon, iv_radiohall_item_apply;
    private TextView mTv_radiohall_item_like_number, tv_radiohall_item_nickname, tv_radiohall_item_creat_time,
            tv_radiohall_item_theme, tv_radiohall_item_appointment_time, tv_radiohall_item_dating_city, tv_radiohall_item_hope,
            tv_radiohall_item_supplement, tv_radiohall_item_comment, tv_radiohall_item_myapply_number, tv_radiohall_item_apply,
            tv_stick;
    private LinearLayout ll_user_radio_centent, ll_radiohall_item_like, ll_radiohall_item_comment, ll_radiohall_item_check_registration,
            ll_radiohall_item_apply, ll_radiohall_item_supplement;
    private RecyclerView racyclerview_radiohall;

    public RadioHallListAdapter(IDisplay display, int layoutResId) {
        super(layoutResId);
        this.display = display;
    }

    @Override
    protected void convert(BaseViewHolder helper, RadioListItemModel item) {
        tv_stick = helper.getView(R.id.tv_stick);//置顶
        ll_user_radio_centent = helper.getView(R.id.ll_user_radio_centent);//用户发布的内容
        iv_radiohall_item_head = helper.getView(R.id.iv_radiohall_item_head);
        mIv_radiohall_item_to_report = helper.getView(R.id.iv_radiohall_item_to_report);//举报
        tv_radiohall_item_nickname = helper.getView(R.id.tv_radiohall_item_nickname);
        iv_radiohall_item_vip = helper.getView(R.id.iv_radiohall_item_vip);
        iv_radiohall_item_gender = helper.getView(R.id.iv_radiohall_item_gender);
        tv_radiohall_item_creat_time = helper.getView(R.id.tv_radiohall_item_creat_time);
        tv_radiohall_item_theme = helper.getView(R.id.tv_radiohall_item_theme);
        tv_radiohall_item_appointment_time = helper.getView(R.id.tv_radiohall_item_appointment_time);
        tv_radiohall_item_dating_city = helper.getView(R.id.tv_radiohall_item_dating_city);
        tv_radiohall_item_hope = helper.getView(R.id.tv_radiohall_item_hope);
        tv_radiohall_item_supplement = helper.getView(R.id.tv_radiohall_item_supplement);
        ll_radiohall_item_like = helper.getView(R.id.ll_radiohall_item_like);//like
        mIv_radiohall_item_like_icon = helper.getView(R.id.iv_radiohall_item_like_icon);
        mTv_radiohall_item_like_number = helper.getView(R.id.tv_radiohall_item_like_number);
        ll_radiohall_item_comment = helper.getView(R.id.ll_radiohall_item_comment);
        iv_radiohall_item_comment_icon = helper.getView(R.id.iv_radiohall_item_comment_icon);
        tv_radiohall_item_comment = helper.getView(R.id.tv_radiohall_item_comment);
        ll_radiohall_item_check_registration = helper.getView(R.id.ll_radiohall_item_check_registration);
        tv_radiohall_item_myapply_number = helper.getView(R.id.tv_radiohall_item_myapply_number);
        ll_radiohall_item_apply = helper.getView(R.id.ll_radiohall_item_apply);//报名
        iv_radiohall_item_apply = helper.getView(R.id.iv_radiohall_item_apply);
        tv_radiohall_item_apply = helper.getView(R.id.tv_radiohall_item_apply);
        racyclerview_radiohall = helper.getView(R.id.racyclerview_radiohall_item_photos);
        ll_radiohall_item_supplement = helper.getView(R.id.ll_radiohall_item_supplement);

        tv_radiohall_item_nickname.setText(item.getNickname());
        tv_radiohall_item_creat_time.setText(item.getCreated_at());
        tv_radiohall_item_theme.setText(item.getTheme());
        tv_radiohall_item_appointment_time.setText(item.getDate() + item.getTime_slot());
        tv_radiohall_item_dating_city.setText(item.getCity());
        tv_radiohall_item_hope.setText("约会期望：" + item.getHope());
        if (StringUtil.isBlank(item.getSupplement())) {
            ll_radiohall_item_supplement.setVisibility(View.GONE);
        } else {
            ll_radiohall_item_supplement.setVisibility(View.VISIBLE);
            tv_radiohall_item_supplement.setText(item.getSupplement());
        }

        if (1 == item.getSex()) {
            x.image().bind(
                    iv_radiohall_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            iv_radiohall_item_gender.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_gender_men_icon));
            if (1 == item.getIsVip()) {
                iv_radiohall_item_vip.setVisibility(View.VISIBLE);
                iv_radiohall_item_vip.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_vip_icon));
            } else {
                iv_radiohall_item_vip.setVisibility(View.GONE);
            }
        } else if (2 == item.getSex()) {
            x.image().bind(
                    iv_radiohall_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );

            iv_radiohall_item_gender.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_gender_women_icon));
            if (1 == item.getIsIdentify()) {
                iv_radiohall_item_vip.setVisibility(View.VISIBLE);
                iv_radiohall_item_vip.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_certified_icon));
            } else {
                iv_radiohall_item_vip.setVisibility(View.GONE);
            }
        }
        if (item.getClick_good_num() > 0) {
            mTv_radiohall_item_like_number.setText(item.getClick_good_num() + "");
        } else {
            mTv_radiohall_item_like_number.setText("like");
        }
        if (1 == item.getIs_click_good()) {
            mIv_radiohall_item_like_icon.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_givelike_icon));
            ll_radiohall_item_like.setEnabled(false);
        } else {
            mIv_radiohall_item_like_icon.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_ungivelike_icon));
            ll_radiohall_item_like.setEnabled(true);
        }
        //是否可评论
        if (1 == item.getComment_status()) {
            ll_radiohall_item_comment.setEnabled(true);
            //是否已评论
            if (1 == item.getIs_comment()) {
                iv_radiohall_item_comment_icon.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_have_comments_icon));
                tv_radiohall_item_comment.setText("Reviewed");
                tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            } else {
                iv_radiohall_item_comment_icon.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_no_comments_icon));
                tv_radiohall_item_comment.setText("comment");
                tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            }
        } else {
            iv_radiohall_item_comment_icon.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_no_comments_icon));
            tv_radiohall_item_comment.setText("Comments are closed");
            tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            ll_radiohall_item_comment.setEnabled(false);
        }
        if (1 == item.getIs_official()) {
            iv_radiohall_item_gender.setVisibility(View.GONE);
            iv_radiohall_item_vip.setVisibility(View.GONE);
            mIv_radiohall_item_to_report.setVisibility(View.GONE);
            ll_user_radio_centent.setVisibility(View.GONE);
            tv_radiohall_item_supplement.setTextColor(ContextCompat.getColor(display.context(), R.color.color_3e3d3d));
            ll_radiohall_item_check_registration.setVisibility(View.INVISIBLE);
            ll_radiohall_item_apply.setVisibility(View.INVISIBLE);
            x.image().bind(
                    iv_radiohall_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.mipmap.radiohall_communique_logo)
                            .build()
            );
        } else {
            iv_radiohall_item_gender.setVisibility(View.VISIBLE);
            iv_radiohall_item_vip.setVisibility(View.VISIBLE);
            ll_user_radio_centent.setVisibility(View.VISIBLE);
            tv_radiohall_item_supplement.setTextColor(ContextCompat.getColor(display.context(), R.color.color_9e9ea4));
            ll_radiohall_item_apply.setVisibility(View.VISIBLE);
            if (1 == item.getIs_oneself()) {
                mIv_radiohall_item_to_report.setVisibility(View.GONE);
                ll_radiohall_item_check_registration.setVisibility(View.VISIBLE);
                tv_radiohall_item_myapply_number.setText("View registration(" + item.getEnrolled_num() + ")");
                iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_overradio_textleft_icon));
                tv_radiohall_item_apply.setText("Close registration");
                tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            } else {
                mIv_radiohall_item_to_report.setVisibility(View.VISIBLE);
                ll_radiohall_item_check_registration.setVisibility(View.INVISIBLE);
                if (1 == item.getIs_enroll()) {
                    iv_radiohall_item_apply.setClickable(false);
                    iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_applied_textleft_icon));
                    tv_radiohall_item_apply.setText("Successful registration(" + item.getEnrolled_num() + ")");
                    tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
                } else {
                    iv_radiohall_item_apply.setClickable(true);
                    iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_apply_textleft_icon));
                    tv_radiohall_item_apply.setText("I want to sign up(" + item.getEnrolled_num() + ")");
                    tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(display.context(), R.color.color_161618));
                }
            }
        }
        if (1 == item.getUppermost()) {
            tv_stick.setVisibility(View.VISIBLE);
        } else {
            tv_stick.setVisibility(View.GONE);
        }
        initRadioPhoto(item);
        ll_radiohall_item_like.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onPraiseClick(helper.getAdapterPosition() - 1, mIv_radiohall_item_like_icon);
                }
            }
        });
        iv_radiohall_item_head.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onAvatarClick(helper.getAdapterPosition() - 1);
                }
            }
        });
        ll_radiohall_item_apply.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onApplyClick(helper.getAdapterPosition() - 1);
                }
            }
        });
        ll_radiohall_item_comment.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onCommentsClick(helper.getAdapterPosition() - 1);
                }
            }
        });
        ll_radiohall_item_check_registration.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onCheckRegistrationClick(helper.getAdapterPosition() - 1);
                }
            }
        });
        mIv_radiohall_item_to_report.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onReportClick(helper.getAdapterPosition() - 1, v);
                }
            }
        });
    }

    private void initRadioPhoto(RadioListItemModel item) {
        if (item.getImg() != null && item.getImg().size() > 0) {
            racyclerview_radiohall.setVisibility(View.VISIBLE);
            List<PicsModel> picsModelList = new ArrayList<>();
            for (int i = 0; i < item.getImg().size(); i++) {
                PicsModel picsModel = new PicsModel();
                picsModel.setPic_url(item.getImg().get(i));
                picsModelList.add(picsModel);
            }
            racyclerview_radiohall.setLayoutManager(new GridLayoutManager(display.context(), 3));
            ViewUserPhotoAdapter adapter = new ViewUserPhotoAdapter(display);
            racyclerview_radiohall.setAdapter(adapter);
            adapter.setNewData(picsModelList);
            adapter.setOnItemClickListener(new OnItemClickListener() {

                @Override
                public void onItemClick(BaseQuickAdapter adapter, View view, int position) {
                    ViewRadioPhotoActivity.openIntent(display, item.getImg(), position);
                }
            });
        } else {
            racyclerview_radiohall.setVisibility(View.GONE);
        }
    }

    /**
     * 区分官方广播
     *
     * @param helper
     * @param item
     */
    private void initOfficialRaido(BaseViewHolder helper, RadioListItemModel item) {
        if (1 == item.getIs_official()) {
            iv_radiohall_item_gender.setVisibility(View.GONE);
            iv_radiohall_item_vip.setVisibility(View.GONE);
            mIv_radiohall_item_to_report.setVisibility(View.GONE);
            ll_user_radio_centent.setVisibility(View.GONE);
            tv_radiohall_item_supplement.setTextColor(ContextCompat.getColor(display.context(), R.color.color_3e3d3d));
            ll_radiohall_item_check_registration.setVisibility(View.INVISIBLE);
            ll_radiohall_item_apply.setVisibility(View.INVISIBLE);
            x.image().bind(
                    iv_radiohall_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.mipmap.radiohall_communique_logo)
                            .build()
            );
        } else {
            iv_radiohall_item_gender.setVisibility(View.VISIBLE);
            iv_radiohall_item_vip.setVisibility(View.VISIBLE);
            mIv_radiohall_item_to_report.setVisibility(View.VISIBLE);
            ll_user_radio_centent.setVisibility(View.VISIBLE);
            tv_radiohall_item_supplement.setTextColor(ContextCompat.getColor(display.context(), R.color.color_9e9ea4));
            ll_radiohall_item_check_registration.setVisibility(View.INVISIBLE);
            ll_radiohall_item_apply.setVisibility(View.VISIBLE);
        }
    }

    public void setOnRadioClickListener(OnRadioClickListener onRadioClickListener) {
        this.mOnRadioClickListener = onRadioClickListener;
    }

}

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
public class MyRadioListAdapter extends BaseQuickAdapter<RadioListItemModel, BaseViewHolder> {

    private IDisplay display;

    private OnRadioClickListener mOnRadioClickListener;

    private ImageView mIv_radiohall_item_to_report, mIv_radiohall_item_like_icon, iv_radiohall_item_head,
            iv_radiohall_item_vip, iv_radiohall_item_gender, iv_radiohall_item_comment_icon, iv_radiohall_item_apply,
            iv_radio_finished_bg, iv_raido_check_registration, iv_radio_appointment_time, iv_radio_rendezvous,
            iv_radio_dating_expectations, iv_radio_date_supplement;

    private TextView mTv_radiohall_item_like_number, tv_radiohall_item_nickname, tv_radiohall_item_creat_time,
            tv_radiohall_item_theme, tv_radiohall_item_appointment_time, tv_radiohall_item_dating_city, tv_radiohall_item_hope,
            tv_radiohall_item_supplement, tv_radiohall_item_comment, tv_radiohall_item_myapply_number, tv_radiohall_item_apply,
            tv_stick;
    private LinearLayout ll_user_radio_centent, ll_radiohall_item_like, ll_radiohall_item_comment, ll_radiohall_item_check_registration,
            ll_radiohall_item_apply, ll_radiohall_item_supplement, ll_radio_item;
    private RecyclerView racyclerview_radiohall;

    public MyRadioListAdapter(IDisplay display, int layoutResId) {
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
        iv_radio_finished_bg = helper.getView(R.id.iv_radio_finished_bg);

        iv_raido_check_registration = helper.getView(R.id.iv_raido_check_registration);
        iv_radio_appointment_time = helper.getView(R.id.iv_radio_appointment_time);
        iv_radio_rendezvous = helper.getView(R.id.iv_radio_rendezvous);
        iv_radio_dating_expectations = helper.getView(R.id.iv_radio_dating_expectations);
        iv_radio_date_supplement = helper.getView(R.id.iv_radio_date_supplement);
        ll_radio_item = helper.getView(R.id.ll_radio_item);


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

        //        initOfficialRaido(helper, item);
        mIv_radiohall_item_to_report.setVisibility(View.GONE);
        ll_radiohall_item_check_registration.setVisibility(View.VISIBLE);
        tv_radiohall_item_myapply_number.setText("View registration(" + item.getEnrolled_num() + ")");
        iv_radiohall_item_apply.setImageDrawable(ContextCompat.getDrawable(display.context(), R.mipmap.radiohall_listitem_overradio_textleft_icon));
        tv_radiohall_item_apply.setText("Close registration");
        tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));

        initRadioPhoto(item);

        if (2 == item.getStatus()) {
            //广播已结束
            ll_radio_item.setClickable(false);
            iv_radio_finished_bg.setVisibility(View.VISIBLE);
            tv_radiohall_item_nickname.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            tv_radiohall_item_theme.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            tv_radiohall_item_appointment_time.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            tv_radiohall_item_dating_city.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            tv_radiohall_item_hope.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            tv_radiohall_item_supplement.setTextColor(ContextCompat.getColor(display.context(), R.color.color_cfcfd2));
            mTv_radiohall_item_like_number.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            tv_radiohall_item_comment.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            tv_radiohall_item_myapply_number.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            tv_radiohall_item_apply.setTextColor(ContextCompat.getColor(display.context(), R.color.color_666666));
            iv_radio_appointment_time.setImageResource(R.mipmap.radiohall_listitem_appointment_time_textleft_finished_icon);
            iv_radio_rendezvous.setImageResource(R.mipmap.radiohall_listitem_rendezvous_textleft_finished_icon);
            iv_radio_dating_expectations.setImageResource(R.mipmap.radiohall_listitem_dating_expectations_textleft_finished_icon);
            iv_radio_date_supplement.setImageResource(R.mipmap.radiohall_listitem_date_content_textleft_finished_icon);
            mIv_radiohall_item_like_icon.setImageResource(R.mipmap.radiohall_listitem_ungivelike_icon);
            iv_radiohall_item_comment_icon.setImageResource(R.mipmap.radiohall_listitem_no_comments_icon);
            iv_raido_check_registration.setImageResource(R.mipmap.radiohall_listitem_check_registration_textleft_finished_icon);
            iv_radiohall_item_apply.setImageResource(R.mipmap.radiohall_listitem_overradio_textleft_finished_icon);

        } else if (1 == item.getStatus()) {
            iv_radio_finished_bg.setVisibility(View.GONE);
        }

        ll_radiohall_item_like.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onPraiseClick(helper.getAdapterPosition(), mIv_radiohall_item_like_icon);
                }
            }
        });
        iv_radiohall_item_head.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onAvatarClick(helper.getAdapterPosition());
                }
            }
        });
        ll_radiohall_item_apply.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onApplyClick(helper.getAdapterPosition());
                }
            }
        });
        ll_radiohall_item_comment.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onCommentsClick(helper.getAdapterPosition());
                }
            }
        });
        ll_radiohall_item_check_registration.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onCheckRegistrationClick(helper.getAdapterPosition());
                }
            }
        });
        mIv_radiohall_item_to_report.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mOnRadioClickListener != null) {
                    mOnRadioClickListener.onReportClick(helper.getAdapterPosition(), v);
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
            ll_radiohall_item_check_registration.setVisibility(View.VISIBLE);
            ll_radiohall_item_apply.setVisibility(View.VISIBLE);
        }
        if (1 == item.getUppermost()) {
            tv_stick.setVisibility(View.VISIBLE);
        } else {
            tv_stick.setVisibility(View.GONE);
        }
    }

    public void setOnRadioClickListener(OnRadioClickListener onRadioClickListener) {
        this.mOnRadioClickListener = onRadioClickListener;
    }

}

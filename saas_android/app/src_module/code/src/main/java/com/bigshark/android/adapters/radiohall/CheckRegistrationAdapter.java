package com.bigshark.android.adapters.radiohall;

import android.app.Activity;
import android.support.v4.content.ContextCompat;
import android.view.View;
import android.widget.ImageView;
import android.widget.TextView;

import com.alibaba.fastjson.JSON;
import com.bigshark.android.R;
import com.bigshark.android.activities.home.ViewUserPhotoActivity;
import com.bigshark.android.core.display.IDisplay;
import com.bigshark.android.http.model.radiohall.CheckRegistrationListItemModel;
import com.bigshark.android.http.model.radiohall.CheckRegistrationModel;
import com.bigshark.android.http.model.user.PicsModel;
import com.bigshark.android.http.xutils.CommonRequestParams;
import com.bigshark.android.http.xutils.CommonResponseCallback;
import com.bigshark.android.http.xutils.HttpSender;
import com.bigshark.android.mmkv.MmkvGlobal;
import com.bigshark.android.utils.StringConstant;
import com.bigshark.android.utils.ToastUtil;
import com.chad.library.adapter.base.BaseQuickAdapter;
import com.chad.library.adapter.base.BaseViewHolder;

import org.xutils.image.ImageOptions;
import org.xutils.x;

import java.util.ArrayList;
import java.util.List;

//import com.bumptech.glide.Glide;
//import com.shuimiao.sangeng.nim.session.SessionHelper;
//import com.bigshark.android.Radiohall.param.ConfirmYueRequestBean;

/**
 * @创建者 wenqi
 * @创建时间 2019/5/13 11:23
 * @描述 没有去爱过怎知她美 没有你爱我问怎可脱俗
 */
public class CheckRegistrationAdapter extends BaseQuickAdapter<CheckRegistrationListItemModel, BaseViewHolder> {

    private Activity mActivity;
    private String mRadioId;

    public CheckRegistrationAdapter(Activity activity) {
        super(R.layout.adapter_check_registration_listitem);
        this.mActivity = activity;
    }

    @Override
    protected void convert(BaseViewHolder helper, CheckRegistrationListItemModel item) {
        ImageView iv_check_registration_item_head = helper.getView(R.id.iv_check_registration_item_head);
        helper.setText(R.id.tv_check_registration_item_nickname, item.getNickname());
        helper.setImageDrawable(R.id.iv_check_registration_item_gender, item.getSex() == 1 ? ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_men_icon) : ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_gender_women_icon));
        ImageView iv_check_registration_item_vip = helper.getView(R.id.iv_check_registration_item_vip);
        ImageView iv_check_registration_item_photo = helper.getView(R.id.iv_check_registration_item_photo);
//        Glide.with(attachedActivity).load(item.getImg_url()).into(iv_check_registration_item_photo);
        x.image().bind(
                iv_check_registration_item_photo,
                item.getImg_url(),
                new ImageOptions.Builder()
                        .setUseMemCache(true)//设置使用缓存
                        .build()
        );
        if (1 == item.getSex()) {
            x.image().bind(
                    iv_check_registration_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_men_default_icon)
                            .build()
            );
            if (1 == item.getIsVip()) {
                iv_check_registration_item_vip.setVisibility(View.VISIBLE);
                iv_check_registration_item_vip.setImageDrawable(ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_vip_icon));
            } else {
                iv_check_registration_item_vip.setVisibility(View.GONE);
            }
        } else if (2 == item.getSex()) {
            x.image().bind(
                    iv_check_registration_item_head,
                    item.getAvatar(),
                    new ImageOptions.Builder()
                            .setUseMemCache(true)//设置使用缓存
                            .setLoadingDrawableId(R.drawable.global_avatar_women_default_icon)
                            .build()
            );
            if (1 == item.getIsIdentify()) {
                iv_check_registration_item_vip.setVisibility(View.VISIBLE);
                iv_check_registration_item_vip.setImageDrawable(ContextCompat.getDrawable(mActivity, R.mipmap.radiohall_listitem_certified_icon));
            } else {
                iv_check_registration_item_vip.setVisibility(View.GONE);
            }
        }
        helper.setText(R.id.tv_check_registration_item_creattime, item.getCreated_at());
        helper.setImageDrawable(R.id.iv_check_registration_state, item.getStatus() == 1 ? ContextCompat.getDrawable(mActivity, R.mipmap.check_registration_listitem_have_date) : ContextCompat.getDrawable(mActivity, R.mipmap.check_registration_listitem_to_date));
        TextView tv_check_registration_state = helper.getView(R.id.tv_check_registration_state);
        if (1 == item.getStatus()) {
            tv_check_registration_state.setText("已约");
            tv_check_registration_state.setTextColor(ContextCompat.getColor(mActivity, R.color.color_cfcfd2));
        } else {
            tv_check_registration_state.setText("约Ta");
            tv_check_registration_state.setTextColor(ContextCompat.getColor(mActivity, R.color.color_3e3d3d));
        }
        List<PicsModel> picsModelList = new ArrayList<>();
        PicsModel picsModel = new PicsModel();
        picsModel.setPic_url(item.getImg_url());
        picsModelList.add(picsModel);
        iv_check_registration_item_photo.setOnClickListener(new View.OnClickListener() {

            @Override
            public void onClick(View v) {
                ViewUserPhotoActivity.openIntent(mActivity, JSON.toJSONString(picsModelList), 0);
            }
        });
        helper.getView(R.id.ll_yue_ta).setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                if (mRadioId != null) {
                    confirmYue(item);
                }
            }
        });
    }

    public void setRadioId(String broadcast_id) {
        this.mRadioId = broadcast_id;
    }


    private void confirmYue(CheckRegistrationListItemModel item) {
//        ConfirmYueRequestBean requestBean = new ConfirmYueRequestBean();
//        requestBean.setBroadcast_id(mRadioId);
//        requestBean.setEnroll_id(item.getId());
//        HttpApi.app().confirmYue(attachedActivity, requestBean, new HttpCallback<String>() {
//            @Override
//            public void onSuccess(int code, String message, String data) {
////                SessionHelper.startP2PSession(attachedActivity, item.getAccid(), "");
//            }
//
//            @Override
//            public void onFailed(HttpError error) {
//                ToastUtil.showToast(attachedActivity, error.getErrMessage());
//            }
//        });
        HttpSender.post(new CommonResponseCallback<CheckRegistrationModel>((IDisplay) mActivity) {
            @Override
            public CommonRequestParams createRequestParams() {
                String url = MmkvGlobal.instance().getCacheUrl(StringConstant.SERVICE_URL_CONFIRMYUE_KEY);
                CommonRequestParams requestParams = new CommonRequestParams(url);
                requestParams.setMultipart(true);
                requestParams.addBodyParameter("broadcast_id", mRadioId);
                requestParams.addBodyParameter("enroll_id", item.getId());
                return requestParams;
            }

            @Override
            public void handleUi(boolean isStart) {

            }

            @Override
            public void handleSuccess(CheckRegistrationModel data, int resultCode, String resultMessage) {
                //SessionHelper.startP2PSession(attachedActivity, item.getAccid(), "");
            }

            @Override
            public void handleFailed(int resultCode, String resultMessage) {
                ToastUtil.showToast(mActivity, resultMessage);
            }
        });
    }

}

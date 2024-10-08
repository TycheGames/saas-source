package com.bigshark.android.adapters.radiohall;

import android.content.Context;
import android.support.v7.widget.RecyclerView;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.ImageView;
import android.widget.TextView;

import com.bigshark.android.R;

//import com.shuimiao.sangeng.imagepicker.ImagePicker;
//import com.shuimiao.sangeng.imagepicker.bean.ImageItem;

/**
 * ================================================
 * 作    者：ikkong （ikkong@163.com），修改 jeasonlzy（廖子尧）
 * 版    本：1.0
 * 创建日期：2016/5/19
 * 描    述：
 * 修订历史：微信图片选择的Adapter, 感谢 ikkong 的提交
 * ================================================
 */
public class ImagePickerAdapter extends RecyclerView.Adapter<ImagePickerAdapter.SelectedPicViewHolder> {

    private int maxImgCount;
    private Context mContext;
//    private List<ImageItem> mData;
    private LayoutInflater mInflater;
    private OnRecyclerViewItemClickListener listener;
    private boolean isAdded;   //是否额外添加了最后一个图片

    public interface OnRecyclerViewItemClickListener {
        void onItemClick(View view, int position);
    }

    public void setOnItemClickListener(OnRecyclerViewItemClickListener listener) {
        this.listener = listener;
    }

//    public void setImages(List<ImageItem> data) {
//        mData = new ArrayList<>(data);
//        if (getItemCount() < maxImgCount) {
//            mData.add(new ImageItem());
//            isAdded = true;
//        } else {
//            isAdded = false;
//        }
//        notifyDataSetChanged();
//    }

//    public List<ImageItem> getImages() {
//        //由于图片未选满时，最后一张显示添加图片，因此这个方法返回真正的已选图片
//        if (isAdded)
//            return new ArrayList<>(mData.subList(0, mData.size() - 1));
//        else
//            return mData;
//    }
//
//    public ImagePickerAdapter(Context mContext, List<ImageItem> data, int maxImgCount) {
//        this.mContext = mContext;
//        this.maxImgCount = maxImgCount;
//        this.mInflater = LayoutInflater.from(mContext);
//        setImages(data);
//    }

    @Override
    public SelectedPicViewHolder onCreateViewHolder(ViewGroup parent, int viewType) {
        return new SelectedPicViewHolder(mInflater.inflate(R.layout.list_item_image, parent, false));
    }

    @Override
    public void onBindViewHolder(SelectedPicViewHolder holder, int position) {
        holder.bind(position);
    }

    @Override
    public int getItemCount() {
//        return mData.size();
        return 0;
    }

    public class SelectedPicViewHolder extends RecyclerView.ViewHolder implements View.OnClickListener {

        private ImageView iv_img;
        private TextView tv_photo_state;
        private int clickPosition;

        public SelectedPicViewHolder(View itemView) {
            super(itemView);
            iv_img = (ImageView) itemView.findViewById(R.id.iv_img);
            tv_photo_state = (TextView) itemView.findViewById(R.id.tv_photo_state);
        }

        public void bind(int position) {
            //设置条目的点击事件
            itemView.setOnClickListener(this);
            //根据条目位置设置图片
//            ImageItem item = mData.get(position);
//            if (isAdded && position == getItemCount() - 1) {
//                iv_img.setImageResource(R.mipmap.selector_image_add);
//                clickPosition = ReleaseRadioActivity.IMAGE_ITEM_ADD;
//            } else {
//                ImagePicker.getInstance().getImageLoader().displayImage((Activity) mContext, item.path, iv_img, 0, 0, item.getPic_url());
//                clickPosition = position;
//                if (item.isIs_burn_after_reading() && item.getIs_red_pack()) {
//                    tv_photo_state.setVisibility(View.VISIBLE);
//                    tv_photo_state.setText("阅后即焚&红包照片");
//                } else if (item.isIs_burn_after_reading()) {
//                    tv_photo_state.setVisibility(View.VISIBLE);
//                    tv_photo_state.setText("阅后即焚");
//                } else if (item.getIs_red_pack()) {
//                    tv_photo_state.setVisibility(View.VISIBLE);
//                    tv_photo_state.setText("红包照片");
//                } else {
//                    tv_photo_state.setVisibility(View.GONE);
//                }
//            }
        }

        @Override
        public void onClick(View v) {
            if (listener != null)
                listener.onItemClick(v, clickPosition);
        }
    }
}
package com.bigshark.android.jump.operations.uis;

import com.bigshark.android.dialog.SingleImageDialog;
import com.bigshark.android.jump.JumpOperationBinder;
import com.bigshark.android.jump.base.JumpOperation;
import com.bigshark.android.jump.model.uis.ImageDialogJumpModel;
import com.bigshark.android.database.XutilDatabaseWrapper;
import com.bigshark.android.database.ImageDialogRecordModel;
import com.bigshark.android.utils.StringConstant;

import org.xutils.db.sqlite.WhereBuilder;

import java.util.List;

/**
 * 图片Dialog提示
 */
public class ImageDialogJumpOperation extends JumpOperation<ImageDialogJumpModel> {

    static {
        JumpOperationBinder.bind(
                ImageDialogJumpOperation.class,
                ImageDialogJumpModel.class,
                StringConstant.JUMP_TIP_DIALOG_IMAGE
        );
    }

    @Override
    public void start() {
        ImageDialogJumpModel.LogicParam logic = request.getData().getLogic();
        if (logic == null || logic.getUniqueId() == null) {
            return;
        }

        String uniqueId = logic.getUniqueId();
        int totalCount = logic.getTotalCount();

        XutilDatabaseWrapper xutilDatabaseWrapper = new XutilDatabaseWrapper();

        List<ImageDialogRecordModel> imageDialogBeans = xutilDatabaseWrapper.findAllByWhere(
                ImageDialogRecordModel.class,
                WhereBuilder.b(StringConstant.IMAGE_DIALOG_RECORD_MODEL_TABLE_COLUMN_KEY_UNIQUE_ID, "=", uniqueId)
        );

        if (imageDialogBeans.isEmpty()) {
            ImageDialogRecordModel imageDialogRecordModel = new ImageDialogRecordModel();
            imageDialogRecordModel.setUniId(uniqueId);
            imageDialogRecordModel.setTotalSize(totalCount);
            imageDialogRecordModel.setCurrentShowSize(1);

            xutilDatabaseWrapper.save(imageDialogRecordModel);
            new SingleImageDialog(request.getDisplay(), request.getData().getContent()).start();
        } else {
            ImageDialogRecordModel imageDialogRecordModel = imageDialogBeans.get(0);
            // 弹框可以展示的总次数(若为0或负数展示次数则无限制，否则展示次数有限制)
            if (imageDialogRecordModel.getTotalSize() > 0 && imageDialogRecordModel.getCurrentShowSize() >= imageDialogRecordModel.getTotalSize()) {
                return;
            }

            imageDialogRecordModel.setCurrentShowSize(imageDialogRecordModel.getCurrentShowSize() + 1);
            xutilDatabaseWrapper.update(imageDialogRecordModel);
            new SingleImageDialog(request.getDisplay(), request.getData().getContent()).start();
        }
    }
}

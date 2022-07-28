package com.deepfinch.kyclib.view;

import android.content.Context;
import android.graphics.Canvas;
import android.graphics.Path;
import android.graphics.RectF;
import android.util.AttributeSet;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class UpRoundImageView extends android.support.v7.widget.AppCompatImageView {

    public UpRoundImageView(Context context) {
        super(context);
    }

    public UpRoundImageView(Context context, AttributeSet attrs) {
        super(context, attrs);
    }

    public UpRoundImageView(Context context, AttributeSet attrs, int defStyleAttr) {
        super(context, attrs, defStyleAttr);
    }


    /**
     * 画图
     *
     * @param canvas
     */
    protected void onDraw(Canvas canvas) {
        Path path = new Path();
        int w = this.getWidth();
        int h = this.getHeight();
        /*向路径中添加圆角矩形。radii数组定义圆角矩形的四个圆角的x,y半径。radii长度必须为8*/
        float mRadius = 20;
        int width = getWidth();
        int height = getHeight();
        float ry = height / 2;
        float rx = ry;
        path.addRoundRect(new RectF(0, 0, w, h), rx, ry, Path.Direction.CW);
        canvas.clipPath(path);
        super.onDraw(canvas);
    }
}

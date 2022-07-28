package com.liveness.dflivenesslibrary.view;
/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 **/
public interface ITimeViewBase {
	public void setProgress(float currentTime);
	public void hide();
	public void show();
	public int getMaxTime();
}
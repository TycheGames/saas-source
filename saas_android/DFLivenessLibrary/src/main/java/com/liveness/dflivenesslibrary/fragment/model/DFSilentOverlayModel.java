package com.liveness.dflivenesslibrary.fragment.model;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 */

public class DFSilentOverlayModel {
    private String showHint;
    private int borderColor;

    public DFSilentOverlayModel(String showHint, int borderColor) {
        this.showHint = showHint;
        this.borderColor = borderColor;
    }

    public String getShowHint() {
        return showHint;
    }

    public void setShowHint(String showHint) {
        this.showHint = showHint;
    }

    public int getBorderColor() {
        return borderColor;
    }

    public void setBorderColor(int borderColor) {
        this.borderColor = borderColor;
    }
}

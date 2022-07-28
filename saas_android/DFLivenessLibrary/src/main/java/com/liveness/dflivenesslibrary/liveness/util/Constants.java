package com.liveness.dflivenesslibrary.liveness.util;

/**
 * Copyright (c) 2018-2019 DEEPFINCH Corporation. All rights reserved.
 **/

public class Constants {
    public static int PREVIEW_WIDTH = 1280;
    public static int PREVIEW_HEIGHT = 720;

    public static final String SEQUENCE = "sequence";
    public static final String OUTTYPE = "outType";
    public static final String RESULT = "result";
    public static final String THRESHOLD = "threshold";
    public static final String LOST = "lost";

    // motion value
    public static final String HOLD_STILL = "STILL";
    public static final String MULTIIMG = "multiImg";

    public static final int LIVENESS_SUCCESS = 0x86243331;
    public static final int LIVENESS_TIME_OUT = 0x86243333;
    public static final int DETECT_BEGIN_WAIT = 5000;
    public static final int DETECT_END_WAIT = 5001;
}

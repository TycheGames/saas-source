/*
 * android-spinnerwheel
 * https://github.com/ai212983/android-spinnerwheel
 *
 * based on
 *
 * Android Wheel Control.
 * https://code.google.com/p/android-wheel/
 *
 * Copyright 2011 Yuri Kanivets
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
package com.bigshark.android.widget.spinnerwheel.adapters;

import android.content.Context;

import com.bigshark.android.R;

import java.util.List;


/**
 * The simple Array spinnerwheel adapter
 *
 * @param <T> the element type
 */
public class ListWheelAdapter<T> extends AbstractWheelTextAdapter {

    // items
    private List<T> list;

    /**
     * Constructor
     *
     * @param context the current context
     * @param list   the items
     */
    public ListWheelAdapter(Context context, int itemResource, List<T> list) {
        super(context, itemResource);
        this.list = list;
    }

    /**
     * Constructor
     *
     * @param context the current context
     * @param list   the items
     */
    public ListWheelAdapter(Context context, List<T> list) {
        super(context, R.layout.wheel_city_text_item);
        this.list = list;
    }

    @Override
    public CharSequence getItemText(int index) {
        if (index >= 0 && index < list.size()) {
            T item = list.get(index);
            if (item instanceof CharSequence) {
                return (CharSequence) item;
            }
            return item.toString();
        }
        return null;
    }

    @Override
    public int getItemsCount() {
        return list.size();
    }
}

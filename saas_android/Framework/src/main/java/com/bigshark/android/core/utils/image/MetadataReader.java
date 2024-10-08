/*
 * Copyright (C) 2017 The Android Open Source Project
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

package com.bigshark.android.core.utils.image;

import android.content.Context;
import android.database.Cursor;
import android.os.Bundle;
import android.provider.MediaStore;
import android.support.annotation.Nullable;
import android.support.media.ExifInterface;
import android.webkit.MimeTypeMap;

import java.io.File;
import java.io.FileInputStream;
import java.io.IOException;
import java.util.ArrayList;
import java.util.Collections;
import java.util.HashMap;
import java.util.List;
import java.util.Map;

/**
 * Class providing support for extracting metadata from a file as a
 * {@link Bundle} suitable for use with {@link android.provider.DocumentsContract getDocumentMetadata}.
 * <p>Currently only EXIF data is supported.
 * <p>TODO: Add support for common video and audio types, as well as PDF files.
 * {@hide}
 */
public final class MetadataReader {

    private MetadataReader() {
    }

    private static final String[] DEFAULT_EXIF_TAGS = {
//            ExifInterface.TAG_APERTURE,
            ExifInterface.TAG_COPYRIGHT,
            ExifInterface.TAG_DATETIME,
            ExifInterface.TAG_EXPOSURE_TIME,
            ExifInterface.TAG_FOCAL_LENGTH,
            ExifInterface.TAG_F_NUMBER,
            ExifInterface.TAG_GPS_LATITUDE,
            ExifInterface.TAG_GPS_LATITUDE_REF,
            ExifInterface.TAG_GPS_LONGITUDE,
            ExifInterface.TAG_GPS_LONGITUDE_REF,
            ExifInterface.TAG_IMAGE_LENGTH,
            ExifInterface.TAG_IMAGE_WIDTH,
            ExifInterface.TAG_ISO_SPEED_RATINGS,
            ExifInterface.TAG_MAKE,
            ExifInterface.TAG_MODEL,
            ExifInterface.TAG_ORIENTATION,
            ExifInterface.TAG_SHUTTER_SPEED_VALUE,
    };

    private static final int TYPE_INT = 0;
    private static final int TYPE_DOUBLE = 1;
    private static final int TYPE_STRING = 2;

    private static final Map<String, Integer> TYPE_MAPPING = new HashMap<>();
    public static final String[] MAPPING_EXIF_TAGS;

    static {
        // TODO: Move this over to ExifInterface.java
        // Since each ExifInterface item has a type, and there's currently no way to get the type
        // from the tag, here we identify the tag to the type so that we can call the correct
        // ExifInterface method
        TYPE_MAPPING.put(ExifInterface.TAG_ARTIST, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_BITS_PER_SAMPLE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_COMPRESSION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_COPYRIGHT, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_DATETIME, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_IMAGE_DESCRIPTION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_IMAGE_LENGTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_IMAGE_WIDTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_JPEG_INTERCHANGE_FORMAT, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_JPEG_INTERCHANGE_FORMAT_LENGTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_MAKE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_MODEL, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_ORIENTATION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_PHOTOMETRIC_INTERPRETATION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_PLANAR_CONFIGURATION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_PRIMARY_CHROMATICITIES, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_REFERENCE_BLACK_WHITE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_RESOLUTION_UNIT, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_ROWS_PER_STRIP, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SAMPLES_PER_PIXEL, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SOFTWARE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_STRIP_BYTE_COUNTS, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_STRIP_OFFSETS, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_TRANSFER_FUNCTION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_WHITE_POINT, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_X_RESOLUTION, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_Y_CB_CR_COEFFICIENTS, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_Y_CB_CR_POSITIONING, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_Y_CB_CR_SUB_SAMPLING, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_Y_RESOLUTION, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_APERTURE_VALUE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_BRIGHTNESS_VALUE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_CFA_PATTERN, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_COLOR_SPACE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_COMPONENTS_CONFIGURATION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_COMPRESSED_BITS_PER_PIXEL, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_CONTRAST, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_CUSTOM_RENDERED, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_DATETIME_DIGITIZED, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_DATETIME_ORIGINAL, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_DEVICE_SETTING_DESCRIPTION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_DIGITAL_ZOOM_RATIO, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_EXIF_VERSION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_EXPOSURE_BIAS_VALUE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_EXPOSURE_INDEX, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_EXPOSURE_MODE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_EXPOSURE_PROGRAM, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_EXPOSURE_TIME, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_F_NUMBER, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_FILE_SOURCE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_FLASH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_FLASH_ENERGY, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_FLASHPIX_VERSION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_FOCAL_LENGTH, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_FOCAL_LENGTH_IN_35MM_FILM, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_FOCAL_PLANE_RESOLUTION_UNIT, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_FOCAL_PLANE_X_RESOLUTION, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_FOCAL_PLANE_Y_RESOLUTION, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GAIN_CONTROL, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_ISO_SPEED_RATINGS, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_IMAGE_UNIQUE_ID, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_LIGHT_SOURCE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_MAKER_NOTE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_MAX_APERTURE_VALUE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_METERING_MODE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_NEW_SUBFILE_TYPE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_OECF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_PIXEL_X_DIMENSION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_PIXEL_Y_DIMENSION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RELATED_SOUND_FILE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SATURATION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SCENE_CAPTURE_TYPE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SCENE_TYPE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SENSING_METHOD, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SHARPNESS, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SHUTTER_SPEED_VALUE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_SPATIAL_FREQUENCY_RESPONSE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SPECTRAL_SENSITIVITY, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBFILE_TYPE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBSEC_TIME, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBSEC_TIME_DIGITIZED, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBSEC_TIME_ORIGINAL, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBJECT_AREA, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBJECT_DISTANCE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBJECT_DISTANCE_RANGE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_SUBJECT_LOCATION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_USER_COMMENT, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_WHITE_BALANCE, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_ALTITUDE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_ALTITUDE_REF, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_AREA_INFORMATION, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DOP, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DATESTAMP, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_BEARING, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_BEARING_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_DISTANCE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_DISTANCE_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_LATITUDE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_LATITUDE_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_LONGITUDE, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DEST_LONGITUDE_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_DIFFERENTIAL, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_IMG_DIRECTION, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_IMG_DIRECTION_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_LATITUDE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_LATITUDE_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_LONGITUDE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_LONGITUDE_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_MAP_DATUM, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_MEASURE_MODE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_PROCESSING_METHOD, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_SATELLITES, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_SPEED, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_SPEED_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_STATUS, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_TIMESTAMP, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_TRACK, TYPE_DOUBLE);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_TRACK_REF, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_GPS_VERSION_ID, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_INTEROPERABILITY_INDEX, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_THUMBNAIL_IMAGE_LENGTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_THUMBNAIL_IMAGE_WIDTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_DNG_VERSION, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_DEFAULT_CROP_SIZE, TYPE_INT);
        //I don't know how to represent this. Type is unknown
        //TYPE_MAPPING.put(ExifInterface.TAG_ORF_THUMBNAIL_IMAGE, TYPE_STRING);
        TYPE_MAPPING.put(ExifInterface.TAG_ORF_PREVIEW_IMAGE_START, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_ORF_PREVIEW_IMAGE_LENGTH, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_ORF_ASPECT_FRAME, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RW2_SENSOR_BOTTOM_BORDER, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RW2_SENSOR_LEFT_BORDER, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RW2_SENSOR_RIGHT_BORDER, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RW2_SENSOR_TOP_BORDER, TYPE_INT);
        TYPE_MAPPING.put(ExifInterface.TAG_RW2_ISO, TYPE_INT);

        MAPPING_EXIF_TAGS = TYPE_MAPPING.keySet().toArray(new String[0]);
    }

    private static final String JPG_MIME_TYPE = "image/jpg";
    private static final String JPEG_MIME_TYPE = "image/jpeg";


    /**
     * Returns true if caller can generally expect to get metadata results
     * for the supplied mimetype.
     */
    public static boolean isSupportedMimeType(String mimeType) {
        return JPG_MIME_TYPE.equals(mimeType) || JPEG_MIME_TYPE.equals(mimeType);
    }

    public static Map<String, Object> getMetadata(File image) {
        return getExifData(image, null);
    }

    /**
     * Generic metadata retrieval method that can retrieve any available metadata from a given doc
     * Currently only functions for exifdata
     *
     * @param tags a variable amount of keys to differentiate which tags the user wants
     *             if null, returns a default set of data. See DEFAULT_EXIF_TAGS.
     * @return the bundle to which we add any relevant metadata
     * @throws IOException when the file doesn't exist
     */
    public static Map<String, Object> getMetadata(File image, @Nullable String[] tags) {
        return getExifData(image, tags);
    }

    /**
     * Helper method that is called if getMetadata is called for an image mimeType.
     *
     * @param image the input image File from which to extra data.
     * @param tags  a list of ExifInterface tags that are used to retrieve data.
     *              if null, returns a default set of data. See DEFAULT_EXIF_TAGS.
     */
    private static Map<String, Object> getExifData(File image, @Nullable String[] tags) {
        String mimeType = MetadataReader.getMimeType(image.getAbsolutePath());
//        KLog.d("mimeType:" + mimeType);
        if (!isSupportedMimeType(mimeType)) {
            return new HashMap<>();
        }

        if (tags == null) {
            tags = DEFAULT_EXIF_TAGS;
        }

        FileInputStream stream = null;
        try {
            stream = new FileInputStream(image);
            ExifInterface exifInterface = new ExifInterface(stream);
            Map<String, Object> exif = new HashMap<>(160);
            for (String tag : tags) {
                if (TYPE_MAPPING.get(tag).equals(TYPE_INT)) {
                    int data = exifInterface.getAttributeInt(tag, Integer.MIN_VALUE);
                    if (data != Integer.MIN_VALUE) {
//                    exif.putInt(tag, data);
                        exif.put(tag, data);
                    }
                } else if (TYPE_MAPPING.get(tag).equals(TYPE_DOUBLE)) {
                    double data = exifInterface.getAttributeDouble(tag, Double.MIN_VALUE);
                    if (data != Double.MIN_VALUE) {
//                    exif.putDouble(tag, data);
                        exif.put(tag, data);
                    }
                } else if (TYPE_MAPPING.get(tag).equals(TYPE_STRING)) {
                    String data = exifInterface.getAttribute(tag);
                    if (data != null) {
//                    exif.putString(tag, data);
                        exif.put(tag, data);
                    }
                }
            }
            return exif;
        } catch (IOException e) {
            e.printStackTrace();
            return new HashMap<>();
        } finally {
            if (stream != null) {
                try {
                    stream.close();
                } catch (IOException e) {
                    e.printStackTrace();
                }
            }
        }
    }


    public static String getMimeType(String filePath) {
        String ext = MimeTypeMap.getFileExtensionFromUrl(filePath);
        return MimeTypeMap.getSingleton().getMimeTypeFromExtension(ext);
    }


    /**
     * 获取到相册中所有图片的路径
     * 需要权限：Manifest.permission.READ_EXTERNAL_STORAGE
     */
    public static List<String> getAlbumPaths(Context context) {
        Cursor cursor = null;
        try {
            List<String> albumPaths = new ArrayList<>();
            cursor = context.getContentResolver().query(MediaStore.Images.Media.EXTERNAL_CONTENT_URI, null, null, null, null);
            if (cursor == null || cursor.getCount() <= 0) {
                return albumPaths;
            }

            while (cursor.moveToNext()) {
                byte[] data = cursor.getBlob(cursor.getColumnIndex(MediaStore.Images.Media.DATA));
                String filePath = new String(data, 0, data.length - 1);
                albumPaths.add(filePath);
            }
            return albumPaths;
        } catch (Exception e) {
            e.printStackTrace();
            return Collections.emptyList();
        } finally {
            if (cursor != null && !cursor.isClosed()) {
                cursor.close();
            }
        }
    }

}

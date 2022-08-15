<?php


namespace common\models\enum;


trait TCommon
{
    /**
     * @param array $mapData
     * @return array
     */
    public static function formatForDropdownBoxData(array $mapData): array
    {
        $result = [];

        foreach ($mapData as $key => $datum) {
            array_push($result, [
                'id'    => $key,
                'label' => $datum,
            ]);
        }

        return $result;
    }
}
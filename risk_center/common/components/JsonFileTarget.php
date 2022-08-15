<?php


namespace common\components;

use yii\log\FileTarget;
use yii\log\Logger;


class JsonFileTarget extends FileTarget
{

    /**
     * Formats a log message for display as a string.
     * @param array $message the log message to be formatted.
     * The message structure follows that in [[Logger::messages]].
     * @return string the formatted message
     */
    public function formatMessage($message)
    {
        list($text, $level, $category, $timestamp) = $message;
        $level = Logger::getLevelName($level);

        $traces = [];
        if (isset($message[4])) {
            foreach ($message[4] as $trace) {
                $traces[] = "in {$trace['file']}:{$trace['line']}";
            }
        }
        if(is_object($text)){
            $text = sprintf('%s in %s', $text->getMessage(),$text->getTraceAsString());
        }

        $log = [];
        $log['time'] = date('Y-m-d H:i:s', $timestamp);
        $log['level'] = $level;
        $log['category'] = $category;
        $log['traces'] = $traces;
        $log['text'] = $text;

        return json_encode($log,JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
    }
}
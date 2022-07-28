<?php


namespace common\models\enum\kudos;


use MyCLabs\Enum\Enum;

/**
 * Class NoteIssueType
 * @package common\models\enum\kudos
 *
 * @method static NoteIssueType ISSUED()
 * @method static NoteIssueType RAISED()
 */
class NoteIssueType extends Enum
{
    private const ISSUED = 'ISSUED';
    private const RAISED = 'RAISED';
}
<?php

use yii\helpers\Url;
$route = Yii::$app->requestedRoute;

$this->shownav('manage', 'menu_collection_track_statistics');
$this->showsubmenu(Yii::T('common', 'Tracking statistics'), array(
    [Yii::T('common', 'Collector-Daily tracking'),Url::toRoute('loan-collection-admin-track'),$route == 'collection-statistics/loan-collection-admin-track'],
    [Yii::T('common', 'Collector-cumulative tracking'),Url::toRoute('loan-collection-admin-total'),$route == 'collection-statistics/loan-collection-admin-total'],
    [Yii::T('common', 'Institution-Daily Tracking'),Url::toRoute('loan-collection-outside-track'),$route == 'collection-statistics/loan-collection-outside-track'],
    [Yii::T('common', 'Institution-cumulative tracking'),Url::toRoute('loan-collection-outside-total'),$route == 'collection-statistics/loan-collection-outside-total']
));

?>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.min.js"></script>
<script language="javascript" type="text/javascript" src="<?php echo $this->baseUrl ?>/js/My97DatePicker/WdatePicker.js"></script>
<script src="<?php echo $this->baseUrl; ?>/js/jquery.sumoselect.min.js" type="text/javascript"></script>
<link href="<?php echo $this->baseUrl; ?>/css/sumoselect.min.css" rel="stylesheet" />
<script language="JavaScript">
    $(function () {
        $('.team-select').SumoSelect({ placeholder:'all team'});
    });
</script>

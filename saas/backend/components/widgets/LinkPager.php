<?php

namespace backend\components\widgets;

use yii\helpers\Html;

class LinkPager extends \yii\widgets\LinkPager
{
    protected function renderPageButtons()
    {
        $pageSizeList = [15, 50, 100];
        $pageCount = $this->pagination->getPageCount();
        if ($pageCount < 2 && $this->hideOnSinglePage) {
            return '';
        }

        $buttons = [];
        $currentPage = $this->pagination->getPage();

        // first page
        $firstPageLabel = $this->firstPageLabel === true ? '1' : $this->firstPageLabel;
        if ($firstPageLabel !== false) {
            $buttons[] = $this->renderPageButton($firstPageLabel, 0, $this->firstPageCssClass, $currentPage <= 0, false);
        }

        // prev page
        if ($this->prevPageLabel !== false) {
            if (($page = $currentPage - 1) < 0) {
                $page = 0;
            }
            $buttons[] = $this->renderPageButton($this->prevPageLabel, $page, $this->prevPageCssClass, $currentPage <= 0, false);
        }

        // internal pages
        list($beginPage, $endPage) = $this->getPageRange();
        for ($i = $beginPage; $i <= $endPage; ++$i) {
            $buttons[] = $this->renderPageButton($i + 1, $i, null, false, $i == $currentPage);
        }

        // next page
        if ($this->nextPageLabel !== false) {
            if (($page = $currentPage + 1) >= $pageCount - 1) {
                $page = $pageCount - 1;
            }
            $buttons[] = $this->renderPageButton($this->nextPageLabel, $page, $this->nextPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // last page
        $lastPageLabel = $this->lastPageLabel === true ? $pageCount : $this->lastPageLabel;
        if ($lastPageLabel !== false) {
            $buttons[] = $this->renderPageButton($lastPageLabel, $pageCount - 1, $this->lastPageCssClass, $currentPage >= $pageCount - 1, false);
        }

        // go
        $totalCount = $this->pagination->totalCount;
        $pageLink = $this->pagination->createUrl(false);
        $realCurrentPage = $currentPage + 1;
        $pageSize = $this->pagination->getPageSize();

        // per page select
        $pageSizeStr = "<select style='text-align: center; padding: 0; width: 60px;  line-height: 100%;' id='page-size-select'>";
        foreach ($pageSizeList as $value) {
            $selected = $pageSize == $value ? "selected='selected'" : "";
            $pageSizeStr .= "<option value ='$value' $selected>$value</option>";
        }
        $pageSizeStr .= "</select>";

        $goHtml = <<<goHtml
<li><span>{$pageSizeStr} item per page，total page {$pageCount}，total count {$totalCount}</span></li>
<li>
    <span>to <input type="number" style="text-align: center; padding: 0; width: 60px;  line-height: 100%;" id="page-input", min="1", max="{$pageCount}" value="{$realCurrentPage}" onkeydown="if(event.keyCode==13) {jump();}"> page</span>
</li>
<li><a href="javascript:void(0)" id="go-page">confirm</a></li>

<script>
$("#go-page").click(function(){
            jump();
    })
    
    function jump(){
        var goPage = $('#page-input').val();
            var pageLink = "{$pageLink}";
            if (!goPage) {
                console.log('GO PAGE IS NULL')
                return;
            }
            pageLink = pageLink.replace("&page=1", "&page="+goPage);
            window.location.href=pageLink;
    }
    
    $("#page-size-select").change(function(){
            var pageLink = "{$pageLink}";
            var mt = pageLink.match("per-page=[0-9]{1,}");
            
            if (mt) {
                pageLink = pageLink.replace(mt, "per-page="+$(this).val());
            } else {
                pageLink = pageLink+"&per-page="+$(this).val()
            }
            window.location.href=pageLink;
    })
    
</script>
goHtml;

        $buttons[] = $goHtml;

        return Html::tag('ul', implode("\n", $buttons), $this->options);
    }
}
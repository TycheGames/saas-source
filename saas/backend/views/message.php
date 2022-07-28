<h3>提示</h3>

<div class="infobox">
	<h4 class="<?php echo $messageClassName; ?>"><?php echo $message; ?></h4>
	<p class="marginbot">
		<?php if (empty($url)): ?>
		<script type="text/javascript">
		if(history.length > 0) document.write('<a href="javascript:history.go(-1);" class="lightlink">Click here to return to the previous page</a>');
		</script>
		<?php elseif (is_integer($url)): ?>
            <script type="text/javascript">
                if(history.length > 0) document.write('<a href="javascript:history.go(<?php echo $url;?>);" class="lightlink">Click here to return to the previous page</a>');
            </script>
        <?php else: ?>
		<p class="marginbot">
			<a href="<?php echo $url; ?>" class="lightlink">If your browser does not automatically redirect, please click here</a>
		</p>
		<script type="text/JavaScript">setTimeout("redirect('<?php echo $url; ?>');", 3000);</script>
		<?php endif; ?>
	</p>
</div>

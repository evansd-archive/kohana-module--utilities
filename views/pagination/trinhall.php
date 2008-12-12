<?php
/**
 *  Trinity Hall pagination style (by Mike Thompson and Dave Evans)
 *
 * Extra options:
 *     spread:      How many pages either side of the current page to display (default 5)
 *     hide_count:  Whether to display the total item count beneath the page listing (default FALSE)
 *     noun:        Noun to use in the item count e.g., 'results', 'users', 'files' (default is 'items')
 *                  Either supply plural form and singular will be automatically worked out or an array in the form (plural, singular)
 *
 * CSS suggestion:
 *
 *    .pagination { clear: both; text-align: center;}
 *    .pagination p.last { color: #9f9f9f }
 *    .pagination a:link, .pagination a:visited { border: 1px solid #286d83; color: #fff; padding: 0.22em 0.5em;}
 *    .pagination a:hover, .pagination a:active, .pagination a:focus { background: #9ed5e7; border: 1px solid #000; color: #fff; text-decoration: none; }
 *    .pagination .current { background: none; color: #000; font-weight: bold; margin: 0 0.3em;}
 *    .pagination .next { margin-left: 2em;}
 *    .pagination .previous { margin-right: 2em;}
 *    .pagination .inactive { color: #9f9f9f; padding: 0.28em 0.5em;}
 *    .pagination p.total { color: #9f9f9f; }
 */

 // Default spread if none set
$spread = isset($extras['spread']) ? $extras['spread'] : 5;

// Generate the total item counter
if (empty($extras['hide_count']))
{
	if (isset($extras['noun']))
	{
		if (is_array($extras['noun']))
		{
			$noun = ($total_items != 1) ? reset($extras['noun']) : end($extras['noun']);
		}
		else
		{
			$noun = ($total_items != 1) ? $extras['noun'] : inflector::singular($extras['noun']);
		}
	}
	else
	{
		$noun = ($total_items != 1) ? 'items' : 'item';
	}

	$counter = $total_items.' '.$noun;
}
else
{
	$counter = FALSE;
}

?>

<div class="pagination">
	<?php if($total_pages > 1) {?>
		<p>
		<?php if($previous_page) {?>
			<a class="" href="<?php echo str_replace('{page}', 1, $url);?>" title="First">&lt;&lt;</a>
			<a class="previous" href="<?php echo str_replace('{page}', $previous_page, $url);?>" title="Previous">&lt;</a>
		<?php } else {?>
			<span class="inactive">&lt;&lt;</span>
			<span class="inactive previous">&lt;</span>
		<?php }?>

		<?php
		$pagefrom = $current_page - $spread;
		$pageto = $current_page + $spread;
		if($pagefrom <= 0) $pageto += 1 - $pagefrom;
		if($pageto > $total_pages) $pagefrom -= $pageto - $total_pages;
		$pagefrom = max(1, $pagefrom);
		$pageto = min($total_pages, $pageto);
		?>

		<?php if($pagefrom>1) echo '&#8230;&nbsp;';?>

		<?php foreach(range($pagefrom, $pageto) as $num) {?>
			<?php if($num == $current_page) {?>
				<span class="current"><?=$num;?></span>
			<?php } else {?>
				<a href="<?php echo str_replace('{page}', $num, $url);?>"><?php echo $num;?></a>
			<?php }?>
		<?php }?>

		<?php if($pageto < $total_pages) echo '&nbsp;&#8230;';?>

		<?php if($next_page) {?>
			<a class="next" href="<?php echo str_replace('{page}', $next_page, $url);?>" title="Next">&gt;</a>
			<a href="<?php echo str_replace('{page}', $total_pages, $url);?>" title="Last">&gt;&gt;</a>
		<?php } else {?>
			<span class="inactive next">&gt;</span>
			<span class="inactive">&gt;&gt;</span>
		<?php }?>
		</p>
	<?php }
	if ($counter !== FALSE) {?>
		<p class="total">(<?php echo $counter;?>)</p>
	<?php }?>
</div>

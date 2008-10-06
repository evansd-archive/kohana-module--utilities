<?php
echo '<div id="notices"', isset($class) ? ' class="'.$class.'"' : '', ">\n";

	if (isset($heading)) echo '<h3>', $heading, "</h3>\n";
	
	// if $text doesn't start with a tag we wrap it in a <p>
	if (isset($text)) echo ($text[0] == '<') ? $text : "<p>$text</p>", "\n";
	
	if (isset($list) AND count($list))
	{
		echo "<ol>\n";
	
		foreach($list as $item)
		{
			echo "<li>$item</li>\n";
		}
	
		echo "</ol>\n";
	}
	
echo '</div>';

<?php
echo '<div id="notices"', isset($class) ? ' class="'.$class.'"' : '', ">\n";

	if (isset($headline)) echo '<h3>', $headline, "</h3>\n";
	
	// if $message doesn't start with a tag we wrap it in a <p>
	if (isset($message)) echo ($message[0] == '<') ? $message : "<p>$message</p>", "\n";
	
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

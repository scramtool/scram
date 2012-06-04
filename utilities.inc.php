<?php
function make_global( &$input, $names)
{
	foreach ($names as $name) {
		if (isset( $input[$name]))
		{
			$GLOBALS[$name] =  $input[$name];
		}
	}
}
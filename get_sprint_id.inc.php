<?php
//
//  Copyright (C) 2012 Danny Havenith
//
//  Distributed under the Boost Software License, Version 1.0. (See
//  accompanying file LICENSE_1_0.txt or copy at
//  http://www.boost.org/LICENSE_1_0.txt)
//

/**
 * Including this file will make sure that the global variable $sprint_id is set.
 * 
 */

$sprint_id = 25;
if (isset( $_GET['sprint_id']))
{
	$sprint_id = $_GET['sprint_id'];
}

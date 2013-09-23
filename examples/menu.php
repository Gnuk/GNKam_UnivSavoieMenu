<?php
/*
* Copyright (c) 2013 GNKW & Kamsoft.fr
*
* This file is part of Gnkam Univ Savoie Menu.
*
* Gnkam Univ Savoie Menu is free software: you can redistribute it and/or modify
* it under the terms of the GNU Affero General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Gnkam Univ Savoie Menu is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU Affero General Public License for more details.
*
* You should have received a copy of the GNU Affero General Public License
* along with Gnkam Univ Savoie Menu.  If not, see <http://www.gnu.org/licenses/>.
*/
	require_once(__DIR__ . '/../vendor/autoload.php');
	
	use Gnkam\Crous\Grenoble\Menu\Formalizer;

	##################
	# Example of use #
	##################
	
	# Set headers
	header('Content-Type: application/json');
	
	# Cache link
	$cacheLink = __DIR__ . '/cache';
	
	# Create cache dir if not exists
	if(!is_dir($cacheLink))
	{
		if(!mkdir($cacheLink))
		{
			echo json_encode('error', 'Impossible to create cache');
			return;
		}
	}
	
	# 6 Hours update
	$update = 6 * 60 * 60;
	
	# Formalize Data
	$formalizer = new Formalizer($cacheLink, $update);
	$json = $formalizer->serviceMenu(7);
	
	# Show json
	echo json_encode($json);
?>
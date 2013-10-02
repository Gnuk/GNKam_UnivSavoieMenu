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
namespace Gnkam\Crous\Grenoble\Menu;

use Gnkam\Base\Formalizer as BaseFormalizer;

/**
 * Formalizer class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 23/09/2013
 */
class Formalizer extends BaseFormalizer
{
	
	/**
	* Call service for Menu
	* @param integer $id Menu to call
	* @return array Menu Data
	*/
	public function serviceMenu($id)
	{
		# Check if empty
		$id = intval($id);
		if(empty($id))
		{
			return null;
		}
		return $this->service('menu', $id);
	}
	
	/**
	* Data recuperation for menu
	* @param integer $id Menu to call
	* @return array Menu in array representation
	*/
	protected function menuData($id)
	{
		$reciever = new MenuReceiver();
		return $reciever->getArrayData($id);
	}
}

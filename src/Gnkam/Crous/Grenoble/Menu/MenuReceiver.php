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

use Gnkw\Http\Rest\Client;

/**
 * MenuReceiver class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 17/09/2013
 */
class MenuReceiver
{
	private $client;
	
	/**
	 * MenuReceiver constructor
	 */
	public function __construct()
	{
		$this->client = new Client("http://www.crous-grenoble.fr");
	}
	
	/**
	* Get array for an id
	* @param integer $id Menu Id
	* @return array Menu
	*/
	public function getArrayData($id)
	{
		$id = intval($id);
		
		# Test id
		if(empty($id))
		{
			return null;
		}
		$request = $this->client->get('/rss-menu-'.$id.'.htm');
		$resource = $request->getResource();
		
		# Test Code
		if(!$resource->code(200))
		{
			return null;
		}
		
		# Get XML
		$content = $resource->getContent();
		$content = mb_convert_encoding($content, mb_internal_encoding(),'CP1252');
		$rss = simplexml_load_string($content, 'SimpleXMLElement', LIBXML_NOCDATA);
		libxml_use_internal_errors(true);
		
		# Get all menu
		$items = $rss->channel[0]->item;
		$menus = array();
		foreach($items as $item){
			$description = trim((string) $item->description);
			if($description != 'Restaurant fermé')
			{
				$menu = array();
				$menu['meals'] = array_values(array_filter(array_map('trim',explode('<br />', $description)), 'strlen'));
				$title = trim((string) $item->title);
				
				# Date Calcul
				$dateArray = explode(' ', $title);
				$dateTime = new \DateTime();
				$date = array_map('trim',explode('/', $dateArray[2]));
				$dateTime->setDate($date[2], $date[1], $date[0]);
				$start = clone $dateTime;
				$end = clone $dateTime;
				if($dateArray[1] == 'midi')
				{
					$start->setTime(11, 30);
					$end->setTime(13, 15);
				}
				else
				{
					$start->setTime(18, 30);
					$end->setTime(19, 45);
				}
				$menu['start'] = $start->getTimestamp();
				$menu['end'] = $end->getTimestamp();
				$menu['rss'] = array
				(
					'title' => $title,
					'link' => trim((string) $item->link),
					'pubDate' => trim((string) $item->pubDate)
				);
				$menus[] = $menu;
			}
		}
		return $menus;
	}
}

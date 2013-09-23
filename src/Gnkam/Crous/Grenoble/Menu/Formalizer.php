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

/**
 * Formalizer class
 * @author Anthony <anthony.rey@mailoo.org>
 * @since 23/09/2013
 */
class Formalizer
{

	/**
	* Cache directory
	* @var string
	*/
	private $cache;
	
	/**
	* Know there is a cache
	* @var boolean
	*/
	private $cachingOk = false;
	
	/**
	* Update time in seconds
	* @var integer
	*/
	private $update;
	
	/**
	 * Formalizer constructor
	 * @param string $cache Cache directory
	 * @param integer $update Update time in seconds
	 */
	public function __construct($cache, $update)
	{
		if(is_dir($cache))
		{
			$this->cache = rtrim($cache, '/');
			$this->cachingOk = true;
		}
		
		$this->update = $update;
	}
	
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
		
		# Check for cache
		if(!$this->cachingOk)
		{
			return null;
		}
		
		# Create cache group directory if not exists
		$fileDir = $this->cache . '/menu';
		if(!is_dir($fileDir))
		{
			if(!mkdir($fileDir))
			{
				return null;
			}
		}
		
		# Files to create
		$filePath = $fileDir . '/' . $id . '.json';
		$filePathPending = $filePath . '.lock';
		
		# Initialisation
		$json = array();
		$recreate = false;
		$currentTime = time();
		
		# Test pending
		$pending = $this->testPending($filePathPending);

		# File already exist
		if(is_file($filePath))
		{
			$json = json_decode(file_get_contents($filePath), true);
			if($pending)
			{
				$json['status'] = 'pending';
			}
			else
			{
				if(isset($json['updated']))
				{
					$updateTimeMax = $json['updated'] + $this->update;
					if(time() > $updateTimeMax)
					{
						$recreate = true;
					}
				}
				else
				{
					$recreate = true;
				}
			}
		}
		else
		{
			$recreate = true;
		}
		
		# Recreate file
		if($recreate)
		{
			if($pending AND is_file($filePath))
			{
				$json = json_decode(file_get_contents($filePath), true);
				$json['status'] = 'pending';
			}
			else
			{
				# Create lock file
				file_put_contents($filePathPending, time());
				
				# Receive the menu json data
				$reciever = new MenuReceiver();
				$json['data'] = $reciever->getArrayData($id);
				
				# Set meta menu informations
				$json['menu'] = $id;
				$json['status'] = 'last';
				$json['updated'] = time();
				$json['date'] = time();
				
				# Put it in a string
				$string = json_encode($json);
				
				# Test data
				if(!empty($string) AND count($json['data']) > 0)
				{
					file_put_contents($filePath, $string);
				}
				else
				{
					# Error case (example : impossible to contact ADE)
					if(is_file($filePath))
					{
						# Old file exist : send old file
						$json = json_decode(file_get_contents($filePath), true);
						$json['status'] = 'old';
						$json['updated'] = time() - $locktimeup;
						$string = json_encode($json);
						file_put_contents($filePath, $string);
					}
					else
					{
						# Send error
						$json = array('error' => 'resource get failure');
					}
				}
				# Remove lock file
				unlink($filePathPending);
			}
		}
		return $json;
	}
	
	/**
	* Test if service is lockeb by another call
	* @param string $file_Lockfile path
	*/
	public function testPending($file)
	{
		$locktimeup = $this->update/2;
		if(is_file($file))
		{
			$lockTimeMax = file_get_contents($file) + $locktimeup;
			if($currentTime > $lockTimeMax)
			{
				unlink($file);
			}
			else
			{
				return true;
			}
		}
		return false;
	}
}

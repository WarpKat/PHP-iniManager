<?php
/**
 *	iniManager PHP Class v0.0.1
 *	A VERY simple INI manager.
 *	
 *	This is a VERY simple INI management class that doesn't directly help you
 *	edit the INI files.  Instead, this produces a workable array of the INI
 *	for you to modify in memory before committing it, either by display or a
 *	direct file write operation - or both!
 *
 *	When you read in the INI file to a variable, it will manifest as a
 *	multidimensional array.  The reason for this is simple:  the INI spec
 *	is very informal and some software developers utilize it in the dumbest
 *	ways, like Epic's Unreal Engine:  they define multiple directives named
 *	the same and some INI class handlers I tried had a problem with this and
 *	made most of those lines disappear.  This is why I wrote mine the way I
 *	did - a very generic and general INI reader and writer.  Modification takes
 *	place OUTSIDE of the class until you're ready to commit your changes either
 *	using the make_ini() or write_ini() function.  Just make sure you make a
 *	backup file before utilizing the write_ini() function, otherwise you'll end
 *	up screwing the pooch.
 *
 *	If you have a problem with the way I wrote this, modify it yourself or make
 *	your own.  This works for me in managing my wife's Killing Floor 2 servers.
 *
 *	Copyright (c) 2019 NoIntegrity.Org
 *
 * @category   INI
 * @package    iniManager
 * @copyright  Copyright (c) 2019 NoIntegrity.Org (https://www.nointegrity.org)
 * @license    GNU GPLv3
 * @version    0.0.1
 * @author	Armando Ortiz <abortizjr@gmail.com>
 */
 
error_reporting(0);

class iniManager
{
	// Define this before proceeding to call functions.
	public $iniManager_INIFile = "";

	function read_ini()
	{
		// Allocate the array to read in the INI file.
		$myInitResult = array();

		// Does the file exists and can we read it? 
		if( file_exists( $this->iniManager_INIFile ) && is_readable( $this->iniManager_INIFile ) ) 
		{
			$myInitResult = file( $this->iniManager_INIFile );
			
			foreach( $myInitResult as $key=>$line )
			{
				$thisLine = trim( $line );
				if( $thisLine != "" )
				{
					if( substr( $thisLine, 0, -strlen( $thisLine ) + 1 ) == "[" && substr( $thisLine, -1 ) == "]" )
					{
						$mySectionName = substr( $thisLine, 1, -1 );
						$myReadINI[ $mySectionName ] = array();
					}
						else
					{
						$cfgLine = explode( "=", $thisLine, 2 );
						$myReadINI[ $mySectionName ][][ $cfgLine[ 0 ] ] = $cfgLine[ 1 ];
					}
				}
			}
			return $myReadINI;
		}
			else
		{
			die( "Unable to continue - INI file is unreadable or doesn't exist.\n\n" );
		}
	}
	
	/*
		This will return an array of all the sections you have, along with
		the current array key.
	*/
	function list_sections()
	{
		if( is_array( $this->read_ini() ) )
		{
			return array_keys( $this->read_ini() );
		}
			else
		{
			die( "No proper INI set provided.\n\n" );
		}
	}
	
	/*
		Spits out an INI file without overwriting the current one.  Call this
		so you can see what the new INI will look like.  Just feed it your
		changed INI array to see the results.
	*/
	function make_ini( $newINI )
	{
		if( is_array( $newINI ) )
		{
			foreach( $newINI as $newINISection => $newINIKeys )
			{
				echo "[" . $newINISection . "]\n";
				foreach( $newINIKeys as $thisDirectiveKey => $thisDirectiveArray )
				{
					foreach( $thisDirectiveArray as $thisDirective => $thisDirectiveValue )
					{
						echo "$thisDirective=$thisDirectiveValue\n";
					}
				}
				echo "\n";
			}
		}
			else
		{
			die( "New INI data passed is not a valid array.\n\n" );
		}
	}

	/*
		This is basically the same function as make_ini(), but it overwrites
		the original INI that was fed to read_ini().
	*/
	function write_ini( $newINI )
	{
		if( is_array( $newINI ) )
		{
			$fp = fopen( $this->iniManager_INIFile, 'w' );
			foreach( $newINI as $newINISection => $newINIKeys )
			{
				fwrite( $fp, "[" . $newINISection . "]\n" );
				foreach( $newINIKeys as $thisDirectiveKey => $thisDirectiveArray )
				{
					foreach( $thisDirectiveArray as $thisDirective => $thisDirectiveValue )
					{
						fwrite( $fp, "$thisDirective=$thisDirectiveValue\n" );
					}
				}
				fwrite( $fp, "\n" );
			}
			fclose( $fp );
			
			return 0;
		}
			else
		{
			die( "New INI data passed is not a valid array.\n\n" );
		}
	}
}

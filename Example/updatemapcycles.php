<?php

/*
	Procedure:
	- Stop the servers.
	- Ensure the 'currentcycle.txt' file is up to date with the filenames of the maps we want.
	- Modify the $myINIFile entry to the correct INI file for the server we want updated.
	- Run the script:  php updatemapcycles.php
	- Start the servers when done.
*/



// Include the INI management class
include( "../iniManager.class.php" );

// Which INI file we're working with.
$myINIFile = "LinuxServer-KFGame.ini"; // Full path to the gameserver's LinuxServer-KFGame.ini file

// The current map cycle we want.
$currentMapCycleFile = file( "currentcycle.txt" );

// Maps we don't want map definitions for (to prevent them from showing up in the web admin)
$deletedMapsFile = file( "deletedmaps.txt" );

// Make a backup of the INI file we're working with so we don't hose ourselves
copy( $myINIFile, $myINIFile . "-" . date( "YmdHis" ) );

// Instantiate the INI file.
$myKFINI = new iniManager();
$myKFINI->iniManager_INIFile = $myINIFile;
$prodINIFile = $myKFINI->read_ini();  // Read in the INI file into a modifiable array

// Create the new GameMapCycles value.
$newINIMapCycle = "(Maps=(";
foreach( $currentMapCycleFile as $key => $val )
{
        $map = trim( $val );
        $newINIMapList .= "\"$map\",";
}
$newINIMapList = substr( $newINIMapList, 0, -1 );
$newINIMapCycle .= $newINIMapList . "))";

// Update the GameMapCycles directive in KFGame.KFGameInfo
foreach( $prodINIFile[ "KFGame.KFGameInfo" ] as $directiveKey=>$directiveArr)
{
	if( array_key_exists( "GameMapCycles", $directiveArr ) )
	{
		$prodINIFile[ "KFGame.KFGameInfo" ][ $directiveKey ][ "GameMapCycles" ] = $newINIMapCycle;
	}
}

// Look for existing mapfile definitions.  If they don't exist, add them.
foreach( $currentMapCycleFile as $currentMapKey => $currentMapName )
{
	$foundSection = 0;
	$mapName = substr( trim( $currentMapName ), 0, -4 );
	$listSections = $myKFINI->list_sections();
	foreach( $listSections as $sectionKey=>$sectionName )
	{
		if( $sectionName == "$mapName KFMapSummary" )
		{
			++$foundSection;
		}
	}
	if( $foundSection == 0 )
	{
		echo "Adding $mapName...\n";
		$prodINIFile[ "$mapName KFMapSummary" ][][ "MapName" ] = $mapName;
	}
}

// Look for maps we DON'T want to be selected in the web admin.
foreach( $deletedMapsFile as $currentMapKey => $currentMapName )
{
	$foundSection = 0;
	$mapName = substr( trim( $currentMapName ), 0, -4 );
	$listSections = $myKFINI->list_sections();
	foreach( $listSections as $sectionKey=>$sectionName )
	{
		if( $sectionName == "$mapName KFMapSummary" )
		{
			++$foundSection;
		}
	}
	
	if( $foundSection > 0 )
	{
		echo "Removing $mapName...\n";
		unset( $prodINIFile[ "$mapName KFMapSummary" ] );
	}
}


// Spit out a new INI file.
$myKFINI->make_ini( $prodINIFile );

// Write a new INI file directly replacing the one from input.
//$myKFINI->write_ini( $prodINIFile );


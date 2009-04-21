<?php
/**
 *  SVN: $Id$
 *
 *  @package         PostCalendar 
 *  @lastmodified    $Date$ 
 *  @modifiedby      $Author$ 
 *  @HeadURL	       $HeadURL$ 
 *  @version         $Revision$ 
 *  
 *  PostCalendar::PostNuke Events Calendar Module
 *  Copyright (C) 2002  The PostCalendar Team
 *  http://postcalendar.tv
 *  Copyright (C) 2009  Sound Web Development
 *  Craig Heydenburg
 *  http://code.zikula.org/soundwebdevelopment/
 *  
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *  
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 *  To read the license please read the docs/license.txt or visit
 *  http://www.gnu.org/copyleft/gpl.html
 *
 */

// grab the form variables
$tplview     = FormUtil::getPassedValue('tplview');
$viewtype    = FormUtil::getPassedValue('viewtype');
$eid         = FormUtil::getPassedValue('eid');
$Date        = FormUtil::getPassedValue('Date');
$print       = FormUtil::getPassedValue('print');
$uid         = pnUserGetVar('uid');
$pc_username = FormUtil::getPassedValue('pc_username');

$output = new pnHTML();
$output->SetInputMode(_PNH_VERBATIMINPUT);
if(!pnModAPILoad('postcalendar','user')) { die('Could not load PostCalendar user API'); }
$theme = pnUserGetTheme();
if(!pnThemeLoad($theme)) { die('Could not load theme'); }

$output->Text('<html><head>');
$output->Text("<title>".pnConfigGetVar('sitename').' :: '.pnConfigGetVar('slogan')."</title>\n");
$output->Text('<link rel="StyleSheet" href="themes/'.$theme.'/style/styleNN.css" type="text/css" />');
$output->Text('<style type="text/css">@import url("themes/'.$theme.'/style/style.css"); </style>');
$output->Text('</head>');
$output->Text('<body bgcolor="#ffffff">');

// setup our cache id
$cacheid = md5($Date.$viewtype.$tplview._SETTING_TEMPLATE.$eid.$print.$uid.$pc_username.$theme);
// display the correct view
switch($viewtype) {
    case 'details' :
        $output->Text(pnModAPIFunc('PostCalendar','user','eventDetail',array('eid'=>$eid,
																		     'Date'=>$Date,
																		     'print'=>$print,
																		     'cacheid'=>$cacheid)));
		break;
    default :
        $output->Text(pnModAPIFunc('postcalendar','user','buildView',array('Date'=>$Date,
																		  'viewtype'=>$viewtype,
																		  'cacheid'=>$cacheid)));
        break;   
}

$output->Text(postcalendar_footer());
$output->Text('</body></html>');
$output->PrintPage();
?>
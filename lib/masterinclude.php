<?php
/*
This file is part of VCMS.

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA
*/

require_once('own/restrictableelement.class.php'); //has to be first
require_once('own/accessrestriction.class.php');
require_once('own/auth.class.php');
require_once('own/calendar.class.php');
require_once('own/cronjobs.class.php');
require_once('own/db.class.php');
require_once('own/dependency.class.php');
require_once('own/event.class.php');
require_once('own/form.class.php');
require_once('own/genericstorage.class.php');
require_once('own/global.class.php');
require_once('own/image.class.php');
require_once('own/include.class.php');
require_once('own/member.class.php');
require_once('own/menu.class.php');
require_once('own/menurenderer.class.php');
require_once('own/modulehandler.class.php');
require_once('own/page.class.php');
require_once('own/securitymanager.class.php');
require_once('own/string.class.php');
require_once('own/table.class.php');
require_once('own/time.class.php');
require_once('own/association.class.php');
require_once('own/icalendar.class.php');

require_once('thirdparty/PasswordHash.php');

require_once('initialize.php');
?>
Statusengine  - the missing event broker
============

Statusengine


Requirements
--------------
- **Nagios 4** or **Naemon**
- MySQL server
- PHP 5.4 or greater
- Ubuntu 14.04 LTS

Installation
--------------
1. Clone repository
 chmod +x install.sh
 ./install.sh

2. Set your username and passwort of MySQL server in /opt/statusengine/cakephp/app/Config/database.php

3. Start Statusengine in legacy mode:
 /opt/statusengine/cakephp/app/Console/cake statusengine_legacy -w

Licence
--------------
Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>

Statusengine is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation in version 2

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
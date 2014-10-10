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
```bash
chmod +x install.sh
./install.sh
```
2. Set your username and password of MySQL server in /opt/statusengine/cakephp/app/Config/database.php
```php
	public $legacy = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'nagios',
		'password' => '12345',
		'database' => 'nagios',
		'prefix' => 'nagios_',
		//'encoding' => 'utf8',
	);
```
3. Create database (using CakePHP shell)
```bash
/opt/statusengine/cakephp/app/Console/cake schema update --plugin Legacy --file legacy_schema.php --connection legacy
```

4. Start Statusengine in legacy mode:
```bash
/opt/statusengine/cakephp/app/Console/cake statusengine_legacy -w
```

Migrate to Statusengine
--------------
1. Clone repository
```bash
chmod +x install.sh
./install.sh
```
2. Set your username and password of MySQL server in /opt/statusengine/cakephp/app/Config/database.php
```php
	public $legacy = array(
		'datasource' => 'Database/Mysql',
		'persistent' => false,
		'host' => 'localhost',
		'login' => 'nagios',
		'password' => '12345',
		'database' => 'nagios',
		'prefix' => 'nagios_',
		//'encoding' => 'utf8',
	);
```

3. Upgrade database with CakePHP schema shell:
```bash
/opt/statusengine/cakephp/app/Console/cake schema update --plugin Legacy --file legacy_schema.php --connection legacy
```
4. Start Statusengine in legacy mode:
```bash
/opt/statusengine/cakephp/app/Console/cake statusengine_legacy -w
```

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
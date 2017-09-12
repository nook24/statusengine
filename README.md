# Statusengine 2 (Old Stable)
This Version of Statusengine is marked as old stable. You can still use it, but take a look at [Version 3](https://github.com/statusengine) or the [FAQ](https://github.com/nook24/statusengine/issues/41).

[Visit the project page](https://www.statusengine.org) for more information.


# Statusengine  - the missing event broker

Statusengine is a drop in replacement for NDOutils and it is able to use the same
database schema. Statusengine uses [gearmand](https://github.com/gearman) as Queueing engine,
so your MySQL database will not slow down the Nagios/Naemon core.

Additionally Statusengine is worker based. If your system grows and you need to process
more data, you can simply increase the number of worker processes.

To make your data visible Statusengine comes with a responsive web interface which
allows you to submit commands and provides a nice way to process the data with external
scripts by quering the HTTP API and append the url with **.json** or **.xml** extension.

Statusengine is modular, so you can use just the parts you need!

[Visit the project page](https://www.statusengine.org/oldstable) for more information.

## Features
- Worker based Nagios/Naemon event data processor
- Based on MySQL
- Json based communication
- Automatic database schema updates
- Responsive Web Interface
- Processing of performance data (RRDTool and Graphite)
- Full UTF-8 support
- In memory engine
- Modular
- Tested on PHP5 and PHP7!

## Requirements
- **Nagios 4** or **Naemon**
- MySQL server
- PHP 5.4 or greater
- Ubuntu 14.04 LTS

Looking for other Ubuntu or Debian version?[Take a look at the supported operating systems](https://www.statusengine.org/documentation.php#supported-os) for more information.

## Installation

Check the documentation for the [installation guide](https://statusengine.org/getting_started.php#installation)

1) Clone repository
```bash
chmod +x install.sh
./install.sh
```

2) Set your username and password of MySQL server in /opt/statusengine/cakephp/app/Config/database.php
```php
        public $legacy = array(
                'datasource' => 'Database/Mysql',
                'persistent' => false,
                'host' => 'localhost',
                'login' => 'naemon',
                'password' => '12345',
                'database' => 'naemon',
                'prefix' => 'naemon_',
                'encoding' => 'utf8',
        );
```

3) Create database (using CakePHP shell) MyISAM
```bash
/opt/statusengine/cakephp/app/Console/cake schema update --plugin Legacy --file legacy_schema.php --connection legacy
```
or:

3) Create database InnoDB **(Recommended!)**
```bash
/opt/statusengine/cakephp/app/Console/cake schema update --plugin Legacy --file legacy_schema_innodb.php --connection legacy
```

4) Change path to your nagios.cfg / naemon.cfg in /opt/statusengine/cakephp/app/Config/Statusengine.php if different on your system
```php
'coreconfig' => '/etc/naemon/naemon.cfg',
```

5) Start Statusengine in legacy mode (forground):
```bash
/opt/statusengine/cakephp/app/Console/cake statusengine_legacy -w
```
or

5) Start Statusengine in legacy mode (background):
```bash
service statusengine start
```
Check the documentation for the [migration guide](https://statusengine.org/getting_started.php#migration)

## Tested with
- Naemon 0.8.0
- Naemon 1.0.3
- Naemon master (development)
- Nagios 4.0.8
- Nagios 4.1.1
- mod_gearman
- NagVis
- MySQL
- MariaDB
- PHP5
- PHP7

## Changelog
**2.1.3**
Resolve missing notifications in database - again
The queues `statusngin_notifications` and `statusngin_contactnotificationdata` are no longer in
use by StatusengineLegacyShell.
All data is parsed out of `statusngin_contactnotificationmethod`

**2.1.2**
Resolve missing notifications in database

**2.1.1**
Resolve Gab in servicestatus_id due to MySQL behavior [#40](https://github.com/nook24/statusengine/issues/40)

**2.1.0**
- Add method for bulk insert - Many thanks to [dhoffend](https://github.com/dhoffend)
- Add broker option gearman_server_addr  - Many thanks to [mjameswh](https://github.com/mjameswh)
- Fixed typos in Statusengine Web Interface - Many thanks to [BlangTech](https://github.com/BlangTech)
- Update Makefile


**2.0.5**
- Fix parsing of negative performance data

**2.0.4**
- Add broker option enable_ochp
- Add broker option enable_ocsp
- Add broker option use_object_data
- Fix that objects will be dumped if use_process_data=0 and use_object_data=1

**2.0.3**
- Add index for object_id in history tables

**2.0.2**
- Fix buildProcessPerfdataCache on low loaded systems

**2.0.1**
- MySQL query improvements [#19](https://github.com/nook24/statusengine/issues/19)
- Fixed display_name for Graphite
- Add composer.json
- Refactor comment and comment history entries [Require database schema update!](https://statusengine.org/documentation.php#update-statusengine)
- Add [broker options](https://statusengine.org/documentation.php#broker-options) to define which data should be transferred to the gearman queues - Many thanks to [jackdown](https://github.com/jackdown)

**2.0.0**
- Update to CakePHP Version 2.8.2
- Add support for PHP7
- Add support for various [debian based distributions](https://statusengine.org/documentation.php#supported-os)
- Add support for Graphite
- Add new responsive theme based on [AdminLTE](https://github.com/almasaeed2010/AdminLTE)
- Fixed hard and soft state display issue for hosts
- Fixed strange URL issue
- Better external command success messages for mobile devices
- Usage of CakeResponse::file() to transmit RRDTool-Graphs PNGs
- Moved version numbers to app/Config/bootstrap.php
- App name can be set now over [Interface.php](https://github.com/nook24/statusengine/blob/master/cakephp/app/Config/Interface.php)
- Prepare for Naemon >= 1.0.4
- Add missing closing form tag for search bar
- Fix display of acknowledgements
- Fix SQL schema for host and service dependencies

**1.6.0**
- Resolve issue with orphaned child processes [Issue 14](https://github.com/nook24/statusengine/issues/14)
- Remove /var/log/statusengine.log [LogfileTask.php] and use syslog instead [Issue 15](https://github.com/nook24/statusengine/issues/15)
- Refactored performance data parser
- Add test for performance data parser
- Set own cache prefix for the CLI app to avoid permission issues
- Add demo mode for Statusengine Web Interface

**1.5.3**
- Add Pull-To-Refresh to the web interface for mobile devices

**1.5.2**
- Improved performance of StatusengineLegacyShell (GEARMAN_WORKER_NON_BLOCKING)

**1.5.1**
- Fixed "MySQL has gone away" crashes of StatusengineLegacyShell

**1.5.0**
- Add responsive web interface

**1.4.1**
- Add support for Naemon configuration process_performance_data for each service

**1.4.0**
- Add native performance data processor
- Add mod_perfdata (performance data processor for mod_german)

**1.3.0**
- Multithreading for Servicestatus

**1.2.0**
- Add in memory engine

**1.0.1**
- First stable version of Statusengine


## Licence

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


#!/bin/bash
#**********************************************************************************
#
#    #####
#   #     # #####   ##   ##### #    #  ####  ###### #    #  ####  # #    # ######
#   #         #    #  #    #   #    # #      #      ##   # #    # # ##   # #
#    #####    #   #    #   #   #    #  ####  #####  # #  # #      # # #  # #####
#         #   #   ######   #   #    #      # #      #  # # #  ### # #  # # #
#   #     #   #   #    #   #   #    # #    # #      #   ## #    # # #   ## #
#    #####    #   #    #   #    ####   ####  ###### #    #  ####  # #    # ######
#
#                            the missing event broker
#
# --------------------------------------------------------------------------------
#
# Copyright (c) 2014 - present Daniel Ziegler <daniel@statusengine.org>
#
# This program is free software; you can redistribute it and/or
# modify it under the terms of the GNU General Public License
# as published by the Free Software Foundation in version 2
# 
# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with this program; if not, write to the Free Software
# Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
#
#**********************************************************************************

which lsb_release > /dev/null
if [ $? -ne 0 ]; then
	echo "lsb_release is missing on your system! Please run 'apt-get install lsb-release' first"
	exit 1
fi

set -e

DISTRIBUTOR=$(lsb_release -si)
CODENAME=$(lsb_release -sc)

packages="gearman-job-server libgearman-dev gearman-tools uuid-dev php5-gearman php5-cli php5-dev libjson-c-dev manpages-dev build-essential libglib2.0-dev"
compiler="-ljson-c"

supportedVersion="false"

if [ $DISTRIBUTOR = "Debian" ] || [ $DISTRIBUTOR == "Raspbian" ]; then
	if [ $CODENAME = "wheezy" ]; then
		packages="gearman-job-server libgearman6 libgearman-dev gearman-tools uuid-dev php5-cli php5-dev libjson0-dev manpages-dev build-essential"
		compiler=" -ljson -DDEBIAN7"
		supportedVersion="true"
	fi
	
	if [ $CODENAME = "jessie" ]; then
		supportedVersion="true"
	fi
	
	if [ $supportedVersion = "false" ]; then
		echo "###########################"
		echo "This installer only support Debian Wheezy and Jessie"
		echo "Check out https://statusengine.org/documentation.php#advanced-installation"
		echo "###########################"
	fi
	
fi

if [ $DISTRIBUTOR = "Ubuntu" ]; then
	if [ $CODENAME = "trusty" ]; then
		supportedVersion="true"
	fi
	
	if [ $CODENAME = "xenial" ]; then
		supportedVersion="true"
	fi
	
	if [ $supportedVersion = "false" ]; then
		echo "###########################"
		echo "This installer only support Ubuntu 14.04 and Ubuntu 16.04"
		echo "Check out https://statusengine.org/documentation.php#advanced-installation"
		echo "###########################"
		supportedVersion="false"
	fi
fi

if [ $supportedVersion = "false" ]; then
	echo "Sorry your OS is not supported by the installer."
	echo "Read https://statusengine.org/documentation.php#advanced-installation for manual installation instructions"
	exit 1;
fi

apt-get update
apt-get install -y $packages

cd statusengine/src
LANG=C gcc -shared -o statusengine-naemon.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman $compiler -DNAEMON;
LANG=C gcc -shared -o statusengine-naemon-1-0-5.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman $compiler -lglib-2.0 -I/usr/include/glib-2.0 -I/usr/lib/x86_64-linux-gnu/glib-2.0/include -lglib-2.0 -DNAEMON105
LANG=C gcc -shared -o statusengine-nagios.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman $compiler -DNAGIOS;

# Naemon master branch
# apt-get install libglib2.0-dev
# LANG=C gcc -shared -o statusengine-naemon-master.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c -lglib-2.0 -I/usr/include/glib-2.0 -I/usr/lib/x86_64-linux-gnu/glib-2.0/include -lglib-2.0 -DNAEMONMASTER

mkdir -p /opt/statusengine
cp statusengine-naemon.o /opt/statusengine/
cp statusengine-nagios.o /opt/statusengine/
cd ../../
cp -r cakephp /opt/statusengine/
cp etc/init.d/statusengine /etc/init.d/statusengine
cp etc/init.d/mod_perfdata /etc/init.d/mod_perfdata
chmod +x /etc/init.d/statusengine
chmod +x /etc/init.d/mod_perfdata
chmod +x /opt/statusengine/cakephp/app/Console/cake
mkdir -p /opt/statusengine/cakephp/app/tmp
chown www-data:www-data /opt/statusengine/cakephp/app/tmp -R

echo "###########################"
echo "Installation done..."
echo ""

echo "For Naemon <= 1.0.3 add the following line to your naemon.cfg"
echo "broker_module=/opt/statusengine/statusengine-naemon.o"
echo ""

echo "For Naemon  1.0.5 add the following line to your naemon.cfg"
echo "broker_module=/opt/statusengine/statusengine-naemon-1-0-5.o"
echo ""

echo "For Naemon master branch add the following line to your naemon.cfg (development)"
echo "broker_module=/opt/statusengine/statusengine-naemon-master.o"
echo ""

echo "For Nagios 4 add the following line to your nagios.cfg"
echo "broker_module=/opt/statusengine/statusengine-nagios.o"
echo ""

if [ $DISTRIBUTOR = "Debian" ]; then
	if [ $CODENAME = "wheezy" ]; then
		echo "###########################"
		echo "Debian wheezy is not 100% supported by this installer due to missing packages in the package manager"
		echo "You need to install php5-gearman manually!"
		echo "Check out https://statusengine.org/documentation.php#advanced-installation"
		echo ""
		echo "Statusengine Event Broker Module is read to use right now!"
		echo "But to use the PHP part of Statusengine, you need to install the php extension"
		echo "Download the following archiv for Debian Wheezy"
		echo "wget https://pecl.php.net/get/gearman-1.0.3.tgz"
		echo "###########################"
	fi
fi

echo ""
echo "Don't forget to set your MySQL credentials in /opt/statusengine/cakephp/app/Config/database.php"

echo ""
echo "Read https://statusengine.org/getting_started.php#installation to see how to continue."

echo ""
echo "Have fun :)"



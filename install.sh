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

if grep -q DISTRIB_CODENAME=trusty /etc/lsb-release; then
	apt-get update
	apt-get install gearman-job-server libgearman-dev gearman-tools uuid-dev php5-gearman php5 php5-cli php5-dev libjson-c-dev manpages-dev build-essential
	cd src
	LANG=C gcc -shared -o statusengine.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c
	mkdir -p /opt/statusengine
	cp statusengine.o /opt/statusengine/
	echo "Installation done..."
	echo "Set broker_module=/opt/statusengine/statusengine.o in your config"
	echo "Set the right MySQL username and passwort in cakephp/app/Config/database.php"
	cd /opt/statusengine
else
	echo "This installer only support Ubuntu 14.04 LTS (trusty)"
fi
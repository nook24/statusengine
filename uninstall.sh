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


apt-get remove gearman-job-server libgearman-dev gearman-tools uuid-dev php5-gearman php5 php5-cli php5-dev libjson-c-dev manpages-dev build-essential

rm -r /opt/statusengine
rm /etc/init.d/statusengine

echo -e "\033[0;34mRemove broker_module=/opt/statusengine/statusengine.o in your config\033[0m"



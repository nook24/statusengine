#!/bin/bash

#this is only for testing! 
set -e
LANG=C gcc -shared -o statusengine.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c
/etc/init.d/nagios restart
tail -f -n 25 /opt/nagios/var/nagios.log

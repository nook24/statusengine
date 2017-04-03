all: bin/nagios/statusengine.o bin/naemon/statusengine.o

bin/nagios/statusengine.o: statusengine.c
	mkdir -p bin/nagios
	LANG=C gcc -DNAGIOS -shared -o "$@" -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c

bin/naemon/statusengine.o: statusengine.c
	mkdir -p bin/naemon
	LANG=C gcc -DNAEMON -shared -o "$@" -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c


bin/naemon/statusengine-1-0-5.o: statusengine.c
	mkdir -p bin/naemon
	LANG=C gcc -DNAEMON105 -shared -o "$@" -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c -lglib-2.0 -I/usr/include/glib-2.0 -I/usr/lib/x86_64-linux-gnu/glib-2.0/include -lglib-2.0

clean:
	rm -f statusengine.o

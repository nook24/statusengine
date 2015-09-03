all: bin/nagios/statusengine.o bin/naemon/statusengine.o

bin/nagios/statusengine.o: statusengine.c
	mkdir -p bin/nagios
	LANG=C gcc -DNAGIOS -shared -o "$@" -fPIC -D_GNU_SOURCE -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c

bin/naemon/statusengine.o: statusengine.c
	mkdir -p bin/naemon
	LANG=C gcc -DNAEMON -shared -o "$@" -fPIC -D_GNU_SOURCE -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c

clean:
	rm -f statusengine.o

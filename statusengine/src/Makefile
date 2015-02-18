statusengine.o: statusengine.c
	LANG=C gcc -shared -o statusengine.o -fPIC  -Wall -Werror statusengine.c -luuid -levent -lgearman -ljson-c

clean:
	rm -f statusengine.o

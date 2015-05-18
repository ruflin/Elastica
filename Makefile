#/bin/bash

BASEDIR = $(pwd)
SOURCE = "${BASEDIR}/lib"


# Commands outside virtual machine
init: prepare
	composer --dev install
	
prepare:
	mkdir -p build/api
	mkdir -p build/code-browser
	mkdir -p build/coverage
	mkdir -p build/logs
	mkdir -p build/docs
	mkdir -p build/pdepend
	
update:
	
clean:
	rm -r build
	
start:
	vagrant up
	
stop:
	vagrant stop
	
destroy: clean
	vagrant destroy	
	
	
# Inside virtual machine
provision:
	
install:
	
run:
	
test:
	
doc:

lint:
	
loc:
	cloc --by-file --xml --exclude-dir=build -out=build/cloc.xml .
	
coverage:
	
package:
	
build:
	
	
# Other
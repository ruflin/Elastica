#!/bin/bash

wget https://github.com/downloads/elasticsearch/elasticsearch/elasticsearch-0.18.4.tar.gz
tar -xzf elasticsearch-0.18.4.tar.gz
elasticsearch-0.18.4/bin/plugin install mapper-attachments

export JAVA_OPTS="-server"
elasticsearch-0.18.4/bin/elasticsearch &

echo "Waiting until elasticsearch is ready on port 9200"
while [[ -z `curl -s 'http://localhost:9200' ` ]]
do 
	echo -n "."
	sleep 2s
done

echo "elasticsearch is up"

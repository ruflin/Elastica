#!/bin/bash

wget http://cloud.github.com/downloads/elasticsearch/elasticsearch/elasticsearch-0.19.11.tar.gz
tar -xzf elasticsearch-0.19.11.tar.gz
sed 's/# index.number_of_shards: 1/index.number_of_shards: 2/' elasticsearch-0.19.11/config/elasticsearch.yml > elasticsearch-0.19.11/config/elasticsearch.yml
sed 's/# index.number_of_replicas: 0/index.number_of_replicas: 0/' elasticsearch-0.19.11/config/elasticsearch.yml > elasticsearch-0.19.11/config/elasticsearch.yml
sed 's/# discovery.zen.ping.multicast.enabled: false/discovery.zen.ping.multicast.enabled: false/' elasticsearch-0.19.11/config/elasticsearch.yml > elasticsearch-0.19.11/config/elasticsearch.yml
elasticsearch-0.19.11/bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/1.6.0

export JAVA_OPTS="-server"
elasticsearch-0.19.11/bin/elasticsearch &

echo "Waiting until elasticsearch is ready on port 9200"
while [[ -z `curl -s 'http://localhost:9200' ` ]]
do 
	echo -n "."
	sleep 2s
done

echo "elasticsearch is up"

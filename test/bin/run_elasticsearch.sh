#!/bin/bash

wget http://cloud.github.com/downloads/elasticsearch/elasticsearch/elasticsearch-${ES_VER}.tar.gz
tar -xzf elasticsearch-${ES_VER}.tar.gz
sed 's/# index.number_of_shards: 1/index.number_of_shards: 2/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
sed 's/# index.number_of_replicas: 0/index.number_of_replicas: 0/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
sed 's/# discovery.zen.ping.multicast.enabled: false/discovery.zen.ping.multicast.enabled: false/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
elasticsearch-${ES_VER}/bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/${ES_MAPPER_ATTACHMENTS_VER}

export JAVA_OPTS="-server"
elasticsearch-${ES_VER}/bin/elasticsearch &
elasticsearch-${ES_VER}/bin/elasticsearch &

echo "Waiting until elasticsearch node 1 is ready on port 9200"
while [[ -z `curl -s 'http://localhost:9200' ` ]]
do 
	echo -n "."
	sleep 2s
done

echo "Waiting until elasticsearch node 2 is ready on port 9201"
while [[ -z `curl -s 'http://localhost:9201' ` ]]
do 
	echo -n "."
	sleep 2s
done

echo "two elasticsearch nodes are up"

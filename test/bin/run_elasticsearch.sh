#!/bin/bash

wget http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-${ES_VER}.tar.gz
tar -xzf elasticsearch-${ES_VER}.tar.gz
sed 's/# index.number_of_shards: 1/index.number_of_shards: 2/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
sed 's/# index.number_of_replicas: 0/index.number_of_replicas: 0/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
sed 's/# discovery.zen.ping.multicast.enabled: false/discovery.zen.ping.multicast.enabled: false/' elasticsearch-${ES_VER}/config/elasticsearch.yml > elasticsearch-${ES_VER}/config/elasticsearch.yml
elasticsearch-${ES_VER}/bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/${ES_MAPPER_ATTACHMENTS_VER}
elasticsearch-${ES_VER}/bin/plugin -install elasticsearch/elasticsearch-transport-thrift/${ES_TRANSPORT_THRIFT_VER}

export JAVA_OPTS="-server"
elasticsearch-${ES_VER}/bin/elasticsearch &
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

echo "Waiting until elasticsearch node 3 is ready on port 9202"
while [[ -z `curl -s 'http://localhost:9202' ` ]]
do
	echo -n "."
	sleep 2s
done

echo "three elasticsearch nodes are up"

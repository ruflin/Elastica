#!/bin/bash

check_port_http_code() {
    http_code=`echo $(curl -s -o /dev/null -w "%{http_code}" "http://localhost:$1")`
    return `test $http_code = "$2"`
}

wget http://download.elasticsearch.org/elasticsearch/elasticsearch/elasticsearch-${ES_VER}.tar.gz
tar -xzf elasticsearch-${ES_VER}.tar.gz

elasticsearch-${ES_VER}/bin/plugin -install elasticsearch/elasticsearch-mapper-attachments/${ES_MAPPER_ATTACHMENTS_VER}
#elasticsearch-${ES_VER}/bin/plugin -install elasticsearch/elasticsearch-transport-thrift/${ES_TRANSPORT_THRIFT_VER}
elasticsearch-${ES_VER}/bin/plugin -install geocluster-facet --url https://github.com/zenobase/geocluster-facet/releases/download/${ES_GEOCLUSTER_FACET_VER}/geocluster-facet-${ES_GEOCLUSTER_FACET_VER}.jar

export JAVA_OPTS="-server"

# start 3 elasticsearch instances
for i in 0 1 2
do
    echo "Setup node #$i"

    let "http_port = 9200 + $i"
    let "thrift_port = 9500 + $i"

    config_yml=elasticsearch-${ES_VER}/config/elasticsearch-$http_port.yml

    echo "Creating config $config_yml"

    cp elasticsearch-${ES_VER}/config/elasticsearch.yml $config_yml

    echo "index.number_of_shards: 2" >> $config_yml
    echo "index.number_of_replicas: 0" >> $config_yml
    echo "discovery.zen.ping.multicast.enabled: false" >> $config_yml
    echo "http.port: $http_port" >> $config_yml
    echo "thrift.port: $thrift_port" >> $config_yml

    # enable udp
    echo "bulk.udp.enabled: true" >> $config_yml
    echo "bulk.udp.bulk_actions: 5" >> $config_yml
    
    # enable dynamic scripting
    echo "script.disable_dynamic: false" >> $config_yml

    echo "Starting server on http port: $http_port"

    elasticsearch-${ES_VER}/bin/elasticsearch -d -Des.config=$config_yml &

    while ! check_port_http_code $http_port 200; do
        echo -n "."
        sleep 2s
    done
    echo ""
    echo "Server #$i is up"
done

echo "three elasticsearch nodes are up"

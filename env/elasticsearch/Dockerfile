FROM elasticsearch:1.6.0
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

# Dependencies
ENV ES_MAPPER_ATTACHMENTS_VER 2.6.0
ENV ES_TRANSPORT_MEMCACHED_VER 2.6.0
ENV ES_TRANSPORT_THRIFT_VER 2.6.0
ENV ES_GEOCLUSTER_FACET_VER 0.0.12
ENV ES_IMAGE_PLUGIN_VER 1.4.0
ENV ES_PLUGIN_BIN /usr/share/elasticsearch/bin/plugin

# Install Plugins
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-mapper-attachments/${ES_MAPPER_ATTACHMENTS_VER}
RUN ${ES_PLUGIN_BIN} -install image --url https://github.com/SibaTokyo/elasticsearch-image/releases/download/${ES_IMAGE_PLUGIN_VER}/elasticsearch-image-${ES_IMAGE_PLUGIN_VER}.zip
RUN ${ES_PLUGIN_BIN} -install geocluster-facet --url https://github.com/zenobase/geocluster-facet/releases/download/${ES_GEOCLUSTER_FACET_VER}/geocluster-facet-${ES_GEOCLUSTER_FACET_VER}.jar
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-transport-thrift/${ES_TRANSPORT_THRIFT_VER}
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-transport-memcached/${ES_TRANSPORT_MEMCACHED_VER}

# Debug interface
RUN ${ES_PLUGIN_BIN} -install mobz/elasticsearch-head

# Copy config files
COPY *.yml /usr/share/elasticsearch/config/

# Expose standard ports, thrift, udp, memcache
EXPOSE 9200 9300 9500 9700 9800 11211

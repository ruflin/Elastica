FROM elasticsearch:1.7.3
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

# Dependencies
ENV ES_MAPPER_ATTACHMENTS_VER 2.7.0
ENV ES_TRANSPORT_MEMCACHED_VER 2.7.0
ENV ES_TRANSPORT_THRIFT_VER 2.7.0
ENV ES_GEOCLUSTER_FACET_VER 0.0.12
ENV ES_IMAGE_PLUGIN_VER 1.7.1
ENV ES_PLUGIN_BIN /usr/share/elasticsearch/bin/plugin

# Install Plugins
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-mapper-attachments/${ES_MAPPER_ATTACHMENTS_VER}
RUN ${ES_PLUGIN_BIN} -install image --url https://github.com/Jmoati/elasticsearch-image/releases/download/${ES_IMAGE_PLUGIN_VER}/elasticsearch-image-${ES_IMAGE_PLUGIN_VER}.zip
RUN ${ES_PLUGIN_BIN} -install geocluster-facet --url https://github.com/zenobase/geocluster-facet/releases/download/${ES_GEOCLUSTER_FACET_VER}/geocluster-facet-${ES_GEOCLUSTER_FACET_VER}.jar
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-transport-thrift/${ES_TRANSPORT_THRIFT_VER}
RUN ${ES_PLUGIN_BIN} -install elasticsearch/elasticsearch-transport-memcached/${ES_TRANSPORT_MEMCACHED_VER}

# Debug interface
RUN ${ES_PLUGIN_BIN} -install mobz/elasticsearch-head

# Copy config files
COPY *.yml /usr/share/elasticsearch/config/
COPY scripts/* /usr/share/elasticsearch/config/scripts/


RUN mkdir -p /tmp/backups/backup1
RUN mkdir -p /tmp/backups/backup2

# Expose standard ports, thrift, udp, memcache
EXPOSE 9200 9300 9500 9700/udp 9800/udp 11211

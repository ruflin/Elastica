#https://www.docker.elastic.co/
FROM docker.elastic.co/elasticsearch/elasticsearch:7.0.0-alpha1-SNAPSHOT
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

#RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install --batch ingest-attachment
RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install --batch https://snapshots.elastic.co/downloads/elasticsearch-plugins/ingest-attachment/ingest-attachment-7.0.0-SNAPSHOT.zip


# Copy config files
COPY elasticsearch.yml /usr/share/elasticsearch/config/elasticsearch.yml
COPY scripts/* /usr/share/elasticsearch/config/scripts/

#https://www.docker.elastic.co/
FROM docker.elastic.co/elasticsearch/elasticsearch:7.1.0
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install --batch ingest-attachment


# Copy config files
COPY elasticsearch.yml /usr/share/elasticsearch/config/elasticsearch.yml
COPY scripts/* /usr/share/elasticsearch/config/scripts/

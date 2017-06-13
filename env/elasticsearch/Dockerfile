FROM docker.elastic.co/elasticsearch/elasticsearch:5.4.1
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

RUN /usr/share/elasticsearch/bin/elasticsearch-plugin install mapper-attachments

# Copy config files
COPY elasticsearch.yml /usr/share/elasticsearch/config/elasticsearch.yml
COPY scripts/* /usr/share/elasticsearch/config/scripts/

FROM java:8-jre
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

ENV VERSION 5.0.0

ENV URL https://artifacts.elastic.co/downloads/elasticsearch/elasticsearch-${VERSION}.tar.gz

# Cache variable can be set during building to invalidate the build cache with `--build-arg CACHE=$(date +%s) .`
ARG CACHE=1

ENV ESHOME /opt/elasticsearch-${VERSION}
ENV ES_PLUGIN_BIN ${ESHOME}/bin/elasticsearch-plugin

# grab gosu for easy step-down from root
RUN gpg --keyserver ha.pool.sks-keyservers.net --recv-keys B42F6819007F00F88E364FD4036A9C25BF357DD4
RUN arch="$(dpkg --print-architecture)" \
	&& set -x \
	&& curl -o /usr/local/bin/gosu -fSL "https://github.com/tianon/gosu/releases/download/1.3/gosu-$arch" \
	&& curl -o /usr/local/bin/gosu.asc -fSL "https://github.com/tianon/gosu/releases/download/1.3/gosu-$arch.asc" \
	&& gpg --verify /usr/local/bin/gosu.asc \
	&& rm /usr/local/bin/gosu.asc \
	&& chmod +x /usr/local/bin/gosu

RUN groupadd -r elasticsearch && useradd -r -m -g elasticsearch elasticsearch

RUN set -x && \
	cd /opt && \
	wget -qO elasticsearch.tar.gz "$URL?${CACHE}" && \
	tar xzvf elasticsearch.tar.gz && \
	chown -R elasticsearch:elasticsearch ${ESHOME}

ENV PATH ${ESHOME}/bin:$PATH

VOLUME ${ESHOME}/data

ENV ES_JAVA_OPTS="-Xms512m -Xmx512m"

RUN ${ES_PLUGIN_BIN} install mapper-attachments

# Copy config files
COPY *.yml /usr/share/elasticsearch/config/
COPY scripts/* /usr/share/elasticsearch/config/scripts/

COPY docker-entrypoint.sh /

ENTRYPOINT ["/docker-entrypoint.sh"]


RUN mkdir -p /tmp/backups/backup1
RUN mkdir -p /tmp/backups/backup2

EXPOSE 9200 9300

CMD ["elasticsearch"]

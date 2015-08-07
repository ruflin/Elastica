# Data image for elasticsearch instances
FROM debian:jessie
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

RUN mkdir -p /mount/backups/backup1
RUN mkdir -p /mount/backups/backup2

VOLUME /mount/backups/backup1
VOLUME /mount/backups/backup2

RUN chmod -R 777 /mount
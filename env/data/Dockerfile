# Data image
FROM debian:jessie

RUN mkdir -p /mount/backups/my_backup
RUN mkdir -p /mount/longterm_backups/my_backup

VOLUME /mount/backups/my_backup
VOLUME /mount/longterm_backups/my_backup

RUN chmod -R 777 /mount
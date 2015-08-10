# Data image for elasticsearch instances
FROM ubuntu:precise
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

COPY . /vagrant/ansible
COPY composer.json /vagrant/


RUN apt-get update && apt-get install sudo make

WORKDIR /vagrant

RUN ansible/provision.sh

RUN /etc/init.d/elasticsearch restart
RUN /etc/init.d/nginx restart

# Data image for elasticsearch instances
FROM ubuntu:precise
MAINTAINER Nicolas Ruflin <spam@ruflin.com>

COPY . /vagrant/ansible

WORKDIR /vagrant

RUN ansible/provision.sh

#!/bin/bash

set -o xtrace

ES_USER=elasticsearch
ES_HOME=/usr/share/elasticsearch
LOG_DIR=/var/log/elasticsearch
PID_DIR=/var/run/elasticsearch
CONF_DIR=/etc/elasticsearch
DATA_DIR=/var/lib/elasticsearch

# Check requirements
id -u $ES_USER &> /dev/null || (echo "User elasticsearch is not created" && exit 1)
which java || (echo "Java is not installed" && exit 1)
test -x $ES_HOME/bin/elasticsearch || (echo "Elasticsearch is not installed" && exit 1)

# Prepare directories
mkdir $LOG_DIR $DATA_DIR $CONF_DIR $PID_DIR
chown -R $ES_USER $LOG_DIR $DATA_DIR $CONF_DIR $PID_DIR

# Start nodes
for node in 0 1; do
    echo "Starting node #$node"
    PID_FILE=$PID_DIR/node-$node.pid

    if [ -f $PID_FILE ]; then
        kill -9 $(cat $PID_FILE)
    fi

    COMMAND="$ES_HOME/bin/elasticsearch \
        -d \
        -p $PID_FILE \
        -Des.config=$CONF_DIR/config-$node.yml \
        -Des.path.home=$ES_HOME \
        -Des.path.logs=$LOG_DIR \
        -Des.path.data=$DATA_DIR \
        -Des.path.conf=$CONF_DIR
    "
    sudo -u $ES_USER ES_HEAP_SIZE=256m $COMMAND

done

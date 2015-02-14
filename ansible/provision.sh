#!/bin/bash

# ----------------------------------------------------------------------------
# Install ansible

sudo apt-get update -q
sudo apt-get install python python-pip python-dev -yq
sudo pip install ansible==1.8.2 -q
sudo mkdir -p /etc/ansible/
echo 'localhost' | sudo tee /etc/ansible/hosts

# ----------------------------------------------------------------------------
# Configure playbook

# Write to stdout directly
export PYTHONUNBUFFERED=1

# No cows >_<
export ANSIBLE_NOCOWS=1

# Root of git repo
if [ -z "$ES_PROJECT_ROOT" ]; then
    export ES_PROJECT_ROOT="$(dirname $(dirname $(readlink -f $0)))"
fi

# Install or not require-dev packages
if [ -z "$ES_COMPOSER_NODEV" ]; then
    export ES_COMPOSER_NODEV="no"
fi

# ----------------------------------------------------------------------------
# Run playbook

ansible-playbook $ES_PROJECT_ROOT/ansible/es-playbook.yml -v | tee /tmp/ansible-playbook-progress

if grep -q 'FATAL\|ERROR' /tmp/ansible-playbook-progress; then
    exit 1
fi

# ----------------------------------------------------------------------------

all_nodes_available() {
    curl -m 5 -s -o /dev/null "http://localhost:9200" &&
    curl -m 5 -s -o /dev/null "http://localhost:9201"
    return $?
}

check_cluster() {
    restarts_left=$1
    seconds_left=$2

    if [ $seconds_left -eq 0 ]; then
        if [ $restarts_left -eq 0 ]; then
            echo "Cluster was restarted 10 times, but still not ready for phpunit. Build failed!"
            return 1
        else
            echo "Restart cluster. Restarts left: $restarts_left"
            sudo service elasticsearch restart
            check_cluster $(( $restarts_left - 1 )) 30
            return $?
        fi
    else
        if all_nodes_available; then
            echo "All nodes available. Sleep 30 seconds and try to check them again..."
            if sleep 10s && all_nodes_available; then
                echo "All nodes still available - cluster ready."
                return 0
            else
                echo "Some nodes were stopped during sleep. Trying cluster restart..."
                check_cluster $restarts_left 0
                return $?
            fi
        else
            echo "Some nodes still unavailable. Sleep 1 second and try to check them again. Seconds to start left: $seconds_left"
            sleep 1s && check_cluster $restarts_left $(( $seconds_left - 1 ))
            return $?
        fi
    fi
}

echo "Wait cluster start..."
if ! check_cluster 10 30; then
    cat /var/log/elasticsearch/*.log
    exit 1
fi

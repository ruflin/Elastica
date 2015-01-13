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

ansible-playbook $ES_PROJECT_ROOT/ansible/es-playbook.yml -v -s | tee /tmp/ansible-playbook-progress

if grep -q 'FATAL\|ERROR' /tmp/ansible-playbook-progress; then
    exit 1
fi

# ----------------------------------------------------------------------------

echo 'Waiting for elasticsearch server ready'
elasticsearch_ready() {
    http_code=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:9200")
    return $(test $http_code = "200")
}
while ! elasticsearch_ready; do
    echo -n '.'
    sleep 1s
done

# ----------------------------------------------------------------------------
# Say bye

echo 'Done'

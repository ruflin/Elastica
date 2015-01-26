
Vagrant.require_version ">= 1.4.0"

Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/precise32"

  config.vm.network :private_network, ip: "10.10.10.10"

  config.vm.provision "shell" do |sh|
    sh.inline = "/bin/bash /vagrant/ansible/provision.sh"
  end

  config.vm.provider :virtualbox do |vb|
    vb.customize ["modifyvm", :id, "--memory", "1024"]
  end

end

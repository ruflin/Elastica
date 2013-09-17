# Elastica debian image
# 
# All passwords and username (db, ...) are root / root
#
# The image has 2GB of memory and a size of 10GB

Vagrant::Config.run do |config|

  config.vm.box = "debian-6.0.3-64-elastica-20120115"
  config.vm.box_url = "http://files.ruflin.com/vagrant/debian-6.0.3-64-elastica-20120528.box"
  config.vm.network :hostonly, "10.10.10.10"
  
  config.vm.share_folder "project", "/project", "."
end

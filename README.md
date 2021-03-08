# DirectAdmin Amazon SES
Welcome to this repository of an unofficial DirectAdmin plugin for amazon ses verification. 

With this plugin end-users on an DirectAdmin server can easliy add and remove amazon ses verification.

# Installation
## Requirements
This plugin works on every DirectAdmin server, but will only verify amazon ses on domains. You will still need to let exim send through it.

## Plugin installation
```
cd /usr/local/directadmin/plugins
git clone https://github.com/PayRequest/Amazon-SES-Directadmin-Hook.git amazon_ses
sh amazon_ses/scripts/install.sh
```

# Configuration
By default, the plugin is working out-of-the box. However the admin needs to setup their API credentials.

# Update
```
cd /usr/local/directadmin/plugins/amazon_ses
git pull
```

# Screenshots
Coming soon

# Todo
- Add Vue layout and make a nice UI.
- Check if _amazonses record has been set but not verified, different status
- Cloudflare integration

# Sponsors
![alt text](https://hostingvergelijker.nl/wp-content/uploads/webdiensten-zzp.png "Webdiensten ZZP")  
[Webdiensten ZZP](https://github.com/lutjebroeker)

#license
![Creative Commons Attribution 4.0 International License](https://i.creativecommons.org/l/by/4.0/88x31.png)  
[This work is licensed under a Creative Commons Attribution 4.0 International License.](http://creativecommons.org/licenses/by/4.0/)

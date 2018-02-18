# prof-it - Installation

Make sure you have PHP 7 available on the machine you want to run the profiler. 

## How to install XHProf

XHProf is used by the profiler to collect data while your scripts are running. You will have to manually compile and 
install this extension.

### Prepare your environment

#### On MacOS

To make things easy, we strongly encourage you to use [Homebrew](https://brew.sh/). It will let you install the most 
recent PHP versions and development tools which you will need to compile the extension.

```sh
brew install php72
brew link --force php72
```

#### On Debian

On Debian, PHP and the development tools are available as two distinct packages. 

```sh
sudo apt-get update
sudo apt-get install php-dev
```

#### On RHEL

On RHEL, PHP and the development tools are also available as two distinct packages. 

```sh
yum install php-devel
```

### Compile the extension

A new version of XHProf has been published by the cool guys at [Tideways](https://tideways.io/). It is available on 
[Github](https://github.com/tideways/php-xhprof-extension).

Compiling and enabling this extension is really easy:

```sh
git clone https://github.com/tideways/php-xhprof-extension.git /tmp/php-xhprof-extension
cd !$

phpize
./configure
make
make install
```

_Depending on your OS, you might get permission errors when running `make install`: sometimes it needs to be run as root._

Now that the PHP extension is compiled, you will have to manually enable it. Find your PHP configuration directory using 
this command:

```
php -i | grep 'Scan this dir for additional .ini files' | grep -oE '[^ ]*$'
```

Now let's create the configuration file for the XHProf extension and see if it works:

```
echo "extension=tideways_xhprof.so" | tee $(php -i | grep 'Scan this dir for additional .ini files' | grep -oE '[^ ]*$')
php -m | grep xhprof
```

You should see `tideways_xhprof` in the output if everything went fine. If it's OK, you are now ready to profile.

## How to install the PHP library

The PHP library will be used in the application you want to profile. The only installation method is [Composer](https://getcomposer.org/).

```sh
composer require --dev jubianchi/prof-it
```

## How to install the client application

To analyze the results of your profiling sessions, you will need the client application. It will let you open the 
profiles and navigate trough them.

### On MacOS

The client application is available as a _DMG_ image. Download the latest version from the Github 
[Releases page](https://github.com/jubianchi/prof-it/releases).

**Be sure to download the latest version.**

Now mount the image and copy the application to your _Applications_ folder.

### On Debian

The client application is available as a _deb_ package. Download the latest version from the Github 
[Releases page](https://github.com/jubianchi/prof-it/releases).

**Be sure to download the latest version.**

Now open the file by double-clicking on it and your package manager should guide you through the installation process.

### On RHEL

The client application is available as a _rpm_ package. Download the latest version from the Github 
[Releases page](https://github.com/jubianchi/prof-it/releases).

**Be sure to download the latest version.**

Now open the file by double-clicking on it and your package manager should guide you through the installation process.

_Depending on your distro, you might get errors when installing the package (somrthing about unsupported OS). If you 
run through this issue, use the following command to install the application:_

```sh
yum install lsb libXScrnSaver
rpm -i --ignoreos prof-it-*.x86_64.rpm
```

#!/bin/bash

# TODO how to replace OSX'curl

# first of all, install Xcode from App Store && run xcode-select --install
ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install)"

brew tap homebrew/dupes
brew install coreutils
brew install bash
brew install gpatch
brew install m4
brew install make
brew install file-formula
brew install git
brew install less
brew install openssh
brew install python
brew install rsync
brew install svn
brew install unzip
brew install vim --override-system-vi
brew install binutils
brew install diffutils
brew install ed --default-names
brew install findutils --with-default-names
brew install gawk
brew install gnu-indent --with-default-names
brew install gnu-sed --with-default-names
brew install gnu-tar --with-default-names
brew install gnu-which --with-default-names
brew install gnutls
brew install grep --with-default-names
brew install gzip
brew install watch
brew install wdiff --with-gettext
brew install wget

brew install cmake bash-completion pcre openssl readline autoconf automake gcc
brew install go node nmap tmux rsync subversion ettercap pstree mercurial
brew install aria2 irssi mplayer p7zip proxychains-ng
brew install php55 php55-mcrypt composer
brew install boot2docker docker

brew install caskroom/cask/brew-cask
brew cask install dash seil google-chrome shadowsocksx virtualbox firefox

#!/bin/bash

# TODO how to replace OSX'curl

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
brew install proxychains-ng aria2 cmake go node composer pstree make m4 readline base-completion p7zip irssi ettercap php55 php55-mcrypt subversion tmux nmap
echo 'PATH="$(brew --prefix coreutils)/libexec/gnubin:/usr/local/bin:$PATH"' >> ~/.bash_profile
echo 'MANPATH="$(brew --prefix coreutils)/libexec/gnuman:$MANPATH"' >> ~/.bash_profile

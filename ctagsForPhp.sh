#/**
#* @file ctagsForPhp.sh
#* @brief 
#* 0. rm ~/.tags
#* 1. cd /PATH/project/
#* 2. sh % 
#* 3. repeat step 1, 2
#* @author cloud@txthinking.com
#* @version 0.0.1
#* @date 2013-07-18
#*/
exec ctags -a -f /home/tx/.tags \
-h \".php\" -R \
--exclude=\"\.svn\" \
--totals=yes \
--tag-relative=yes \
--PHP-kinds=+cf \
--regex-PHP='/abstract class ([^ ]*)/\1/c/' \
--regex-PHP='/interface ([^ ]*)/\1/c/' \
--regex-PHP='/(public |static |abstract |protected |private )+function ([^ (]*)/\2/f/'

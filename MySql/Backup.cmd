rem Connect to Database
rem mysql --defaults-extra-file=.\_private\mysqldump.cnf intranet

rem Full Backup Databse Intranet
mysqldump --defaults-extra-file=.\_private\mysqldump.cnf intranet > c:\temp\intranet.sql
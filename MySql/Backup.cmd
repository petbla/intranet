rem Connect to Database
rem mysql --defaults-extra-file=.\_private\mysqldump.cnf intranet
rem mysql -u root -p --password=rumBA#21 intranet

rem Full Backup Databse Intranet
mysqldump --defaults-extra-file=.\_private\mysqldump.cnf intranet > c:\temp\intranet.sql
mysqldump -u root -p --password=rumBA#21 intranet --single-transaction > c:\temp\intranet.sql

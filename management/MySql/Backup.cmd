cls
@echo Backup database to c:\temp\intranet.sql
@mysqldump --defaults-extra-file=.\_private\mysqldump.cnf intranet > c:\temp\intranet.sql
@echo Finished
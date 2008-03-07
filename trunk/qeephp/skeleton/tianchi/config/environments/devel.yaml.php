# <?php die(); ?>

#############################
# 数据库元信息缓存设置
#############################

# 数据表元数据缓存时间（秒）
db_meta_lifetime:       0

# 指示是否缓存数据表的元数据
db_meta_cached:         false

# 缓存元数据使用的缓存服务
db_meta_cache_backend:  QCache_File


#############################
# 日志设置
#############################

# 指示是否启用日志服务
log_enabled:            true

# 指示日志服务的程序
log_provider:           QLog

# 指示用什么目录保存日志文件
#
# 如果没有指定日志存放目录，则保存到内部缓存目录中
log_files_dir:          %ROOT_DIR%/log

# 指示用什么文件名保存日志
log_filename:           devel-debug.log

# 指示当日志文件超过多少 KB 时，自动创建新的日志文件，单位是 KB，不能小于 512KB
log_file_maxsize:       4096

# 指示哪些级别的错误要保存到日志中
log_level:              notice, debug, warning, error, exception, log

# 指示是否显示错误信息
display_errors:         true

# 指示是否显示友好的错误信息
friendly_errors:        true

# 指示是否在错误信息中显示出错位置的源代码
display_source:         true
# <?php die(); ?>

#############################
# 访问规则
#############################

#
# 访问规则示例
#

# 控制器名称
nonexistent:
  # 对该控制器需要的访问权限
  allow: ACL_EVERYONE
  # actions 表示对控制器的个别动作进行权限控制
  actions:
    first:
      # first 动作的访问权限
      allow: ACL_EVERYONE
    second:
      # second 动作的访问权限
      deny: member
    # ACTION_ALL 代表该控制器的所有其他动作
    ACTION_ALL:
      allow: member

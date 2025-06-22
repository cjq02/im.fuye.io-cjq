-- 创建时间：2024-06-07
-- 功能：为群聊表添加群类型字段（1公开群，2私密群）
ALTER TABLE ims_mdkeji_im_rooms ADD COLUMN group_type TINYINT(1) NOT NULL DEFAULT 1 COMMENT '群类型：1公开群 2私密群'; 
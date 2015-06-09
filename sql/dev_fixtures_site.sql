-- sample data for development of the LibreCores web site

INSERT INTO `Organization` (`id`, `name`) VALUES
(1, 'openrisc');

INSERT INTO `SourceRepo` (`id`, `type`, `url`, `stats_id`) VALUES
(1, 'git', 'https://github.com/openrisc/mor1kx.git', NULL);

INSERT INTO `Project` (`id`, `name`, `parentOrganization`, `parentUser`, `projectUrl`, `issueTracker`, `sourceRepo_id`) VALUES
(1, 'mor1kx', 1, NULL, 'https://github.com/openrisc/mor1kx', 'https://github.com/openrisc/mor1kx/issues', 1);


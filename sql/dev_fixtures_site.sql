-- sample data for development of the LibreCores web site

INSERT INTO `Organization` (`id`, `name`) VALUES
(1, 'unassigned'),
(2, 'openrisc');

INSERT INTO `SourceRepo` (`id`, `type`, `url`, `stats_id`) VALUES
(1, 'git', 'https://github.com/openrisc/mor1kx.git', NULL),
(2, 'svn', 'http://opencores.org/ocsvn/openmsp430/openmsp430/trunk/', NULL);

INSERT INTO `Project` (`id`, `name`, `parentOrganization_id`, `parentUser_id`, `projectUrl`, `issueTracker`, `sourceRepo_id`, `status`, `descriptionTextAutoUpdate`, `licenseName`, `licenseTextAutoUpdate`, `inProcessing`, `dateAdded`, `dateLastModified`) VALUES
(1, 'mor1kx', 2, NULL, 'https://github.com/openrisc/mor1kx', 'https://github.com/openrisc/mor1kx/issues', 1, 'ASSIGNED', 1, 'OHDL', 1, 0, NOW(), NOW()),
(2, 'openmsp430', 1, NULL, 'http://opencores.org/project,openmsp430', 'http://opencores.org/project,openmsp430,bugtracker', 2, 'UNASSIGNED', 1, 'LGPL', 1, 0, NOW(), NOW());

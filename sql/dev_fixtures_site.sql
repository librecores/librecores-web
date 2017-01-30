-- sample data for development of the LibreCores web site

INSERT INTO `Organization` (`id`, `name`, `displayName`, `description`, `ownerId`) VALUES
(1, 'unassigned', 'Unassigned', 'Unassigned description', NULL),
(2, 'openrisc', 'OpenRISC', 'OpenRISC Description', NULL);

INSERT INTO `SourceRepo` (`id`, `type`, `url`, `stats_id`) VALUES
(1, 'git', 'https://github.com/openrisc/mor1kx.git', NULL),
(2, 'svn', 'http://opencores.org/ocsvn/openmsp430/openmsp430/trunk/', NULL);

INSERT INTO `Project` (`id`, `name`, `parentOrganization_id`, `parentUser_id`, `projectUrl`, `issueTracker`, `sourceRepo_id`, `status`, `descriptionTextAutoUpdate`, `licenseName`, `licenseTextAutoUpdate`, `inProcessing`, `dateAdded`, `dateLastModified`) VALUES
(1, 'mor1kx', 2, NULL, 'https://github.com/openrisc/mor1kx', 'https://github.com/openrisc/mor1kx/issues', 1, 'ASSIGNED', 1, 'OHDL', 1, 0, NOW(), NOW()),
(2, 'openmsp430', 1, NULL, 'http://opencores.org/project,openmsp430', 'http://opencores.org/project,openmsp430,bugtracker', 2, 'UNASSIGNED', 1, 'LGPL', 1, 0, NOW(), NOW());

INSERT INTO `ProjectTagCategory` (`id`, `name`, `color`) VALUES
(1,'','#777'),
(2,'language','#337ab7'),
(3,'isa','#5cb85c');

INSERT INTO `ProjectTag` (`id`, `category_id`, `name`, `createdAt`) VALUES
(1, 1, 'soc', NOW()),
(2, 1, 'cpu', NOW()),
(3, 2, 'verilog', NOW()),
(4, 2, 'vhdl', NOW()),
(5, 3, 'openrisc', NOW()),
(6, 3, 'msp430', NOW());

INSERT INTO `ProjectTagging` (`project_id`, `tag_id`, `taggedAt`) VALUES
(1, 2, NOW()),
(1, 3, NOW()),
(1, 5, NOW()),
(2, 2, NOW()),
(2, 3, NOW()),
(2, 6, NOW());

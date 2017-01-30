-- sample data for development of the LibreCores web site

INSERT INTO `Organization` (`id`, `name`, `displayName`, `description`, `creatorId`, `createdAt`, `updatedAt`) VALUES
(1, 'unassigned', 'Unassigned', 'Unassigned description', NULL, NOW(), NOW()),
(2, 'openrisc', 'OpenRISC', 'OpenRISC Description', NULL, NOW(), NOW());

INSERT INTO `SourceRepo` (`id`, `type`, `url`, `stats_id`) VALUES
(1, 'git', 'https://github.com/openrisc/mor1kx.git', NULL),
(2, 'git', 'https://github.com/optimsoc/sources', NULL);

INSERT INTO `Project` (`id`, `name`, `parentOrganization_id`, `parentUser_id`, `projectUrl`, `issueTracker`, `sourceRepo_id`, `status`, `descriptionTextAutoUpdate`, `licenseName`, `licenseTextAutoUpdate`, `inProcessing`, `dateAdded`, `dateLastModified`) VALUES
(1, 'mor1kx', 2, NULL, 'https://github.com/openrisc/mor1kx', 'https://github.com/openrisc/mor1kx/issues', 1, 'ASSIGNED', 1, 'OHDL', 1, 0, NOW(), NOW()),
(2, 'optimsoc', 1, NULL, 'http://www.optimsoc.org', 'https://github.com/optimsoc/issues', 2, 'UNASSIGNED', 1, 'MIT', 1, 0, NOW(), NOW());

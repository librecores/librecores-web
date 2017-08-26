# How do we calculate the project quality?

The quality score you see on every project page is calculated from
various metrics extracted from the project's source code, its issue
tracker, pull requests (for GitHub projects) and project's activity.

The score gives you an estimate of how good the project is, by
examining a few points.

1. How long has been the project actively developed ?
2. Does it follow standard conventions ?
3. How often are the projects updated ?
4. Does the source code carry proper comments ?

## The Formula for calculation

We calculate the score by awarding or deducting points accordion to
certain criteria on certain metrics.

1. **Presence of an issue tracker**: +2 if present, -1 if not.
2. **Activity**: Activity co\nsists of any activity such as, commits
to the repository, comments on issue trackers, pull requests etc. +2
if last activity took place within 1 month, +1 if last activity took
place within last 1 year, +0.25 if last activity took place within
last 3 years, else -0.25.
3. **Issues**: If project has open issues, +0.5
4. **Pull requests**: (GitHub projects only) +0.25 if there are open
pull requests to the project.
5. **Contributors**: +3 if project has more than 20 contributors, +1
if the project has more than 8 contributors, +0.5 if the project has
more than 3 contributors, else -1.
6. **Code to comment ratio**: +2 if the ratio of comments to source
code is greater than 0.2, else -1.
7. **GitHub stars**: +2.5 for 10000+ stars, +1 for 1000 - 10000 stars,
+0.5 for 100 stars.
8. **Changelog**: +0.25 for release notes or a change log.
9. **Description**: +1 if project description is present, else -2.
10. **License**:  +1 if a license is present or selected in project
settings, else -3.
11. **Stable commit activity**: +0.5 if commits continue to be in same
rate since the beginning.
12. **Regular code update**: +0.5 if project has reqular updates.
13. **Trend in number of contributors**: +0.5 if new contributors actively
join the project.


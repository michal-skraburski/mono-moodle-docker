---
title: Usage docs
---

# moodle.skraburski.com usage docs

## Reference
This domain belongs to [https://github.com/michal-skraburski/mono-moodle-docker](https://github.com/michal-skraburski/mono-moodle-docker).
The purpose of this website is to provide a diff for what the implemented changes look like.
The website is a part of my dissertation project, anything you see is possibly subject to change.
If you have an issue with the website, please follow the [github link](https://github.com/michal-skraburski/mono-moodle-docker) and make a GitHub Issue.

## Usage
The website domain is split into different web servers.
- [moodle.skraburski.com/new](https://moodle.skraburski.com/new)
- [moodle.skraburski.com/old]

The two distinctions to make is that `new` and `old` reflect which version of the changes the
path is providing. `new` meaning everything up to latest and my changes. `old` meaning my
changes only.

This setup is useful to you (hopefully the marker) because you can alternate between the two
sub paths and see what is different. Both servers share the database and Jobe server, except
for the plugins which have separate volumes. This means you can just replace the `new` sub path
with `old` and land on the exact same page, **you will however have to login twice** if you 
use this feature, once per session -- This is because the web servers cannot share session keys.

## See more
- [Server configuration](setup.md) - the setup behind this website is awesome, this page details everything about it
- [What to explore](explore.md) - guide that contains links to find every change
- [GitHub repo](https://github.com/michal-skraburski/mono-moodle-docker) - github repo this hosts off

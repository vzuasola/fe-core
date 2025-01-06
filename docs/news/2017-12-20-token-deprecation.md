# Token Deprecations

In favor of the new [query builder](docs/news/2017-12-20-query-format.md), tokens
that return hard coded separators will be deprecated

| `Previous Token` | `New Token`        | `New Token Usage`                                      |
|------------------|--------------------|--------------------------------------------------------|
| legacy.params    | credentials.params | http://mysite.com/about/[query:({credentials.params})] |
| tracking.token   | tracking           | http://mysite.com/about/[query:({tracking})]           |
| creferer.token   | creferer.token     | http://mysite.com/about/[query:({creferer.token})]     |

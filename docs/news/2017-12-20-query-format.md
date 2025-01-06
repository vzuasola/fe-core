# The New Query Format

Webcomposer now has support for dynamic query parameters using a new query builder syntax

This is useful if you want to define query parameters on the CMS or on inline contents.

The query builder has the following syntax

```
http://mysite.com/about/[query:(name=leandrew&age=35)]
```

The query builder will automatically add the proper queries

```
http://mysite.com/about/?name=leandrew&age=53
```

You can also define tokens as query in two ways.

You can add a specific key if the token does not provide any

```
http://mysite.com/about/[query:(name={name}&age=35)]
```

> The query builder will ommit an empty query parameter, example is the above,
> if it happens that name token is empty, then the resulting output will be just
> `?age=35`


You can also add tokens that provides a key, example is

```
http://mysite.com/about/[query:({tracking}&name=alex&age=35)]
```

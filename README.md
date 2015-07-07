# RestBase

A simple PHP base class for quick and painless REST clients. RestBase uses
Guzzle to handle the actual HTTP call and allows you to focus on just the
details of the particular REST endpoint.

The compositional nature of the subclasses allows for elegant application of the
DRY principle, where each REST endpoint request is a small class detailing only
the nature of the exact request.

## Easy to use

Below is a sample class for retrieving a list of repositories using the Github
API for a particular user.

```php
<?php

class GithubReposRequest extends \DanBruce\RestBase\AbstractRestRequest
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    protected function getHttpVerb()
    {
        return self::HTTP_VERB_GET;
    }

    protected function getUrl()
    {
        return sprintf(
            'https://api.github.com/users/%s/repos',
            $this->user
        );
    }

    protected function getOptions()
    {
        return []; // defaults are fine
    }
}
?>
```

And to use the class:
```php
<?php
$request = new GithubReposRequest('danbruce');
$repositories = $request->makeRequest();
?>
```

## Three simple methods

Subclasses of the `AbstractRestRequest` must only implement three methods, most
of which return simple constant strings.

- `getHttpVerb`: the HTTP verb (get, post, put, delete, etc) to use in the
  request
- `getUrl`: the URL to use in the request (e.g.
  https://api.github.com/users/danbruce/repos)
- `getOptions`: the options to hand off to the Guzzle client when making the
  request (if not required, an empty array will suffice)

Due to the compositional design of the subclasses, it is easy to reduce the
amount of repeated code with additional object hierarchy on top of
`AbstractRestRequest`.

As an example, we can add an `AbstractGithubApiRequest` class that assumes
requests use the HTTP verb GET and that the requests can use the default set of
options. Furthermore, the `getUrl` method will return a base URL that can be
reused in all subclasses. We can then rewrite `GithubReposRequest` to be even
smaller with less code.

```php
<?php

class AbstractGithubApiRequest extends \DanBruce\RestBase\AbstractRestRequest
{
    protected function getHttpVerb()
    {
        return self::HTTP_VERB_GET;
    }

    protected function getUrl()
    {
        return 'https://api.github.com';
    }

    protected function getOptions()
    {
        return [];
    }
}

class GithubReposRequest extends AbstractGithubApiRequest
{
    private $user;

    public function __construct($user)
    {
        $this->user = $user;
    }

    protected function getUrl()
    {
        return sprintf(
            '%s/users/%s/repos',
            parent::getUrl(),
            $this->user
        );
    }
}
```

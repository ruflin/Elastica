# Security Policy

## Supported Versions

Security fixes are provided for the latest minor release of each supported
major version. The supported branches at any time mirror the
[Elasticsearch end-of-life schedule](https://www.elastic.co/support/eol).

| Branch | Elasticsearch | PHP        | Status                |
|--------|---------------|------------|-----------------------|
| 9.x    | 9.x           | 8.1 – 8.5  | Active development    |
| 8.x    | 8.x           | 8.0 – 8.3  | Security fixes only   |
| 7.x    | 7.x           | 7.2+       | End of life           |
| 6.x    | 6.x           | 7.0+       | End of life           |

Anything older than 7.x is unmaintained.

## Reporting a Vulnerability

If you discover a security vulnerability in Elastica, **please do not open
a public GitHub issue**. Instead use one of the following private channels:

- Open a [GitHub Security Advisory](https://github.com/ruflin/Elastica/security/advisories/new)
  on the repository (preferred).
- Or email the maintainer directly at `spam@ruflin.com` with the subject
  prefix `[Elastica security]`.

Please include:

- A description of the vulnerability and its impact.
- Steps to reproduce, ideally with a minimal proof of concept.
- The Elastica version and PHP version you tested against.
- Any suggested mitigation, if available.

You can expect an initial acknowledgement within **5 business days** and a
coordinated disclosure window of up to **90 days** while a fix is prepared
and released. Credits are given in the release notes unless you ask to
remain anonymous.

## Scope

The following are in scope:

- Code published as part of the [`ruflin/elastica`](https://packagist.org/packages/ruflin/elastica)
  Composer package.
- Continuous-integration workflows and reusable scripts shipped with the
  repository.

Out of scope:

- Vulnerabilities in third-party dependencies (please report those upstream
  to their maintainers); we will track and bump impacted versions once a
  fix is available there.
- Vulnerabilities in user code that incorrectly uses Elastica.

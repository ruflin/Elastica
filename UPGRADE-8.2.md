UPGRADE FROM 8.1 to 8.2
=======================
Elastica 8.2 is a minor release. According to the release process, there should be no significant backward compatibility breaks.
Minor backward compatibility breaks are prefixed in this document with `[BC BREAK]`, make sure your code is compatible with these entries before upgrading.

Changes
-------
- [BC BREAK] `Search::search()` no longer takes the 3rd argument `$method`, the argument is unused
- [BC BREAK] `Search::count()` no longer takes the 3rd argument `$method`, the argument is unused
- [BC BREAK] `Search::search()` no longer takes the 3rd argument `$method`, the argument is unused
- [BC BREAK] `Request` class is removed

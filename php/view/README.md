The component files are disposed along a hierarchical tree, with the
user role as first level and the page type as second level:

  /view
    |--> /default
    |     |--> /generic_page
    |     |     |--> head.php
    |     |     |--> ...
    |     |
    |     |--> /home
    |     |     |--> head.php
    |     |     |--> ...
    |     |
    |     |--> ...
    |
    |--> /User
    |     |--> /generic_page
    |     |     |--> head.php
    |     |     |--> ...
    |     |
    |     |--> home
    |     |     |--> head.php
    |     |     |--> ...
    |     |
    |     |--> ...
    |
    |--> /Admin
          |--> /generic_page
          |     | --> head.php
          |     | --> ...
          |
          |--> ...

With such a hierarchy, the homepage of an User will probably share some
components with the about page for the same User, e.g. the head.php
file, while having different content.php file, and maybe it will share
some components with a visitor user too (e.g. the footer).

In this file tree, only the needed files for the components are written
and put in their right locations, while the components unchanged
respect to the more generic user/page type are just omitted. The right
component for each page is searched and loaded by the `setPage()` method
in the `ViewDescriptor` class.

When a page is set, the component file is found with a cascade search.
It is searched in its most specific location first (i.e the specified
role and page type). If the file is not found, a fallback is searched
in the generic page for the specified role. If this is not found too,
the search is repeated for the visitor role in the specified page type
and, when even this file does not exist, the generic page component for
the visitor role is used.

CHANGELOG for 1.1.x
=====================

This changelog references the relavant changes (bug and security fixes) done in
1.1 minor versions.

To get the new features in this major release, check the list at the bottom of
this file.

* 1.1.5 ()
    * Changed zerustech/io version to ``>= 2.1.0-dev``

* 1.1.4 (2016-08-21)
    * Re-implemented read() and write() methods
    * Added readSubstring() and writeSubstring() methods
    * Added input() and output() methods

* 1.1.3 (2016-08-11)
    * Removed duplicated files

* 1.1.2 (2016-08-11)
    * Merged 1.0

* 1.1.1 (2016-08-10)
    * Changed zerustech/io version to ``^1.1.0``
    * Removed ``getDownstream()`` from ``PipedOutputStream``
    * Removed ``getUpstream()`` from ``PipedInputStream``
    * Removed ``getBuffer()`` from ``PipedInputStream``

* 1.1.0 (2016-08-10)
    * Removed class ``AbstractStream``.
    * Added class ``AbstractInputStream``.
    * Added class ``AbstractOutputStream``.
    * Added class ``EventDispatcherContainer``.
    * Changed zerustech/io version to ``>= 1.1.0`` in composer.json
    * Removed dev-master alias.

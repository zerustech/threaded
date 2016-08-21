CHANGELOG for 2.1.x
=====================

This changelog references the relavant changes (bug and security fixes) done in
2.1 minor versions.

To get the new features in this major release, check the list at the bottom of
this file.

* 2.1.2 (2016-08-21)
    * Merged changes from v1.1.4 to support zerustech/io: 2.0.0

* 2.1.1 (2016-08-11)
    * Moved source files to src
    * Update dev-master alias to dev-2.1
    * Merge 1.1 into 2.1

* 2.1.0 (2016-08-10)
    * Removed class ``AbstractStream``.
    * Added class ``AbstractInputStream``.
    * Added class ``AbstractOutputStream``.
    * Added class ``EventDispatcherContainer``.
    * Changed zerustech/io version to ``^1.1.0`` in composer.json
    * Changed ext-pthreads to ``^3.1.6`` in composer.json
    * Removed dev-master alias.
    * Removed ``getUpStream()`` and ``getBuffer()`` from ``PipedInputStream``
    * Removed ``getDownstream()`` from ``PipedOutputStream``

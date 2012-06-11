====================================
Question2Answer Most Active Users Widget (per time interval) v1.0
====================================
-----------
Description
-----------
This is a plugin for **Question2Answer** that displays the most active users of the current week or the current month in a widget. 

--------
Features
--------
- considers question/answer/comment posted as +1 activity point
- needs plugin "Event Logger" enabled, check Admin > Plugins >Event Logger >options (tick "Log events to qa_eventlog database table")

------------
Installation
------------
#. Install Question2Answer_
#. Get the source code for this plugin from _github, download directly from the `project page`_ and click **Download**
#. extract the files to a subfolder such as ``most-active-users-widget`` inside the ``qa-plugins`` folder of your Q2A installation.
#. navigate to your site, go to **Admin -> Plugins** on your q2a install.
#. Set up the event logger plugin to ``Log events to qa_eventlog database table``.
#. Then, go to Admin >Layout >Available widgets, and add the widget "Most active users per week/month" where you want it to appear
#. Change settings (week or month) in file qa-most-active-users.php

.. _Question2Answer: http://www.question2answer.org/install.php
.. _github:
.. _project page: https://github.com/echteinfachtv/q2a-most-active-users

----------
Disclaimer
----------
This is **beta** code.  It is probably okay for production environments, but may not work exactly as expected.  Refunds will not be given.  If it breaks, you get to keep both parts.

-------
Release
-------
All code herein is Copylefted_.

.. _Copylefted: http://en.wikipedia.org/wiki/Copyleft

---------
About q2A
---------
Question2Answer is a free and open source platform for Q&A sites. For more information, visit:

http://www.question2answer.org/


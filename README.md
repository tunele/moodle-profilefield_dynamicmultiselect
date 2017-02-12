Dynamic multi-select user profile field for Moodle. Now users can create user multi-select fields whose values are retrieved from the moodle DB. Basically, the user can set an SQL query as value definition of the field. Please note that the query must return two fields: id and data. 
Please note that this is an advanced plugin, mainly intended for developers and very advanced moodle users. You must be confident with Moodle DB and SQL language to use this plugin properly. In fact, this plugin allows execution of raw SQL. Please be aware that executing raw SQL that has been improperly written can irreparably damage your site and/or cause performance issues. Please ensure you are aware of the impact of your SQL before executing it.
A possible use case is when one needs to link a user profile field to values that change in time because they are stored in a Moodle table and are updated by users and/or by external services.

Installation instructions:
Just upload and install it liek any other Moodle plugin.

Supported versions:
From 2.3 onwards.
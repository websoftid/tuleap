# Codendi
# Copyright (c) Xerox Corporation, Codendi Team, 2001-2009. All rights reserved
# http://www.codendi.com
#
# 
#
# Database upgrade script (see dbXXXX_template for instructions)
#
# This SQL script allows you to upgrade the CodeX database. In most cases
# this sql script relates to a well indentified modification in the 
# CodeX source code. All references are included
# below.
#
# Description
# Add new columns new_value into table artifact_history
#
#
# References:
# See task #240
#
# Dependencies:
# none
#
#

ALTER TABLE artifact_history ADD COLUMN new_value text NOT NULL default '' AFTER old_value;


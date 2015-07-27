<?php

# In April 2013, OCNL started using OCPL code. Google Code sends POST requests
# to one URL only. Therefore, we need this script to forward the update request
# to all destinations.

ignore_user_abort(true);
set_time_limit(0);

header("Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Content-Type: text/plain; charset=utf-8");

print "OCPL\n";
print "====\n\n";
readfile("http://opencaching.pl/post-commit.php?from=ocpl-propagate");

print "\n\n";
print "OCNL (test)\n";
print "===========\n\n";
readfile("http://test.opencaching.nl/post-commit.php?from=ocpl-propagate");

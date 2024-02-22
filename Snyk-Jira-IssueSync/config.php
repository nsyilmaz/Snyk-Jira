<?php

$DEBUG = 0;
$JiraApiToken="XXXXXXX"; // Put your Jira Api Token.
$SnykApiToken = "XXXXX"; // Put your Snyk Api Token.
$DividoOrgID = "XXXXXX";  // Put Org ID from Snyk.

/* 
    If API token fails, this is a workaround for session. It can be obtained from jira web session from developer tools. 
    It should be set via "curl_setopt()" as follows.
    $JiraCloudToken="cloud.session.token=XXXXXXXXX";
    curl_setopt($ch, CURLOPT_COOKIE, $JiraCloudToken );
*/

?>

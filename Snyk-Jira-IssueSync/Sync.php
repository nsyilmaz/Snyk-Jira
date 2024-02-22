<?php
include 'config.php';



/*
    Snyk Part
*/

// This function works with reporting API which works very slow, now it's retired.

/*
function FetchLibIssueFromReportApi($SnykProjectId, $SnykIssueId){

    $SnykApiURL = "https://snyk.io/api/v1/reporting/issues/?from=2021-07-01&to=2022-07-07";
    
    $CompOrgID = "XXXXXXXXXXXXXXXXXXXXXXXXXX";
    $SnykHeader = array("Authorization: token $SnykApiToken", "Content-Type: application/json");
    $ch = curl_init($SnykApiURL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_COOKIE, "");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $SnykHeader);
    
    $SnykPostData=array (
        'filters' => 
            array (
                'orgs' => 
                array (
                    0 => "$CompOrgID",
                ),
                'projects' => 
                array (
                    0 => "$SnykProjectId",
                ),
                'issues' => 
                array (
                    0 => "$SnykIssueId",
                )
            )
    );
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($SnykPostData));
    
    $SnykResponse = curl_exec($ch);
    
    curl_close($ch);
    
    $SnykIssues = (array)json_decode($SnykResponse);

    
    $SnykIssueURL = $SnykIssues['results'][0]->issue->url;

    echo "Snyk URL: " . $SnykIssueURL . "\n\n";
    //print_r($SnykIssues);
}
*/







function FetchCodeIssue($SnykProjectId, $SnykIssueId){

    global $DEBUG;
    global $SnykApiToken;
    global $CompOrgID;

    $SnykApiURL = "https://api.snyk.io/v3/orgs/$CompOrgID/issues/detail/code/$SnykIssueId?project_id=$SnykProjectId&version=2022-04-06%7Eexperimental";
    
    
    $SnykHeader = array("Authorization: token $SnykApiToken", "Content-Type: application/json");
    $ch = curl_init($SnykApiURL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_COOKIE, "");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $SnykHeader);
    

    // No data required for this Api call.
    //$SnykPostData=array ();

    //curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($SnykPostData));
    
    $SnykResponse = curl_exec($ch);
    
    curl_close($ch);
    
    $SnykIssues = (array)json_decode($SnykResponse);

    
    //$SnykIssueURL = $SnykIssues['results'][0]->issue->url;

    //echo "Snyk URL: " . $SnykIssueURL . "\n\n";
    //echo $SnykIssues['issues'][0]->id;
    //print_r($SnykIssues);

    if (isset($SnykIssues['data']->id)){
        if ($SnykIssues['data']->id == $SnykIssueId ){
            return 0;
        }
    }

    return 1;
    //exit(0);
}



function FetchLibIssue($SnykProjectId, $SnykIssueId){

    global $DEBUG;
    global $SnykApiToken;
    global $CompOrgID;  

    $SnykApiURL = "https://snyk.io/api/v1/org/company/project/$SnykProjectId/aggregated-issues";
    
    
    $SnykHeader = array("Authorization: token $SnykApiToken", "Content-Type: application/json");
    $ch = curl_init($SnykApiURL);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    //curl_setopt($ch, CURLOPT_COOKIE, "");
    curl_setopt($ch, CURLOPT_HTTPHEADER, $SnykHeader);
    

    // No data required for this Api call.
    $SnykPostData=array ();

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($SnykPostData));
    
    $SnykResponse = curl_exec($ch);
    
    curl_close($ch);
    
    $SnykIssues = (array)json_decode($SnykResponse);

    
    //$SnykIssueURL = $SnykIssues['results'][0]->issue->url;

    //echo "Snyk URL: " . $SnykIssueURL . "\n\n";
    //echo $SnykIssues['issues'][0]->id;
    //print_r($SnykIssues);

    if (isset($SnykIssues['issues'])){
        foreach($SnykIssues['issues'] as $issues){
            
            if ($issues->id == $SnykIssueId and $issues->isIgnored<>1){
                if($DEBUG){
                    echo "Snyk ID: " . $issues->id . "\n";
                    echo "Issue found..\n\n";
                }
                return 0;
                //exit(0);
            }
        }
    }
    if($DEBUG){
        echo "Issue NOT found..\n\n";
    }
    return 1;
    //exit(0);
}









/* 
    SAST Issue EPIC Link - SB-241
*/
$JiraURL="https://company.atlassian.net/rest/agile/1.0/epic/20083/issue?maxResults=1500";



/* 
    If API token fails, this is a workaround for session. It can be obtained from jira web session from developer tools. 
    It should be set via "curl_setopt()" as follows.
    $JiraCloudToken="cloud.session.token=";
    curl_setopt($ch, CURLOPT_COOKIE, $JiraCloudToken );
*/

$JiraHeader = array("Content-Type: application/json", "Authorization: Basic $JiraApiToken");


$ch = curl_init($JiraURL);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $JiraHeader);

//$data="";
//curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

echo "\nFetching SAST issues from Jira...\n";

$JiraResponse = curl_exec($ch);

curl_close($ch);

$SastIssues = (array)json_decode($JiraResponse);



//echo("Epic\n");
print_r($SastIssues['total'] . " items fetched from ");
print_r($SastIssues['issues'][0]->fields->parent->key . " - ");
print_r($SastIssues['issues'][0]->fields->parent->fields->summary . "\n");

$Count = 1;
echo("\nClosed issues will be checked from Snyk and listed here...:\n");

foreach($SastIssues['issues'] as $issues){
    $SnykIssueLink = "";
    echo "\033[200D" . $Count++ . "=> $issues->key " . "\033[K";
    
    
    //if ($issues->key <>"SB-291"){
      //  continue;
    //}
    if($DEBUG){
        print_r($issues->key . " - ");
        print_r($issues->fields->summary . " - ");
        print_r($issues->fields->priority->name . " - ");
        print_r($issues->fields->status->statusCategory->name . "\n");
    }
    //print_r($issues->fields->description . "\n");
    //print_r("Repo: https://app.snyk.io" . explode("snyk.io", $issues->fields->description)[1] . "\n\n");

    $level1 = explode("[Vulnerability in Repo", $issues->fields->description);
    if (isset($level1[1])){
            $level2 = explode("|", $level1[1]);
            if (isset($level2[1])){
                $SnykIssueLink = explode("]", $level2[1])[0];
            }
    }
    

    //echo "link: " . $SnykIssueLink . "\n\n";
    if (isset(explode("project/", $SnykIssueLink)[1])){
        $SnykProjectId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[0];
        $SnykIssueId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[1];
    }else{

        $SnykIssueLink = "https://app.snyk.io" . explode("|", explode("snyk.io", $issues->fields->description)[1])[0];
        //echo "Snyk issue link: " . $SnykIssueLink . "\n";
        if (isset(explode("project/", $SnykIssueLink)[1])){
            //echo "Snyk issue link1: " . $SnykIssueLink . "\n";



            if (isset(explode("]", $SnykIssueLink)[1])){
                $SnykIssueLink = explode("]", $SnykIssueLink)[0];
            }
            //echo "Snyk issue link: " . $SnykIssueLink . "\n";
            $SnykProjectId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[0];
            $SnykIssueId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[1];


        }else{
            $SnykIssueLink = "https://app.snyk.io" . explode("]", explode("|", explode("snyk.io", $issues->fields->description)[2])[0])[0];
            $SnykProjectId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[0];
            $SnykIssueId = explode("#issue-", explode("project/", $SnykIssueLink)[1])[1];
    

        }
    }
    $IssueType = explode("[", explode("Issue", $issues->fields->summary )[0])[1];

    $JiraIssueLink = "https://company.atlassian.net/browse/" .$issues->key;
    if($DEBUG){
        echo "Jira Link: " . $JiraIssueLink . "\n";
        echo "Snyk Link: " . $SnykIssueLink . "\n";
        echo "Project Id: " . $SnykProjectId . "\n";
        echo "Issue Id: " . $SnykIssueId . "\n";
        echo "Issue Type: " . $IssueType . "\n";
    }

    if ($issues->fields->status->statusCategory->name<>"Done"){
 
        if (   $IssueType == "Lib"  ){
            //if(FetchLibIssue("7176bb7e-089a-49bb-b24b-fe79e291dcf4", "SNYK-GOLANG-GOLANGORGXTEXTENCODINGUNICODE-609611")){
            if(FetchLibIssue($SnykProjectId, $SnykIssueId)){
                echo "Closed Issue  - [Lib]: " . $JiraIssueLink . "\n\n"; 
            }
        }elseif( $IssueType == "Code" ) {
            if(FetchCodeIssue($SnykProjectId, $SnykIssueId)){
                echo "Closed Issue - [Code]: " . $JiraIssueLink . "\n\n";
            } 
        }
    }

    if ($issues->key =="SB-291"){

        //print_r($issues);
        //exit(0);
    }
 
}

echo "\033[200D" . "Done" . "\033[K\n\n";

?>
